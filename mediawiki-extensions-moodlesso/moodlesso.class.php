<?php
/**
 * Copyright (C) 2012 Howard Miller
 * http://www.e-learndesign.co.uk
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 */

/**
 * @package moodlesso
 * @author Howard Miller, E-Learn Design Limited
 */

class moodlesso {

    /**
     * redirect
     * @param string $url 
     */
    public static function redirect( $url ) {
        header( "Location: $url" );
        die;
    }

    /**
     * Create a SSO token and store as transient
     * wrt current user
     * May not work if user has multiple logins but I
     * don't care
     * @param string $username
     * @return string token
     */
    public static function get_token( $username, $url ) {

        // make token by taking SHA1 of some random-ish
        // values
        $rawtoken = $username . time();
        $token = substr( sha1( $rawtoken ), 0, 20 );

        // Save in database
        $a = array(
            'id' => NULL,
            'timestamp'=> time(),
            'username' => strtolower($username),
            'token' => $token,
            'url' => $url,
        );
        $dbw = wfGetDB( DB_MASTER );
        $dbw->begin(); 
        $dbw->insert('moodlesso', $a, __METHOD__);
        $dbw->commit();

        return $token;
    }

    /**
     * Check and normalise jump-to url
     * @param string $url url supplied on page
     * @return mixed normalised url or false
     */
    private static function checkurl( $url ) {
        global $wgMoodlessoUrl;

        // if the url starts with http then
        // it has to match $wgMoodlessoUrl at the start
        if (stripos($url, 'http') === 0) {
            if (stripos($url, $wgMoodlessoUrl) === FALSE) {
                return false;
            }

            // trim off the wwwroot bit to just leave the path
            $url = substr($url, strlen($wgMoodlessoUrl));
        }        

        // check there is something left
        if (empty($url)) {
            return FALSE;
        }

        // make sure the url starts with a /
        if (substr($url, 0, 1) != '/') {
            $url ="/$url";
        } 

        return $url;
    }

    /** 
     * verify moodle sso request
     * @param string $username
     * @param string $token
     */
    public static function ws_verify($username, $token) {

        // look for token in the database
        $dbr = wfGetDB( DB_SLAVE );
        $conds = array(
            'token'=>$token,
            'username'=>$username,
        );
        $dbr->begin();
        $res = $dbr->select('moodlesso', '*', $conds, __METHOD__  );
        $dbr->commit();
        foreach ($res as $row) {
            $sso = $row;
            break;
        }

        // did we find anything
        if (empty($sso)) {
            echo "Error: SSO token was not found";
            die;
        }

        // details
        $timestamp = $sso->timestamp;
        $url = self::checkurl($sso->url);

        // check timeout
        $age = time() - $timestamp;
        if ($age > 30) {
            echo "Error: SSO has timed out. Please try again";
            die;
        }

        // delete sso record 
        $dbw = wfGetDB( DB_MASTER );
        $dbw->begin();
        $dbw->delete('moodlesso', $conds, __METHOD__);
        $dbw->commit();
 
        // SSO is now verified
        // Just remains to get user details
        $dbr = wfGetDB( DB_SLAVE );
        $conds = array(
            'user_name' => $username,        
        );
        $dbr->begin();
        $res = $dbr->select('user', '*', $conds, __METHOD__);
        $dbr->commit();
        foreach ($res as $row) {
            $user = $row;
            break;
        }

        // check user found
        if (empty($user)) {
            echo "Error: user account not found in MediaWiki";
            die;
        }

        // add destination url
        $user->url = $url;

        // don't want any sort of password
        $user->user_password = '';
        $user->user_token = '';

        // encode into json
        $user_json = json_encode( $user );
        
        // just send the json data (back to Moodle)
        echo $user_json . "\n";
    }

    /**
     * Verify login from Moodle
     * @param string $username
     * @param string $password
     */
    public static function ws_authenticate($username, $password) {

        // MW always capitalizes first letter of username!!
        $username = ucfirst( strtolower($username) );

        // create user object 
        $u = User::newFromName($username);
        $u->load();
        if (empty($u) or empty($u->mId)) {
            $user = new stdClass;
            $user->error = "Error: username $username does not exist";
            echo json_encode( $user ) . "\n";
            die;
        }

        // check password
        if (!$u->checkPassword($password)) {
            $user = new stdClass;
            $user->error = "Error: invalid password";
            echo json_encode( $user ) . "\n";
            die;
        }
        
        // Just remains to get user details
        $dbr = wfGetDB( DB_SLAVE );
        $conds = array(
            'user_name' => $username,        
        );
        $dbr->begin();
        $res = $dbr->select('user', '*', $conds, __METHOD__);
        $dbr->commit();
        foreach ($res as $row) {
            $user = $row;
            break;
        }

        // don't want any sort of password
        $user->user_password = '';
        $user->user_token = '';

        // return the json data
        $user->error = '';
        $user_json = json_encode( $user );
     
        echo "$user_json\n";
    }

}
