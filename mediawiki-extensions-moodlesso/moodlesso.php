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

// extension details
$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'moodlesso',
    'description' => 'Single Sign On (SSO) from MediaWiki to Moodle 2',
    'version' => '0.1',
    'author' => 'Howard Miller',
    'url' => 'http://www.e-learndesign.co.uk',
    );

// load our extension class
$wgAutoloadClasses['moodlessoHooks'] = dirname(__FILE__) . '/moodlesso.hooks.php';

// specify callback function to initialise parser function
$wgHooks['ParserFirstCallInit'][] = 'moodlessoHooks::SetupParserFunction';

// specify callback to add database table
$wgHooks['LoadExtensionSchemaUpdates'][] = 'moodlessoHooks::SetupDatabase';

// allow translation of the parser function name (!?)
$wgExtensionMessagesFiles['moodlessoMagic'] = dirname(__FILE__) . '/moodlesso.i18n.magic.php';

