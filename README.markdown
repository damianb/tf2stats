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

If you are running a phar-packaged version, your installation of PHP needs OpenSSL installed as well.

## dependencies

* Twig 1.1.2 (provided in a git submodule)
* OpenFlame Framework 1.2.0-dev (provided in a git submodule)
* OpenFlame Dbal 1.0.0-dev (provided in a git submodule)
* Quartz (provided in a git submodule)
* 960gs (provided)
* jQuery 1.6.2 (provided)

## installation

Installation of the tf2 stats web ui is fairly straightforward.

### editing the configuration file

To prepare your installation, rename or copy the file `data/config/config.example.json` to `data/config/config.json`, then open it up in your favorite unix-newline-safe editor (I recommend Notepad++ for Windows users).

In it are several different settings, explained below:

* `db.host` - This setting should be the hostname used to connect to your database server.  In most circumstances, it is `localhost`.
* `db.name` - The name of your database (*not* database user).
* `db.username` - The database username to connect to the database with.
* `db.password` - The password to use with the datbase username to connect to the database with.
* `twig.debug` - Enables debug mode in the Twig template library.  Only needed for anyone modifying templates themselves.
* `site.debug` - Enables debug mode on the site itself.  The full error message, backtrace, and code context is displayed instead of a vague message.  Also, if `site.use_js` is enabled, additional code timing information is displayed in the page footer.
* `site.use_js` - Embeds jQuery and the tf2.js file into the entire site.  Enable only if you require the use of jQuery.
* `site.use_jquery_cdn` - If `site.use_js` is enabled, this will embed the Google CDN copy of jQuery in the page, instead of the local copy.
* `site.use_gzip_assets` - The site will use gzip-compressed assets (CSS, JS files) instead of uncompressed files to save bandwidth if this setting is enabled.  **If you experience problems with page styling not appearing correctly, try disabling this setting first.**
* `site.timezone` - A valid DateTime timezone.  Please reference [http://us3.php.net/manual/en/timezones.php] to locate your timezone.
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

## troubleshooting

### enabling pretty urls

To enable the "pretty URLs" mode, first check that you are running Apache and that you have the modules `mod_rewrite` and `mod_env` enabled (`mod_env` is needed to let the script know that rewriting is enabled).

Now...

* open up the file `.htaccess` in your installation
* locate the section regarding rewriting
* uncomment the lines as instructed - just don't remove anything after the first comment hash (the `#` symbol)
* locate the line `RewriteBase /tf2stats` within the uncommented section, and change this to match the path you set in the `page.base_path` configuration setting ( ***DO NOT LEAVE A TRAILING SLASH FOR THIS LINE*** )
* save the file
* access your site and look at the links to players, the top 10 listing, etc.  If you're being sent to `/tf2stats/top10/` for example, everything is working fine.

### enabling compressed stylesheets/js files

Depending on your system, enabling compressed stylesheets/javascript files may or may not work out of the box.  Go ahead and set the site config setting `site.use_gzip_assets` to **true** to see if your server sends the files with the correct headers.  If you load the page and the styling is screwed up beyond belief, read on.  Otherwise, you're all set.

Due to a rather...*odd* choice by the Ubuntu repository maintainers, Apache's `mime_module` configuration file declares files ending with the extension .gz to be the wrong content type.  Instead of declaring the content encoding as `x-gzip` (the code to do this is actually commented out), the maintainers decided to declare the content type to be `application/x-gzip`, which will cause most browsers to not use the data within as merely compressed content.

To fix this problem on Ubuntu server 10.04 LTS (may apply to other versions):

* open up the file `/etc/apache2/mods-available/mime.conf`
* locate the line `# AddEncoding x-gzip .gz .tgz` and uncomment it (remove the **#** at the beginning of the line)
* locate the line `AddType application/x-gzip .gz .tgz` and comment it out (add a **#** at the beginning of the line)
* restart/reload Apache with `sudo service apache2 reload` or `sudo service apache2 restart`
* clear your browser cache
* reload the page and see if the page styling loads properly
