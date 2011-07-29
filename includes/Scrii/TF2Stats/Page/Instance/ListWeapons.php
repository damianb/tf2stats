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
use \OpenFlame\Framework\Utility\JSON;
use \OpenFlame\Dbal\Query;
use \OpenFlame\Dbal\QueryBuilder;

class ListWeapons extends \Scrii\TF2Stats\Page\Base
{
	protected $template_name = 'listweapons.twig.html';

	public function executePage()
	{
		$injector = Injector::getInstance();
		$template = $injector->get('template');
		$steam = $injector->get('steamgroup');
		$input = $injector->get('input');
		$steam->getGroupMembers();

		// Get weapon data
		$weapons = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json');

		// build the "select" portion of the query...
		$select = array();
		foreach($weapons as $weapon_name => $weapon)
		{
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

		$template->assignVars(array(
			'data'			=> $data,
		));

		return;
	}
}
