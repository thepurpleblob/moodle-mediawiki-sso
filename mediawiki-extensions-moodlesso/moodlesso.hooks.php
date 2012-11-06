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

class moodlessoHooks {

    // set up parser hook
    public static function SetupParserFunction( &$parser ) {

        // associate "moodle" magic word
        $parser->setFunctionHook( 'moodle', 'moodlessoHooks::RenderParserFunction' );

        return TRUE;
    }

    // setup database table
    public static function SetupDatabase( DatabaseUpdater $updater ) {
        $updater->addExtensionUpdate( array('addTable', 'moodlesso', 
            dirname(__FILE__) . '/moodlesso.sql', TRUE) );

        return TRUE;
    }

    public static function RenderParserFunction( $parser, $path='', $name='Moodle' ) {
        global $wgServer, $wgScriptPath;

        // all we need to do is to provide a link to the jump function
        $path = urlencode( $path );
        $link = $wgServer . '/' . $wgScriptPath . '/extensions/moodlesso/jump.php';
        return '['.$link."?path=$path $name]";
    }
}
