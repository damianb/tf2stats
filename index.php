<?php
/**
 *
 *===================================================================
 *
 *  tf2stats-webui
 *-------------------------------------------------------------------
 * @category    tf2stats-webui
 * @package     tf2stats-webui
 * @author      Damian Bushong
 * @copyright   (c) 2011 scrii.com
 * @license     GPLv3
 *
 *===================================================================
 *
 */

// Not sure if this will actually work or not.  Hope it does, people need to know if their server doesn't support this or not. :\
if(version_compare(PHP_VERSION, '5.3.0', '<'))
{
	printf('<!DOCTYPE html><html lang="en-us"><head><meta charset="utf-8" /><title>Fatal error</title></head><body><h1>Fatal error</h1><p>The scrii tf2 stats web ui requires PHP 5.3.0 or newer; your current webserver installation only provides PHP %s.<br />Please contact your host and request an upgrade.  At the time of this script&apos;s release, only PHP 5.3.x is supported by The PHP Group (the developers of PHP).  All previous release lines have been declared end of life and no longer receive official security patches, maintenance, or updates.</p></body></html>', PHP_VERSION);
	exit;
}

define('TF2STATS_USE_PHAR', false);

// Define required constants and load the bootstrap file
define('Codebite\\Quartz\\SITE_ROOT', dirname(__FILE__) . '/');
if(TF2STATS_USE_PHAR)
{
	define('Scrii\\TF2Stats\\ROOT_PATH', 'phar://' . dirname(__FILE__) . '/lib/tf2stats.phar/');
	require 'phar://' . dirname(__FILE__) . '/lib/tf2stats.phar/includes/Scrii/TF2Stats/Bootstrap.php';
}
else
{
	define('Scrii\\TF2Stats\\ROOT_PATH', dirname(__FILE__));
	require dirname(__FILE__) . '/includes/Scrii/TF2Stats/Bootstrap.php';
}

$quartz->pagePrepare()
	->pageExecute()
	->pageDisplay();
