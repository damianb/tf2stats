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

namespace Scrii\TF2Stats\Controller;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Utility\JSON;
use \Codebite\Quartz\Site as Quartz;
use \Codebite\Quartz\Dbal\Query;
use \Codebite\Quartz\Dbal\QueryBuilder;
use \Scrii\Steam\SteamID;

class PlayerWeaponRanking extends \Codebite\Quartz\Controller\Base
{
	const LIMIT_PAGE = 50;

	protected $template_name = 'playerweaponranking.twig.html';

	public function executePage()
	{
		$quartz = Quartz::getInstance();

		$dbg_instance = NULL;
		$quartz->debugtime->newEntry('steam->getgroupmembers', '', $dbg_instance);
		$quartz->steamgroup->getGroupMembers();
		$quartz->debugtime->newEntry('steam->getgroupmembers', 'Fetched steam group members (20 minute cache)', $dbg_instance);

		if(\Scrii\TF2Stats\REWRITING_ENABLED)
		{
			$page = $this->route->get('page');
			$weapon = $this->route->get('weapon');
			if($page <= 0)
			{
				$page = 1;
			}
		}
		else
		{
			$page = $quartz->input->getInput('GET::p', 1)
				->getClean();
			$weapon = $quartz->input->getInput('GET::weapon', '')
				->getClean();
		}

		// Get weapon data
		$weapons = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json');

		if($weapon == '' || !isset($weapons[$weapon]) || !preg_match('#^[\w_]+$#', $weapons[$weapon][1]))
		{
			if(\Scrii\TF2Stats\REWRITING_ENABLED)
			{
				throw new \Codebite\Quartz\Internal\ServerErrorException('', 404);
			}
			else
			{
				$error = $quartz->simplerouter->getPage('error');
				Core::setObject('controller.instance', $error);
				$error->setErrorCode(404);
				$error->executePage();
				return;
			}
		}

		$weapon_column = $weapons[$weapon][1];

		// Calculate the intended offset (pages are 50 per)
		$offset = ($page > 1) ? ($page - 1) * self::LIMIT_PAGE : 0;

		// Get the collective number of kills for this weapon
		$tq = QueryBuilder::newInstance();
		$tq->select(sprintf('SUM(%s) as weapon_kills', $weapon_column))
			->from('Player');
		$_tq = $tq->fetchRow();
		$weapon_kill_total = $_tq['weapon_kills'];

		$q = QueryBuilder::newInstance();
		$q->select('p.STEAMID, p.NAME' . ((\Scrii\TF2Stats\ENABLE_BANREASON) ? ', p.BANREASON' : '') . sprintf(', %s as weapon_kills', $weapon_column))
			->from('Player p')
			->where(sprintf('p.%s > 0', $weapon_column))
			->orderBy(sprintf('p.%s', $weapon_column), 'DESC')
			->offset($offset)
			->limit(self::LIMIT_PAGE);

		$rows = array();
		$i = 0;
		while($row = $q->fetchRow())
		{
			$steam_id = new SteamID($row['STEAMID']);
			$row['steamid64'] = $steam_id->getSteamID64();
			$row['ismember'] = in_array($row['steamid64'], $quartz->steamgroup->members) ? true : false;

			$row['rank'] = $offset + ++$i;
			$row['is_banned'] = (isset($row['BANREASON']) && $row['BANREASON'] != '') ? true : false;

			$rows[] = $row;
		}

		if(empty($rows))
		{
			$quartz->template->assignVar('noresults', true);
			return;
		}

		$current_weapon = array(
			'name'		=> $weapons[$weapon][0],
			'kills'		=> $weapon_kill_total,
			'image'		=> $weapons[$weapon][2],
			'urlname'	=> $weapon,
		);

		$pq = QueryBuilder::newInstance();
		$pq->select('COUNT(p.STEAMID) as total')
			->from('Player p')
			->where(sprintf('p.%s > 0', $weapon_column));
		$pagedata = $pq->fetchRow();
		$total = $pagedata['total'];
		$total_pages = ceil($total / self::LIMIT_PAGE);

		// build pagination
		$pagination = array(
			'first'		=> 1,
			'current'	=> $page,
			'pages'		=> array(),
			'last'		=> $total_pages,
			'record'	=> array(
				'from'		=> (($page > 1) ? ($page - 1) * self::LIMIT_PAGE : 0) + 1,
				'total'		=> $total,
				'to'		=> ($page * self::LIMIT_PAGE <= $total) ? $page * self::LIMIT_PAGE : $total,
			),
		);

		// Run through and generate a number of page links...
		for($i = -3; $i <= 3; $i++)
		{
			// "before" first page?
			if($page + $i < 1)
			{
				continue;
			}
			elseif($page + $i > $total_pages)
			{
				continue;
			}

			$pagination['pages'][] = $page + $i;
		}

		$quartz->template->assignVars(array(
			'data'			=> $rows,
			'pagination'	=> $pagination,
			'weapon'		=> $current_weapon,
		));

		return;
	}
}
