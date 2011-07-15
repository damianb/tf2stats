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

class Player extends \Scrii\TF2Stats\Page\Base
{
	protected $template_name = 'player.twig.html';

	protected $is_member = false;

	public function executePage()
	{
		$injector = Injector::getInstance();
		$template = $injector->get('template');
		$input = $injector->get('input');
		$steam = $injector->get('steamgroup');
		// get steam ID
		if(\Scrii\TF2Stats\REWRITING_ENABLED)
		{
			$steam_cid = $this->route->getRequestDataPoint('steam');
		}
		else
		{
			$steam_cid = $input->getInput('GET::steam', '')
				->disableFieldJuggling()
				->getClean();
		}

		$steam->getGroupMembers();
		$this->is_member = in_array($steam_cid, $steam->members) ? true : false;
		$steam_id = \Scrii\TF2Stats\steamCommunityToSteamId($steam_cid);

		if($steam_id === false)
		{
			if(\Scrii\TF2Stats\REWRITING_ENABLED)
			{
				throw new \Codebite\Quartz\Exception\ServerErrorException('', 404);
			}
			else
			{
				$router = $injector->get('simplerouter');
				$error = $router->getPage('error');
				Core::setObject('page', $error);
				$error->setErrorCode(404);
				$error->executePage();
				return;
			}
		}

		$q = QueryBuilder::newInstance();
		$q->select('p.*')
			->from('Player p')
			->where('p.STEAMID = ?', $steam_id)
			->limit(1);

		$row = $q->fetchRow();

		// Any data?
		if($row === false)
		{
			// @todo refactor for rewriting
			$router = $injector->get('simplerouter');
			$error = $router->getPage('error');
			Core::setObject('page', $error);
			$error->setErrorCode(404);
			$error->executePage();
			return;
		}

		// Prep vars
		$data = array();
		$d1 = new \DateTime('@0');
		$timezone = new \DateTimeZone(Core::getConfig('site.timezone') ?: 'America/New_York');
		$utc = new \DateTimeZone('UTC');

		// Build "action" data.
		$data['actions'] = array(
			// general actions
			array('name' => 'Dominations', 'value' => $row['Domination']),
			array('name' => 'Revenge', 'value' => $row['Revenge'],),
			array('name' => 'Headshot Kill', 'value' => $row['HeadshotKill']),
			array('name' => 'Kill Assists', 'value' => $row['KillAssist']),
			// medic actions
			array('name' => 'Kill Assists - Medic', 'value' => $row['KillAssistMedic']),
			array('name' => 'Ubercharge', 'value' => $row['Overcharge']),
			array('name' => 'Medic Healing', 'value' => $row['MedicHealing']),
			// engineer actions
			array('name' => 'Built Object - Sentrygun', 'value' => $row['BuildSentrygun']),
			array('name' => 'Built Object - Dispenser', 'value' => $row['BuildDispenser']),
			array('name' => 'Built Object - Teleporter', 'value' => $row['BOTeleporterentrace']),
			array('name' => 'Sappers Removed', 'value' => $row['KOSapper']),
			// spy actions
			array('name' => 'Backstabs', 'value' => $row['K_backstab']),
			array('name' => 'Sappers Placed', 'value' => $row['BOSapper']),
			array('name' => 'Sentries Destroyed', 'value' => $row['KOSentrygun']),
			array('name' => 'Dispensers Destroyed', 'value' => $row['KODispenser']),
			array('name' => 'Teleporters Destroyed', 'value' => $row['KOTeleporterEntrace']),
			array('name' => 'Feigned Deaths', 'value' => $row['player_feigndeath']),
			// capture related
			array('name' => 'Points Captured', 'value' => $row['CPCaptured']),
			// array('name' => 'Captures Blocked', 'value' => $row['CPBlocked']), // data doesn't seem to track correctly plugin-side
			array('name' => 'Intel Captures', 'value' => $row['FileCaptured']),
			// misc
			array('name' => 'Sandviches Stolen', 'value' => $row['player_stealsandvich']),
			array('name' => 'People Extinguished', 'value' => $row['player_extinguished']),
			array('name' => 'Times Teleported', 'value' => $row['player_teleported']),
		);

		// Rank class performance...
		$tf2classes = array('Scout' => 'Scout	', 'Soldier' => 'Soldier', 'Pyro' => 'Pyro', 'Demo' => 'Demoman', 'Heavy' => 'Heavy', 'Engi' => 'Engineer', 'Medic' => 'Medic', 'Sniper' => 'Sniper', 'Spy' => 'Spy');
		foreach($tf2classes as $classname => $class)
		{
			$kd[$classname] = ($row[$classname . 'Deaths'] > 0) ? round($row[$classname . 'Kills'] / $row[$classname . 'Deaths'], 2) : $row[$classname . 'Kills'];
		}
		arsort($kd, SORT_NUMERIC);
		foreach($kd as $classname => $kdr)
		{
			$data['classperformance'][$classname] = array(
				'class'		=> $tf2classes[$classname],
				'kills'		=> $row[$classname . 'Kills'],
				'deaths'	=> $row[$classname . 'Deaths'],
				'kd'		=> $kdr,
			);
		}

		// Trick the steam group data fetcher here...
		$steam->members['temp'] = $steam_cid;
		$data['profile'] = $steam->getMemberInfo($steam_cid, false, 60);

		// Get weapon kill data
		$weapons = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json');

		$used_weapons = array();
		foreach($weapons as $key => $weapon)
		{
			if($row[$weapon[1]] <= 0)
			{
				continue;
			}
			$used_weapons[$key] = $row[$weapon[1]];
		}
		arsort($used_weapons, SORT_NUMERIC);

		foreach($used_weapons as $key => $kills)
		{
			$data['weaponkills'][] = array(
				'name'		=> $weapons[$key][0],
				'kills'		=> $kills,
				'image'		=> $weapons[$key][2],
			);
		}

		// Obtain rank on server...
		$q = QueryBuilder::newInstance();
		$q->select('COUNT(p.STEAMID) as position')
			->from('Player p')
			->where('p.POINTS > ?', $row['POINTS']);
		$res = $q->fetchRow();
		$data['rank'] = $res['position'] + 1;

		// Some basic stats
		$data['points'] = $row['POINTS'];
		$data['kills'] = $row['KILLS'];
		$data['deaths'] = $row['Death'];
		$data['kdr'] = ($row['Death'] > 0) ? round($row['KILLS'] / $row['Death'], 2) : $row['KILLS'];
		$data['kpm'] = ($row['PLAYTIME'] > 0) ? round($row['KILLS'] / $row['PLAYTIME'], 2) : $row['KILLS'];

		// Figure out total time played.
		$d2 = new \DateTime('@' . $row['PLAYTIME'] * 60);
		$interval = $d1->diff($d2);
		if($interval->h > 0)
		{
			$data['playspan'] = $interval->format('%h hrs %i minutes');
		}
		else
		{
			$data['playspan'] = $interval->format('%i minutes');
		}

		// Figure out last-online time.
		$online = new \DateTime('@' . $row['LASTONTIME']);
		$online->setTimeZone($timezone);
		$utc_online = new \DateTime('@' . $row['LASTONTIME']);
		$utc_online->setTimeZone($utc);
		$data['lastonline'] = $online->format(\DateTime::RSS);
		$data['lastonline_utc'] = $utc_online->format(\DateTime::RSS);

		// Some more vars
		$data['backpackurl'] = rtrim(str_replace('http://steamcommunity.com/', 'http://tf2items.com/', $data['profile']['profileurl']), '/');
		$data['friendlink'] = 'steam://friends/add/' . $steam_cid;
		$data['is_banned'] = (isset($row['BANREASON']) && $row['BANREASON'] != '') ? true : false;
		$data['banreason'] = (isset($row['BANREASON'])) ? $row['BANREASON'] : '';

		// in case steam community mucks up
		if($data['profile']['personaname'])
		{
			$data['playername_trim'] = (strlen($data['profile']['personaname']) > 23) ? substr($data['profile']['personaname'], 0, 19) . ' [...]' : $data['profile']['personaname'];
			$data['playername_full'] = $data['profile']['personaname'];
		}
		else
		{
			$data['playername_trim'] = (strlen($row['NAME']) > 23) ? substr($row['NAME'], 0, 19) . ' [...]' : $row['NAME'];
			$data['playername_full'] = $row['NAME'];
		}


		// Dump vars to template now
		$template->assignVars(array(
			'playername'		=> $row['NAME'],
			'player_id'			=> $steam_id,
			'player_cid'		=> $steam_cid,
			'player_url'		=> 'http://steamcommunity.com/profiles/' . $steam_cid . '/',
			'playerdata'		=> $data,
			'group_member'		=> $this->is_member,
		));

		return;
	}
}
