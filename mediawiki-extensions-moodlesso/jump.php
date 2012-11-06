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

// get destination url
if (isset($_GET['path'])) {
    $path = $_GET['path'];
}
else {
    echo "<p>moodlesso: no destination supplied</p>";
    die;
}

// check user is logged in
if (!empty($wgUser)) {
    $id = $wgUser->getId();
    $username = $wgUser->getName();
    $email = $wgUser->getEmail();
}
else {
    $id = 0;
}

// if user is not logged in then
// redirect to login page
if (empty($id)) {
    $link = "{$wgServer}{$wgScriptPath}/index.php?title=Special:UserLogin";
    moodlesso::redirect( $link );
}

// get a token (and store in database)
$token = moodlesso::get_token( $username, $path );

// build the Moodle link and redirect
$link = $wgMoodlessoUrl . '/auth/mwsso/land.php?token=' . $token . '&user=' . $username;
moodlesso::redirect( $link );
