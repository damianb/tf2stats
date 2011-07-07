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
 * @copyright   (c) 2010 - 2011 scrii.com
 * @license     GPLv3
 *
 *===================================================================
 *
 */

namespace Scrii;
use OpenFlame\Framework\Core;
use OpenFlame\Framework\Event\Instance as Event;
use OpenFlame\Framework\Dependency\Injector;
use OpenFlame\Framework\Exception\Handler as ExceptionHandler;
use OpenFlame\Dbal\Connection as DbalConnection;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

// Our secondary bootstrap file (quartz), and the functions file...
require \Scrii\TF2Stats\ROOT_PATH . '/Codebite/Quartz/Bootstrap.php';
require \Scrii\TF2Stats\ROOT_PATH . '/Scrii/Functions.php';
require \Scrii\TF2Stats\ROOT_PATH . '/Scrii/TF2Stats/Functions.php';

define('Scrii\\TF2Stats\\VERSION', '1.0.0');
/**
 * banreason support is disabled by default in this script, as it requires database structure modifications
 * to enable banreason support, use this query, and uncomment the constant declaration line below (remove the //):
 *
 * 'ALTER TABLE `player` ADD `BANREASON` VARCHAR( 255 ) NOT NULL AFTER  `KILLS`;'
 */
//define('Scrii\\TF2Stats\\ENABLE_BANREASON', true);

/**
 * Idiot checks...make sure stupid settings aren't being used.
 * Under no circumstances should these checks be removed.
 */
$debug = ExceptionHandler::getDebugState();
if(!$debug)
{
	ExceptionHandler::enableDebug();
}
if(@ini_get('register_globals'))
{
	throw new \RuntimeException('Web UI will not run with register_globals enabled; please disable register_globals to run the script.');
}
if(@get_magic_quotes_gpc())
{
	throw new \RuntimeException('Web UI will not run with magic_quotes_gpc enabled; please disable magic_quotes_gpc to run the script.');
}
if(@get_magic_quotes_runtime())
{
	throw new \RuntimeException('Web UI will not run with magic_quotes_runtime enabled; please disable magic_quotes_runtime to run the script.');
}
if(!@extension_loaded('bcmath'))
{
	throw new \RuntimeException('Web UI will not run without the bcmath extension; please enable or load the bcmath extension to run the script.');
}
if(!$debug)
{
	ExceptionHandler::disableDebug();
}

// debug modo
if(Core::getConfig('site.debug') == true)
{
	@ini_set("display_errors", "On");
	@error_reporting(E_ALL);
	ExceptionHandler::enableDebug();
}
if(!defined('Scrii\\TF2Stats\\ENABLE_BANREASON'))
{
	define('Scrii\\TF2Stats\\ENABLE_BANREASON', false);
}

/**
 * Define some of our own injectors
 */

$injector = Injector::getInstance();

$injector->setInjector('db', function() {
	$dsn = 'mysql:host=' . (Core::getConfig('db.host') ?: 'localhost') . ';dbname=' . (Core::getConfig('db.name') ?: 'tf2stats');
	$username = Core::getConfig('db.username') ?: 'tf2stats';
	$password = Core::getConfig('db.password') ?: '';
	$options = array(
		\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
	);
	$connection = DbalConnection::getInstance()
		->connect($dsn, $username, $password, $options);

	return $connection;
});

$injector->setInjector('simplerouter', function() {
	return new \Scrii\TF2Stats\Router\SimpleRouter();
});

$injector->setInjector('steamgroup', function() {
	$group_url = Core::getConfig('steam.groupurl');
	$web_api_key = Core::getConfig('steam.webapikey');

	if($group_url === NULL || $web_api_key === NULL)
	{
		throw new \RuntimeException('Required configs "steam.groupurl" and "steam.webapikey" are not defined.');
	}

	$steam = new \Scrii\Steam\Group($group_url, $web_api_key);

	return $steam;
});

/**
 * Define extra primary-tier listeners
 */

 // Remove the PHP X-Powered-By header to help deter vuln sniffing
$dispatcher->register('page.headers.send', 5, function(Event $event) use($injector) {
	$header_manager = $injector->get('header');
	$header_manager->removeHeader('X-Powered-By')
		->setHeader('X-App-Version', 'scrii tf2 stats web ui ' . \Scrii\TF2Stats\VERSION);
});

