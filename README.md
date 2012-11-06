MOODLESSO extension
===================

This extension provides single sign on FROM MediaWiki TO Moodle.
In addition it provides a signon facility in Moodle to authenticate
against existing MediaWiki accounts (that have already been created 
by SSO). 

INSTALL
-------

### In MediaWiki

Copy mediawiki-extensions-moodlesso to $IP/extensions/moodlesso

in LocalSettings.php add the following lines at the end

```PHP
require_once( "$IP/extensions/moodlesso/moodlesso.php" );
$wgMoodlessoUrl = 'http://my.moodle.org/moodle';
```

$wgMoodlessoUrl should be the url of your Moodle site (wwwroot). 

Then go to maintenance/ directory and on the command line do,

```
php update.php
```

...to install the database table.


### In Moodle

Copy the moodle-auth-mwsso plugin to auth/mwsso directory.

Visit 'Site administration > Notifications' to install.

Go to 'Site administration > Plugins > Authentication >
Manage authentication' enable the MediaWiki plugin (by opening the
'eye') and then go to its Settings page. There is only one setting, 
it is the URL of the MediaWiki site.

### Usage

In a MediaWiki page, simply add.

{{#moodle: moodle_path | Text}}

- moodle_path is optional, and is the path within moodle you would
like to go to after login (e.g. /course/view.php?id=92). The Text
is whatever you would like to appear on the page (e.g. 'Click to go to 
your Moodle courses')

When the page is rendered just the text appears as a link. Clicking it
will take you to the Moodle site logged in. If you are not logged into 
MediaWiki you will, instead, be taken to the login page. 

### Logging in from Moodle

The MediaWiki users are created in Moodle with their own auth type 'mwsso'.
No password is recorded in Moodle. If you log into Moodle with one of these
users authentication will be attempted by Moodle calling a web service
in the MediaWiki extension. If you convert them to manual users at any time
you will need to supply a new password

### Security

SSO security is by a one time token which with a time-limited lifespan. Note
that there is no encryption of data and the trust relationship is defined only
by the hostnames specified at each end. This may not be sufficient security for 
a sensitive site. This is up to you to decide. 
