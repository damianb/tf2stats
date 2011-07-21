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

namespace Scrii\TF2Stats;
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

define('Scrii\\TF2Stats\\VERSION', '1.0.2-dev');
/**
 * banreason support is disabled by default in this script, as it requires database structure modifications
 * to enable banreason support, use this query, and uncomment the constant declaration line below (remove the //):
 *
 * 'ALTER TABLE `player` ADD `BANREASON` VARCHAR( 255 ) NOT NULL AFTER `KILLS`;'
 */
//define('Scrii\\TF2Stats\\ENABLE_BANREASON', true);

/**
 * Check to see if URL rewriting support is enabled and in use.
 */
define('Scrii\\TF2Stats\\REWRITING_ENABLED', (getenv('HTTP_USING_MOD_REWRITE') == 'On' ? true : false));

/**
 * Idiot checks...make sure stupid settings aren't being used.
 * Under no circumstances should these checks be removed.
 */
$debug = Core::getConfig('site.debug') ?: false;
$injector = Injector::getInstance();
$dispatcher = $injector->get('dispatcher');
$dispatcher->trigger(Event::newEvent('debug.enable'));

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
	$dispatcher->trigger(Event::newEvent('debug.disable'));
}

if(!defined('Scrii\\TF2Stats\\ENABLE_BANREASON'))
{
	define('Scrii\\TF2Stats\\ENABLE_BANREASON', false);
}

/**
 * Define some of our own injectors
 */

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
		->setHeader('X-Frame-Options', 'DENY') // NO FRAMES.
		->setHeader('X-App-Version', 'scrii tf2 stats web ui ' . \Scrii\TF2Stats\VERSION);
});

/**
 * Setup second-tier listeners
 */

// Add in our own assets.
$dispatcher->register('page.assets.define', 5, function(Event $event) use($dispatcher) {
	$dispatcher->triggerUntilBreak(Event::newEvent('page.assets.autodefine'));
});

$dispatcher->register('page.routes.load', 10, function(Event $event) use($injector) {
	$url = $injector->get('url_builder');

	$url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$url->newPattern('groupRanking', 'group/');
	$url->newPattern('playerProfile', 'player/%s/');
	$url->newPattern('serverRanking', 'list/');
	$url->newPattern('serverRankingPage', 'list/%d/');
	$url->newPattern('top10', 'top10/');
});

$dispatcher->register('page.simpleroutes.load', 5, function(Event $event) use($injector) {
	$router = $injector->get('simplerouter');

	$router->newRoute('home', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$router->newRoute('error', '\\Scrii\\TF2Stats\\Page\\Instance\\Error');
	$router->newRoute('group', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$router->newRoute('player', '\\Scrii\TF2Stats\Page\Instance\\Player');
	$router->newRoute('list', '\\Scrii\TF2Stats\Page\Instance\\ListPlayers');
	$router->newRoute('top10', '\\Scrii\TF2Stats\Page\Instance\\Top10');
});

$dispatcher->register('page.simpleroutes.load', 10, function(Event $event) use($injector) {
	$url = $injector->get('url_builder');

	$url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$url->newPattern('groupRanking', '?page=group');
	$url->newPattern('playerProfile', '?page=player&steam=%s');
	$url->newPattern('serverRanking', '?page=list');
	$url->newPattern('serverRankingPage', '?page=list&p=%d');
	$url->newPattern('top10', '?page=top10');
});

// Prepare page elements (assets, routes, language file stuff, etc.)
$dispatcher->register('page.prepare', 0, function(Event $event) use($injector, $dispatcher) {
	$url = $injector->get('url_builder');
	$asset_manager = $injector->get('asset');
	$template = $injector->get('template');

	$url->setBaseURL($asset_manager->getBaseURL());

	if(\Scrii\TF2Stats\REWRITING_ENABLED)
	{
		$dispatcher->trigger(Event::newEvent('page.routes.load'));
	}
	else
	{
		$dispatcher->trigger(Event::newEvent('page.simpleroutes.load'));
	}

	$template->assignVar('SCRII_TF2_VERSION', \Scrii\TF2Stats\VERSION);
	$template->assignVar('use_gzip_content', Core::getConfig('site.use_gzip_assets'));

	$dispatcher->trigger(Event::newEvent('page.headers.snag'));
	$dispatcher->trigger(Event::newEvent('page.assets.define'));
});

// Execute the page logic
$dispatcher->register('page.execute', 5, function(Event $event) use($injector) {
	// If we're using rewriting, skip this listener!
	if(\Scrii\TF2Stats\REWRITING_ENABLED)
	{
		return;
	}

	$input = $injector->get('input');
	$router = $injector->get('simplerouter');
	$dispatcher = $injector->get('dispatcher');

	$p = $input->getInput('GET::page', 'home')
		->disableFieldJuggling()
		->getClean();
	$page = $router->getPage($p);

	Core::setObject('page', $page);
	$page->executePage();

	// prevent the other listener from firing
	$event->breakTrigger();
});