/**
 * Setup second-tier listeners
 */

// Add in our own assets.
$dispatcher->register('page.assets.define', 5, function(Event $event) use($injector) {
	$asset_manager = $injector->get('asset');

	$assets = array(
		'css'	=> array(
			'grid'			=> '960.min.css',
			'grid_min'		=> '960.min.css.gz',
			'common'		=> 'tf2.css',
			'common_min'	=> 'tf2.min.css.gz',
		),
		'js'	=> array(
			'jquery'		=> 'jquery-1.6.2.min.js',
			'jquery_min'	=> 'jquery-1.6.2.min.js.gz',

			/**
			 * @note: reason all JS is isolated into an external file is to make it easy to support CSP (Content Security Policy) in the future
			 * see: https://developer.mozilla.org/en/introducing_content_security_policy
			 *
			 * CSP is only fully supported in Firefox 4 and above; no other browser fully implements CSP at this time
			 * Some experimental support for CSP has been added in Chromium 13, however it is not yet completely supported
			 * The CSP implementation in Chromium also uses a different (proprietary) header, so it must be addressed/supported separately.
			 */
			'common'	=> 'tf2.js',
		),
		'image' => array(
			'shana_error'	=> 'shana_error.jpg',
			'killicon'		=> 'killicon/Killicon_',
			'bannedavvy'	=> 'banavatar.png',
		),
	);

	if(isset($assets['css']) && !empty($assets['css']))
	{
		foreach($assets['css'] as $asset_name => $asset_url)
		{
			$asset_manager->registerCSSAsset($asset_name)
				->setURL('/style/css/' . $asset_url);
		}
	}
	if(isset($assets['js']) && !empty($assets['js']))
	{
		foreach($assets['js'] as $asset_name => $asset_url)
		{
			$asset_manager->registerJSAsset($asset_name)
				->setURL('/style/js/' . $asset_url);
		}
	}
	if(isset($assets['image']) && !empty($assets['image']))
	{
		foreach($assets['image'] as $asset_name => $asset_url)
		{
			$asset_manager->registerImageAsset($asset_name)
				->setURL('/style/img/' . $asset_url);
		}
	}
});

// Prepare page elements (assets, routes, language file stuff, etc.)
$dispatcher->register('page.prepare', 0, function(Event $event) use($injector) {
	$dispatcher = $injector->get('dispatcher');
	$router = $injector->get('simplerouter');
	$url = $injector->get('url_builder');
	$asset_manager = $injector->get('asset');
	$template = $injector->get('template');

	$router->newRoute('error', '\\Scrii\\TF2Stats\\Page\\Instance\\Error');
	$router->newRoute('home', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$router->newRoute('group', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$router->newRoute('player', '\\Scrii\TF2Stats\Page\Instance\\Player');
	$router->newRoute('list', '\\Scrii\TF2Stats\Page\Instance\\ListPlayers');
	$router->newRoute('top10', '\\Scrii\TF2Stats\Page\Instance\\Top10');

	$url->setBaseURL($asset_manager->getBaseURL());
	$url->newPattern('playerProfile', '?page=player&steam=%s');
	$url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$url->newPattern('groupRanking', '?page=group');
	$url->newPattern('serverRanking', '?page=list&p=%d');
	$url->newPattern('top10', '?page=top10');

	$template->assignVar('SCRII_TF2_VERSION', \Scrii\TF2Stats\VERSION);
	$template->assignVar('use_gzip_content', Core::getConfig('site.use_gzip_assets'));

	$dispatcher->trigger(Event::newEvent('page.headers.snag'));
	$dispatcher->trigger(Event::newEvent('page.assets.define'));
});

// Execute the page logic
$dispatcher->register('page.execute', 5, function(Event $event) use($injector) {
	$input = $injector->get('input');
	$router = $injector->get('simplerouter');
	$dispatcher = $injector->get('dispatcher');
	$db = $injector->get('db');

	$p = $input->getInput('GET::page', 'home')
		->disableFieldJuggling()
		->getClean();
	$page = $router->getPage($p);

	Core::setObject('page', $page);
	$page->executePage();
	$dispatcher->triggerUntilBreak(Event::newEvent('page.display'));

	// override the other listener
	$event->breakTrigger();
});
