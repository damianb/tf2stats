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

namespace Scrii\TF2Stats\Page\Instance;
use \Codebite\Quartz\Site as Quartz;
use \Codebite\Quartz\Dbal\Query;
use \Codebite\Quartz\Dbal\QueryBuilder;
use \OpenFlame\Framework\Core;
use \Scrii\Steam\SteamID;

class Home extends \Scrii\TF2Stats\Page\Base
{
	protected $template_name = 'home.twig.html';

	public function executePage()
	{
		$quartz = Quartz::getInstance();

		$dbg_instance = NULL;
		$quartz->debugtime->newEntry('steam->getgroupmembers', '', $dbg_instance);
		$quartz->steamgroup->getGroupMembers();
		$quartz->debugtime->newEntry('steam->getgroupmembers', 'Fetched steam group members (20 minute cache)', $dbg_instance);

		$where = array();
		foreach($quartz->steamgroup->members as $member)
		{
			// Make sure this is a valid steam ID...if it's just digits, it should be safe.
			if(!ctype_digit($member))
			{
				continue;
			}

			// convert to steamID32 format.
			try
			{
				$steam_id = new SteamID($member);
			}
			catch(\RuntimeException $e)
			{
				continue;
			}

			$where[] = $steam_id->getSteamID32();
		}

		if($quartz->steamgroup->unavailable === true)
		{
			$quartz->template->assignVar('unavailable', true);
		}

		$q = Query::newInstance();
		$q->sql('SELECT p.STEAMID, p.NAME, p.POINTS, p.PLAYTIME, p.LASTONTIME
			FROM Player p
			WHERE p.STEAMID IN ("' . implode('", "', $where) . '")
			ORDER BY p.POINTS DESC');

		$rows = array();
		$timezone = new \DateTimeZone(Core::getConfig('site.timezone') ?: 'America/New_York');
		$utc = new \DateTimeZone('UTC');
		$i = 0;
		while($row = $q->fetchRow())
		{
			// handle playtime calculations
			// whee math!
			$playtime = array();
			$playtime['s'] = (int) $row['PLAYTIME'] * 60;
			// calc days
			$playtime['d'] = (int) floor($playtime['s'] / 60 / 60 / 24);
			$playtime['s'] -= $playtime['d'] * (60 * 60 * 24);
			// calc hours
			$playtime['h'] = (int) floor($playtime['s'] / 60 / 60);
			$playtime['s'] -= $playtime['h'] * (60 * 60);
			// calc minutes
			$playtime['m'] = (int) floor($playtime['s'] / 60);
			$playtime['s'] -= $playtime['m'] * 60;

			// figure out plurals
			$playtime = array_merge($playtime, array(
				'plural_d'		=> ($playtime['d'] == 1) ? '' : 's',
				'plural_h'		=> ($playtime['h'] == 1) ? '' : 's',
				'plural_m'		=> ($playtime['m'] == 1) ? '' : 's',
			));

			if($playtime['d'] > 0)
			{
				$format = '%2$d day%5$s %3$d hr%6$s %4$d min%7$s';
			}
			elseif($playtime['h'] > 0)
			{
				$format = '%3$d hr%6$s %4$d min%7$s';
			}
			else
			{
				$format = '%4$d min%7$s';
			}
			$row['playspan'] = vsprintf($format, $playtime);

			$steam_id = new SteamID($row['STEAMID']);
			$row['steamid64'] = $steam_id->getSteamID64();
			$row['ismember'] = in_array($row['steamid64'], $quartz->steamgroup->members, true) ? true : false;

			$online = new \DateTime('@' . $row['LASTONTIME']);
			$online->setTimeZone($timezone);
			$utc_online = new \DateTime('@' . $row['LASTONTIME']);
			$utc_online->setTimeZone($utc);
			$row['lastonline'] = $online->format(\DateTime::RSS);
			$row['lastonline_utc'] = $utc_online->format(\DateTime::RSS);
			$row['rank'] = ++$i;

			$rows[] = $row;
		}

		if(empty($rows))
		{
			$quartz->template->assignVar('noresults', true);
			return;
		}

		$quartz->template->assignVars(array(
			'members'		=> count($rows),
			'data'			=> $rows,
		));

		return;
	}
}
