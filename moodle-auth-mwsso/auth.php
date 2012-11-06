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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir.'/authlib.php');

class auth_plugin_mwsso extends auth_plugin_base {

    private $password = '';

    /**
     * Constructor
     */
    public function __construct() {
        $this->authtype = 'mwsso';
        $this->pluginconfig = 'auth/' . $this->authtype;
        $this->config = get_config($this->pluginconfig);
    }

    /**
     * Returns true if the username and password work and false if they are
     * wrong or don't exist.
     *
     * @param string $username The username (without system magic quotes)
     * @param string $password The password (without system magic quotes)
     *
     * @return bool Authentication success or failure.
     */
    public function user_login($username, $password) {

        // Construct request.
        $parameters = array(
            'username'=>$username,
            'password'=>$password,
        );

        // Keep it.
        $this->password = $password;

        // Do web service to WP.
        $data = $this->wsclient( 'authenticate', $parameters );

        // Should return the user object, otherwise...
        if (!isset($data->user_name)) {
            return false;
        }

        // This just says it's ok.
        return true;
    }


    /**
     * Prints a form for configuring this authentication plugin.
     *
     * This function is called from admin/auth.php, and outputs a full page with
     * a form for configuring this plugin.
     *
     * @param array $page An object containing all the data for this page.
     */
    public function config_form($config, $err, $user_fields) {
        global $CFG, $OUTPUT;

        include($CFG->dirroot.'/auth/mwsso/config.html');
    }

    /**
     * Processes and stores configuration data for this authentication plugin.
     */
    public function process_config($config) {

        // Set to defaults if undefined.
        if (!isset($config->mwurl)) {
             $config->mwurl = 'http://localhost/mediawiki';
        }

        // Save settings.
        set_config('mwurl', trim($config->mwurl), $this->pluginconfig);

        return true;
    }

    /**
     * web service client to send request to MW
     * @param string $action function to do
     * @param array $parameters
     * @return array response
     */
    public function wsclient( $action, $parameters ) {

        // Construct link to wp site.
        $link = $this->config->mwurl . '/extensions/moodlesso/ws.php';

        // Construct data to send.
        $data = array('action'=>$action);
        foreach ($parameters as $key => $value) {
            $data[$key] = $value;
        }

        // Encode.
        $data_json = json_encode( $data );

        // HTTP header.
        $header = array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data_json),
        );

        // Setup curl to send stuff.
        if (!$ch = curl_init( $link )) {
            echo "Failed to open link to MediaWiki site";
            die;
        }
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        if (!$result = curl_exec($ch)) {
            echo "Failed to access MediaWiki site";
            die;
        }

        // Result either contains an error message
        // or the json object.
        if (!$data = json_decode($result)) {
            echo "Error decoding SSO response. Recieved '$result'";
            die;
        }

        return $data;
    }

    /**
     * Returns true if this authentication plugin is "internal".
     *
     * Internal plugins use password hashes from Moodle user table for authentication.
     *
     * @return bool
     */
    public function is_internal() {
        return false;
    }

}
