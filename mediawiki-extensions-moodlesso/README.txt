MOODLESSO extension
===================

This extension provides single sign on FROM MediaWiki TO Moodle.
In addition it provides a signon facility in Moodle to authenticate
against existing MediaWiki accounts (that have already been created 
by SSO). 

INSTALL
=======

1. In MediaWiki

Unzip the MediaWiki extension in $IP/extensions (as moodlesso)

in LocalSettings.php add the following lines at the end

    require_once( "$IP/extensions/moodlesso/moodlesso.php" );
    $wgMoodlessoUrl = 'http://my.moodle.org/moodle';

$wgMoodlessoUrl should be the url of your Moodle site (wwwroot). 

Then go to maintenance/ directory and do,

    php update.php

...to install the database table.


2. In Moodle

Unzip the mwsso plugin in Moodle's auth/ directory.

Visit 'Site administration > Notifications' to install.

Go to 'Site administration > Plugins > Authentication >
Manage authentication' enable the MediaWiki plugin (by opening the
'eye') and then go to its Settings page. There is only one setting, 
it is the URL of the MediaWiki site.

3. Usage

In a MediaWiki page, simply add.

{{#moodle: moodle_path | Text}}

- moodle_path is optional, and is the path within moodle you would
like to go to after login (e.g. /course/view.php?id=92). The Text
is whatever you would like to appear on the page (e.g. 'Click to go to 
your Moodle courses')
