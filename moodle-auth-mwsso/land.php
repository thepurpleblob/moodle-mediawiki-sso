<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details
 *
 * @package    auth
 * @subpackage mwsso
 * @author     Howard Miller
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once( dirname(__FILE__) . '/../../config.php' );

// Get user and token from MediaWiki.
$username = required_param( 'user', PARAM_ALPHANUM );
$token = required_param( 'token', PARAM_ALPHANUM );

// Get mwsso plugin setting.
$config = get_config( 'auth/mwsso' );

// Get plugin instance.
$mwsso = get_auth_plugin('mwsso');

// All we're going to do now is to send this
// straight back to MediaWiki to make sure it actually
// sent it.

// Construct link to wp site.
$link = $config->mwurl . '/extensions/moodlesso/ws.php';

// Construct request.
$parameters = array(
    'username' => $username,
    'token' => $token,
);

// Do web service.
$data = $mwsso->wsclient( 'verify', $parameters );

// Get information vital to Moodle.
$user_login = $data->user_name;
$user_email = $data->user_email;
$url = $data->url;

// Sanity check for username (just in case).
if ($username != $user_login) {
    echo "Error: returned username does not match ('$username' != '$user_login')\n";
    die;
}

// From here we need username to be all lower case,
// Moodle will do this anyway.
$username = strtolower($username);

// Does this user exist?
$exists = true;
if (!$user = $DB->get_record('user', array('username'=>$username))) {
    $user = new stdClass;
    $exists = false;
} else {

    // Check this user doesn't already exist with different
    // auth type.
    if ($user->auth != 'mwsso') {
        echo "Error: user '$username' already exists in Moodle as non-MediaWiki user\n";
        die;
    }
}

// Populate user object.
$user->username = $username;
$user->auth = 'mwsso';
$user->firstname = empty($user->firstname) ? '' : $user->firstname;
$user->lastname = empty($user->lastname) ? '' : $user->lastname;
$user->email = $user_email;
$user->confirmed = 1;
$user->lastip = getremoteaddr();
$user->timemodified = time();
$user->mnethostid = $CFG->mnet_localhost_id;

// Create or update the user.
if ($exists) {
    $DB->update_record( 'user', $user );
} else {
    $DB->insert_record( 'user', $user );
}

// Complete login.
$USER = get_complete_user_data( 'username', $username );
complete_user_login( $USER );
$user = $USER;

// If info is missing off to edit page.
if (empty($user->firstname) or empty($user->lastname)) {
    $link = new moodle_url( '/user/editadvanced.php', array('id'=>$user->id, 'course'=>1));
    redirect( $link );
} else if (!empty($url)) {
    $link = new moodle_url( $url );
    redirect( $link );
} else {
    redirect( $CFG->wwwroot );
}

