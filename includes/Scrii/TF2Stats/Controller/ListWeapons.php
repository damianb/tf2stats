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

class ListWeapons extends \Codebite\Quartz\Controller\Base
{
	protected $template_name = 'listweapons.twig.html';

	public function executePage()
	{
		$quartz = Quartz::getInstance();

		$dbg_instance = NULL;
		$quartz->debugtime->newEntry('steam->getgroupmembers', '', $dbg_instance);
		$quartz->steamgroup->getGroupMembers();
		$quartz->debugtime->newEntry('steam->getgroupmembers', 'Fetched steam group members (20 minute cache)', $dbg_instance);

		// Get weapon data
		$weapons = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json');

		// build the "select" portion of the query...
		$select = array();
		foreach($weapons as $weapon_name => $weapon)
		{
			// This is a trick to use just one preg_match for verifying that our data is safe for use within the query.  Just being paranoid.
			if(!preg_match('#^[\w_]+::[\w_]+$#', $weapon[1] . '::' . $weapon_name))
			{
				continue;
			}
			$select[] = sprintf('SUM(%1$s) as %2$s', $weapon[1], $weapon_name);
		}
		$select = implode(', ', $select);

		$q = QueryBuilder::newInstance();
		$q->select($select)
			->from('Player p')
			->limit(1);

		$row = $q->fetchRow();
		arsort($row, SORT_NUMERIC);

		// Hammer out the $data array...
		$data = array();
		foreach($row as $weapon_name => $weapon_kills)
		{
			$data[$weapon_name] = array(
				'name'		=> $weapons[$weapon_name][0],
				'image'		=> $weapons[$weapon_name][2],
				'urlname'	=> $weapon_name,
				'kills'		=> $weapon_kills,
			);
		}

		if(empty($data))
		{
			$quartz->template->assignVar('noresults', true);
			return;
		}

		$quartz->template->assignVars(array(
			'data'			=> $data,
		));

		return;
	}
}
