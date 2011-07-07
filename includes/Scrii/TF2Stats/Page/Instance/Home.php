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

namespace Scrii\TF2Stats\Page\Instance;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;

class Home extends \Scrii\TF2Stats\Page\Base
{
	protected $template_name = 'home.twig.html';

	public function executePage()
	{
		$injector = Injector::getInstance();
		$template = $injector->get('template');

		$steam = $injector->get('steamgroup');
		$steam->getGroupMembers();

		$where = array();
		foreach($steam->members as $member)
		{
			if(!ctype_digit($member))
			{
				continue;
			}

			// convert to STEAM_0: ID.
			$steamid = \Scrii\TF2Stats\steamCommunityToSteamId($member);
			if($steamid === false)
			{
				continue;
			}

			$where[] = $steamid;
		}

		$q = Query::newInstance();
		$q->sql('SELECT p.STEAMID, p.NAME, p.POINTS, p.PLAYTIME, p.LASTONTIME
			FROM Player p
			WHERE p.STEAMID IN ("' . implode('", "', $where) . '")
			ORDER BY p.POINTS DESC');

		$rows = array();
		$d1 = new \DateTime('@0');
		$timezone = new \DateTimeZone('America/Chicago');
		$utc = new \DateTimeZone('UTC');
		$i = 0;
		while($row = $q->fetchRow())
		{
			$d2 = new \DateTime('@' . $row['PLAYTIME'] * 60);
			$interval = $d1->diff($d2);

			if($interval->h > 0)
			{
				$row['playspan'] = $interval->format('%h hrs %i minutes');
			}
			else
			{
				$row['playspan'] = $interval->format('%i minutes');
			}
			$row['steamid64'] = \Scrii\TF2Stats\steamIdToSteamCommunity($row['STEAMID']);
			$row['ismember'] = in_array($row['steamid64'], $steam->members) ? true : false;

			$online = new \DateTime('@' . $row['LASTONTIME']);
			$online->setTimeZone($timezone);
			$utc_online = new \DateTime('@' . $row['LASTONTIME']);
			$utc_online->setTimeZone($utc);
			$row['lastonline'] = $online->format(\DateTime::RSS);
			$row['lastonline_utc'] = $utc_online->format(\DateTime::RSS);
			$row['rank'] = ++$i;

			$rows[] = $row;
		}

		$template->assignVars(array(
			'test'			=> 'Test variable',
			'data'			=> $rows,
		));

		return;
	}
}
