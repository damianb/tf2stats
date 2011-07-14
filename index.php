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

use OpenFlame\Framework\Core;
use OpenFlame\Framework\Autoloader;
use OpenFlame\Framework\Dependency\Injector;
use OpenFlame\Framework\Event\Instance as Event;
use OpenFlame\Framework\Exception\Handler as ExceptionHandler;

// Required constants for Quartz and OpenFlame Framework
define('Codebite\\Quartz\\SITE_ROOT', __DIR__);
// @deprecated
define('OpenFlame\\ROOT_PATH', \Codebite\Quartz\SITE_ROOT . '/includes/');
define('Scrii\\TF2Stats\\ROOT_PATH', \Codebite\Quartz\SITE_ROOT . '/includes/');

// Load the OpenFlame Framework autoloader
require \Scrii\TF2Stats\ROOT_PATH . '/OpenFlame/Framework/Autoloader.php';
$autoloader = Autoloader::register(\Scrii\TF2Stats\ROOT_PATH);

ExceptionHandler::register();
ExceptionHandler::setUnwrapCount(2);

// Load up the bootstrap file
require \Scrii\TF2Stats\ROOT_PATH . '/Scrii/TF2Stats/Bootstrap.php';

/**
 * Fire off our events!
 */
$injector = Injector::getInstance();
$dispatcher = $injector->get('dispatcher');

/**
 * - Load essential services
 * - Prepare page elements (assets, routes, language file stuff, etc.)
 * - Execute page handling logic & display the page!
 */
$dispatcher->triggerUntilBreak(Event::newEvent('page.prepare'));
$dispatcher->triggerUntilBreak(Event::newEvent('page.execute'));
$dispatcher->triggerUntilBreak(Event::newEvent('page.display'));
