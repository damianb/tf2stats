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
use Codebite\Quartz\Site as Quartz;
use Codebite\Quartz\Internal\RequirementException;
use OpenFlame\Framework\Core;
use OpenFlame\Framework\Autoloader;
use OpenFlame\Framework\Event\Instance as Event;
use OpenFlame\Framework\Dependency\Injector;
use OpenFlame\Framework\Exception\Handler as ExceptionHandler;

// Our secondary bootstrap file (quartz), and the functions file...
require \Scrii\TF2Stats\ROOT_PATH . '/Codebite/Quartz/Site.php';
require \Scrii\TF2Stats\ROOT_PATH . '/Scrii/Functions.php';
Quartz::definePaths(\Scrii\TF2Stats\ROOT_PATH);
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

$quartz->loadConfig('assets')
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
	$quartz->simplerouter->newRoute('top10', '\\Scrii\TF2Stats\Page\Instance\\Top10');

	$quartz->url->newPattern('groupRanking', ''); // URL looks nicer this way :D
	//$quartz->url->newPattern('groupRanking', '?page=group');
	$quartz->url->newPattern('playerProfile', '?page=player&steam=%s');
	$quartz->url->newPattern('serverRanking', '?page=list');
	$quartz->url->newPattern('serverRankingPage', '?page=list&p=%d');
	$quartz->url->newPattern('weaponList', '?page=weapons');
	$quartz->url->newPattern('weaponRank', '?page=weapon&weapon=%s');
	$quartz->url->newPattern('top10', '?page=top10');
}

/**
 * Define extra listeners
 */

$quartz->setListener('page.execute', 5, function(Event $event) use($quartz) {
	$quartz->header->removeHeader('X-Powered-By')
		->setHeader('X-Frame-Options', 'DENY') // NO FRAMES.
		->setHeader('X-App-Version', 'scrii tf2 stats web ui ' . \Scrii\TF2Stats\VERSION);
});

$quartz->setListener('page.display', -10, function(Event $event) use($quartz) {
	$quartz->template->assignVars(array(
		'SCRII_TF2_VERSION'		=> \Scrii\TF2Stats\VERSION,
		'use_gzip_content'		=> Core::getConfig('site.use_gzip_assets'),
	));
});

// Simplerouter listener, allows overriding the native OFF router's listener
$quartz->setListener('page.route', -5, function(Event $event) use($quartz) {
	// If we're using rewriting, skip this listener!
	if(\Scrii\TF2Stats\REWRITING_ENABLED)
	{
		return;
	}

	$p = $quartz->input->getInput('GET::page', 'home')
		->disableFieldJuggling()
		->getClean();

	$page = $quartz->simplerouter->getPage($p);

	Core::setObject('page', $page);
	$page->executePage();

	// prevent the other listener from firing
	$event->breakTrigger();
});
