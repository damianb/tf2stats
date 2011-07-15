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
		$d1 = new \DateTime('@0');
		$timezone = new \DateTimeZone(Core::getConfig('site.timezone') ?: 'America/New_York');
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
			$row['is_banned'] = (isset($row['BANREASON']) && $row['BANREASON'] != '') ? true : false;

			$rows[] = $row;
		}

		$template->assignVars(array(
			'data'			=> $rows,
		));

		return;
	}
}
