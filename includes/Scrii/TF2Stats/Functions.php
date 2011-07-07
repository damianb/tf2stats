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

namespace Scrii\TF2Stats;
use OpenFlame\Framework\Core;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

function steamIdToSteamCommunity($steam_id)
{
	// mmmm regexp
	$count = preg_match('/STEAM_0:([01]):([0-9]+)/i', $steam_id, $matches);
	if(!$count)
	{
		return false;
	}
	list($steam_cid, ) = explode('.', bcadd((((int) $matches[2] * 2) + $matches[1]), '76561197960265728'), 2);
	return $steam_cid;
}

function steamCommunityToSteamId($steam_community)
{
	$c1 = substr($steam_community, -1, 1) % 2 == 0 ? 0 : 1;
	$c2 = bcsub($steam_community, '76561197960265728');
	if(bccomp($c2, '0') != 1)
	{
		return false;
	}
	$c2 = bcsub($c2, $c1);
	list($c2, ) = explode('.', bcdiv($c2, 2), 2);
	return "STEAM_0:$c1:$c2";
}
