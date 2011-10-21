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

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

/**
 * A time-insensitive string comparison function, to help deter highly accurate timing attacks.
 * @param string $a - The first string to compare
 * @param string $b - The second string to compare
 * @return boolean - Do the strings match?
 *
 * @license - Public Domain - http://twitter.com/padraicb/status/41055320243437568
 * @link http://blog.astrumfutura.com/2010/10/nanosecond-scale-remote-timing-attacks-on-php-applications-time-to-take-them-seriously/
 * @author http://twitter.com/padraicb
 */
function full_compare($a, $b)
{
	if(strlen($a) !== strlen($b))
		return false;

	$result = 0;

	for($i = 0, $size = strlen($a); $i < $size; $i++)
		$result |= ord($a[$i]) ^ ord($b[$i]);

	return $result == 0;
}

function getEventTime($timestamp)
{
	$timezone = new \DateTimeZone(Core::getConfig('site.timezone') ?: 'America/New_York');
	$time = new \DateTime('@' . (int) $timestamp);
	$utctime = new \DateTime('@' . (int) $timestamp);
	$utc = new \DateTimeZone('UTC');
	$time->setTimezone($timezone);
	$utctime->setTimezone($utc);

	return array(
		'w3c'		=> $utctime->format(DATE_W3C),
		'readable'	=> $time->format('F j, Y'),
		'title'		=> $time->format('Y-d-m H:i:s'),
	);
}
