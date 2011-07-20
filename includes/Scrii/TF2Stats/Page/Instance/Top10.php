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
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;

class Top10 extends \Scrii\TF2Stats\Page\Base
{
	protected $template_name = 'top10.twig.html';

	public function executePage()
	{
		$injector = Injector::getInstance();
		$template = $injector->get('template');
		$steam = $injector->get('steamgroup');
		$steam->getGroupMembers();

		$q = QueryBuilder::newInstance();
		$q->select('p.STEAMID, p.NAME, p.POINTS, p.PLAYTIME, p.LASTONTIME' . ((\Scrii\TF2Stats\ENABLE_BANREASON) ? ', p.BANREASON' : ''))
			->from('Player p')
			->orderBy('p.points', 'DESC')
			->limit(10);

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

			$row['steamid64'] = \Scrii\TF2Stats\steamIdToSteamCommunity($row['STEAMID']);
			$row['ismember'] = in_array($row['steamid64'], $steam->members) ? true : false;

			$online = new \DateTime('@' . $row['LASTONTIME']);
			$online->setTimeZone($timezone);
			$utc_online = new \DateTime('@' . $row['LASTONTIME']);
			$utc_online->setTimeZone($utc);
			$row['lastonline'] = $online->format(\DateTime::RSS);
			$row['lastonline_utc'] = $utc_online->format(\DateTime::RSS);
			$row['rank'] = ++$i;
			$row['is_banned'] = (isset($row['BANREASON']) && $row['BANREASON'] != '') ? true : false;

			$rows[] = $row;
		}

		$template->assignVars(array(
			'data'			=> $rows,
		));

		return;
	}
}
