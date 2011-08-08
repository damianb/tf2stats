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
use \Codebite\Quartz\Site as Quartz;
use \Codebite\Quartz\Internal\RequirementException;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Autoloader;
use \OpenFlame\Framework\Event\Instance as Event;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Framework\Exception\Handler as ExceptionHandler;

define('Scrii\\TF2Stats\\VERSION', '1.1.0-beta1');

// Our secondary bootstrap file (quartz), and the functions file...
require \Scrii\TF2Stats\ROOT_PATH . '/includes/Codebite/Quartz/Site.php';
// Define include paths
Quartz::definePaths(\Scrii\TF2Stats\ROOT_PATH);

require \Codebite\Quartz\INCLUDE_ROOT . '/Scrii/Functions.php';

//$quartz = new \Codebite\Quartz\Site();
$quartz = Quartz::getInstance();
$quartz->init();

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

// Verify that the bcmath requirement is met
if(!@extension_loaded('bcmath'))
{
	throw new RequirementException('Application will not run without the bcmath extension; please enable or load the bcmath extension to run this application.', 1004);
}

if(!defined('Scrii\\TF2Stats\\ENABLE_BANREASON'))
{
	define('Scrii\\TF2Stats\\ENABLE_BANREASON', false);
}

/**
 * Define some of our own injectors
 */

$quartz->setInjector('simplerouter', function() {
	return new \Scrii\TF2Stats\Router\SimpleRouter();
});

$quartz->setInjector('steamgroup', function() {
	$group_url = Core::getConfig('steam.groupurl');
	$web_api_key = Core::getConfig('steam.webapikey');

	if($group_url === NULL || $web_api_key === NULL)
	{
		throw new RequirementException('Required configs "steam.groupurl" and "steam.webapikey" are not defined.', 1005);
	}

	$steam = new \Scrii\Steam\Group($group_url, $web_api_key);

	return $steam;
});

$quartz->connectToDatabase('mysql')
	->loadConfig('assets')
	->setAssets();

if(\Scrii\TF2Stats\REWRITING_ENABLED)
{
	$quartz->loadConfig('routes')
		->setRoutes();

	$quartz->url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$quartz->url->newPattern('groupRanking', 'group/');
	$quartz->url->newPattern('playerProfile', 'player/%s/');
	$quartz->url->newPattern('serverRanking', 'list/');
	$quartz->url->newPattern('serverRankingPage', 'list/%d/');
	$quartz->url->newPattern('weaponList', 'weapons/');
	$quartz->url->newPattern('weaponRank', 'weapon/%s/');
	$quartz->url->newPattern('weaponRankPage', 'weapon/%s/%d/');
	$quartz->url->newPattern('top10', 'top10/');
}
else
{
	$quartz->simplerouter->newRoute('home', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$quartz->simplerouter->newRoute('error', '\\Scrii\\TF2Stats\\Page\\Instance\\Error');
	$quartz->simplerouter->newRoute('group', '\\Scrii\TF2Stats\Page\Instance\\Home');
	$quartz->simplerouter->newRoute('player', '\\Scrii\TF2Stats\Page\Instance\\Player');
	$quartz->simplerouter->newRoute('list', '\\Scrii\TF2Stats\Page\Instance\\ListPlayers');
	$quartz->simplerouter->newRoute('weapons', '\\Scrii\TF2Stats\Page\Instance\\ListWeapons');
	$quartz->simplerouter->newRoute('weapon', '\\Scrii\TF2Stats\Page\Instance\\PlayerWeaponRanking');
	$quartz->simplerouter->newRoute('top10', '\\Scrii\TF2Stats\Page\Instance\\Top10');

	$quartz->url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$quartz->url->newPattern('groupRanking', '?page=group');
	$quartz->url->newPattern('playerProfile', '?page=player&steam=%s');
	$quartz->url->newPattern('serverRanking', '?page=list');
	$quartz->url->newPattern('serverRankingPage', '?page=list&p=%d');
	$quartz->url->newPattern('weaponList', '?page=weapons');
	$quartz->url->newPattern('weaponRank', '?page=weapon&weapon=%s');
	$quartz->url->newPattern('weaponRankPage', '?page=weapon&weapon=%s&p=%d');
	$quartz->url->newPattern('top10', '?page=top10');
}

/**
 * Define extra listeners
 */

$quartz->setListener('page.execute', 5, function(Event $event) use($quartz) {
	$quartz->header->removeHeader('X-Powered-By')
		->setHeader('Content-Type', 'text/html') // content type, should be text/html because we're html5
		->setHeader('Cache-Control', 'no-cache') // prevent caching
		->setHeader('Pragma', 'no-cache') // prevent caching
		->setHeader('X-Frame-Options', 'DENY') // NO FRAMES.
		->setHeader('X-XSS-Protection', '1; mode=block') // IE8 header
		->setHeader('X-Content-Type-Options', 'nosniff') // Chromium, IE8 implement this.
		->setHeader('X-App-Version', 'scrii tf2 stats web ui ' . \Scrii\TF2Stats\VERSION);

	if(Core::getConfig('site.enable_csp'))
	{
		$csp_header = 'default-src \'self\'; img-src \'self\' media.steampowered.com; script-src \'self\' *.googleapis.com; font-src \'self\' fonts.googleapis.com; style-src \'self\' fonts.googleapis.com';
		// Will need updated later to match the W3C spec for CSP that's currently in progress.
		$quartz->header->setHeader('X-Content-Security-Policy', $csp_header);
		//$quartz->header->setHeader('X-WebKit-CSP', $csp_header); // X-WebKit-CSP is currently broken
		//$quartz->header->setHeader('Content-Security-Policy', $csp_header); // when the CSP specification is complete, this should be safe to uncomment. :)
	}
});

$quartz->setListener('page.display', -10, function(Event $event) use($quartz) {
	$quartz->template->assignVars(array(
		'SCRII_TF2_VERSION'		=> \Scrii\TF2Stats\VERSION,
		'use_gzip_content'		=> Core::getConfig('site.use_gzip_assets'),
		'use_js'				=> Core::getConfig('site.use_js'),
		'use_jquery_cdn'		=> Core::getConfig('site.use_jquery_cdn'),
		'timetracker'			=> (Core::getConfig('site.debug') && Core::getConfig('site.use_js')) ? $quartz->debugtime : NULL,
	));
});

// Simplerouter listener, allows overriding the native OFF router's listener
$quartz->setListener('page.route', -5, function(Event $event) use($quartz) {
	// If we're using rewriting, skip this listener!
	if(\Scrii\TF2Stats\REWRITING_ENABLED)
	{
		return;
	}

	$dbg_instance = $dbg_instance2 = NULL;
	$quartz->debugtime->newEntry('app->route', '', $dbg_instance);

	$p = $quartz->input->getInput('GET::page', 'home')
		->getClean();

	$page = $quartz->simplerouter->getPage($p);

	$quartz->debugtime->newEntry('app->route', 'Application route parsed', $dbg_instance, array('request' => $quartz->input->getInput('SERVER::REQUEST_URI', '/')->getClean()));
	$quartz->debugtime->newEntry('app->executepage', '', $dbg_instance2);

	Core::setObject('page', $page);
	$page->executePage();

	$quartz->debugtime->newEntry('app->executepage', 'Application page execution complete', $dbg_instance2);

	// prevent the other listener from firing
	$event->breakTrigger();
});
