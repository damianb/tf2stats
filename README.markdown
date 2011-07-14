# scrii.com tf2 stats web ui

## copyright

(c) 2011 - scrii.com

## license

All original code in this web application is licensed under the GNU General Public License version 3; a copy of the license is included in the file /LICENSE.

The [Twig templating library](http://twig-project.com) is licensed under the New BSD license, available here: https://github.com/fabpot/Twig/blob/master/LICENSE

The [Quartz usability layer](https://github.com/damianb/Quartz/tree/feature.injection) for the OpenFlame Framework is licensed under the MIT license, available here: https://github.com/damianb/Quartz/blob/master/LICENSE

The [OpenFlame Framework](https://github.com/OpenFlame/OpenFlame-Framework) and the [OpenFlame Dbal](https://github.com/OpenFlame/OpenFlame-Dbal) are licensed under the MIT license, available here:  https://github.com/OpenFlame/OpenFlame-Framework/blob/master/LICENSE

jQuery is dual-licensed under the [MIT License](http://github.com/jquery/jquery/blob/master/MIT-LICENSE.txt) and the [GPLv2](http://github.com/jquery/jquery/blob/master/GPL-LICENSE.txt).

The 960gs grid system is dual-licensed under the [MIT License](https://github.com/nathansmith/960-Grid-System/blob/master/licenses/MIT_license.txt) and the [GPLv3](https://github.com/nathansmith/960-Grid-System/blob/master/licenses/GPL_license.txt).

## requirements

* PHP 5.3.0 or newer (tested on PHP 5.3.1)
* `bcmath` extension must be available
* `pdo` extension must be available
* `pdo_mysql` must be available
* `register_globals` MUST be disabled in php.ini
* `magic_quotes_gpc` MUST be disabled in php.ini
* `magic_quotes_runtime` MUST be disabled in php.ini
* A Steam Web API key
* A Steam group

## dependencies

* Twig 1.1.0 (provided in a git submodule)
* OpenFlame Framework 1.2.0-dev (provided in a git submodule)
* OpenFlame Dbal 1.0.0-dev (provided in a git submodule)
* Quartz (provided in a git submodule)
* 960gs (provided)
* jQuery 1.6.2 (provided)

## installation

Installation of the tf2 stats web ui is fairly straightforward.

### editing the configuration file

To prepare your installation, rename or copy the file `data/config/config.example.json` to `data/config/config.json`, then open it up in your favorite unix-newline-safe editor (I recommend Notepad++ for Windows users).

In it are ten different settings, explained below:

* `db.host` - This setting should be the hostname used to connect to your database server.  In most circumstances, it is `localhost`.
* `db.name` - The name of your database (*not* database user).
* `db.username` - The database username to connect to the database with.
* `db.password` - The password to use with the datbase username to connect to the database with.
* `twig.debug` - Enables debug mode in the Twig template library.  Only needed for anyone modifying templates themselves.
* `site.debug` - Enables debug mode on the site itself.  The full error message, backtrace, and code context is displayed instead of a vague message.
* `site.use_gzip_assets` - The site will use gzip-compressed assets (CSS, JS files) instead of uncompressed files to save bandwidth if this setting is enabled.  **If you experience problems with page styling not appearing correctly, try disabling this setting first.**
* `page.base_url` - The base URL to use for all links generated on the site.  If you install the site to [http://mysite.com/], this would be "/", if you install the site to [http://mysite.com/tf2stats/] this would be "/tf2stats/", etc.
* `steam.webapikey` - Your personal Steam Web API key.  See the section "obtaining a Steam Web API key" below for more details if you do not have a Steam Web API key yet.
* `steam.groupurl` - The URL to your Steam group's profile page.  This should be something like `http://steamcommunity.com/groups/scrii/`.

Edit the configuration file with the appropriate values for each setting (take care not to break the JSON formatting!), then save the file and try to pull up the site.

If you:

* encounter issues loading the site, try enabling `site.debug` first before continuing.
* have issues connecting to the database, verify the `db.` settings with your host first.
* can load the page but think it looks jumbled and unstyled, try disabling the `site.use_gzip_assets` setting and see if that helps.

### obtaining a Steam Web API key

To obtain a Steam Web API key, please refer to [this page](http://steamcommunity.com/dev).

Also, please note that the "powered by steam" text and link in the footer may not be removed; it is required by the [Steam API terms of use](http://steamcommunity.com/dev/apiterms).

## easter eggs

There's one somewhere in it.  ;)

## todo

* Package and distribute the app with the the OpenFlame Framework,  OpenFlame Dbal, and Quartz contained in OpenSSL-signed phar packages.
* Timezone should be set in config file
* Move array of "weapon kill" data in Player controller to own json file for easy updating
* Update for new changes to Quartz
