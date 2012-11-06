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

// This is a bodge to load the MW APIs and stuff
// There's possibly a better way to do it :(
putenv('MW_INSTALL_PATH=' . realpath( dirname(__FILE__) . '/../..' ) );
require( dirname(__FILE__) . '/../../includes/WebStart.php' );

// library class
require_once( 'moodlesso.class.php' );

// get the http body
// this should be the json data (with luck)
$body = file_get_contents('php://input');
$data = json_decode( $body );

// should at least have an action
if (empty($data->action)) {
    echo "Error: No action in request\n";
    die;
} 
else {
    $action = $data->action;
}

// check the action
if ($action == 'verify') {

    // expect fields 'username' and 'token'
    if (empty($data->username)) {
        echo "Error: No username in verify request\n";
        die;
    }
    else {
        $username = $data->username;
    }
    if (empty($data->token)) {
        echo "Error: No token in verify request\n";
        die;
    }
    else {
        $token = $data->token;
    }

    // check that user can login in 
    // and return user details
    moodlesso::ws_verify( $username, $token );
    die;
}

if ($action == 'authenticate') {

    // expect fields 'username' and 'password'
    if (empty($data->username) or empty($data->password)) {
        echo "Error: No username and/or password in authenticate request\n";
        die;
    }
    else {
        $username = $data->username;
        $password = $data->password;
        
        // verify user
        moodlesso::ws_authenticate( $username, $password );
        die;
    }
}

print_r( $data );
