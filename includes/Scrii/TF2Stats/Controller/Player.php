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
use \Codebite\Quartz\Site as Quartz;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Utility\JSON;
use \Codebite\Quartz\Dbal\Query;
use \Codebite\Quartz\Dbal\QueryBuilder;
use \Scrii\Steam\SteamID;

class Player extends \Codebite\Quartz\Controller\Base
{
	protected $template_name = 'player.twig.html';

	protected $is_member = false;

	public function executePage()
	{
		$quartz = Quartz::getInstance();

		// Get steam ID
		if(\Scrii\TF2Stats\REWRITING_ENABLED)
		{
			$steam_id = $this->route->get('steam');
		}
		else
		{
			$steam_id = $quartz->input->getInput('GET::steam', '')
				->getClean();
		}

		try
		{
			$steam_id = new SteamID($steam_id);
		}
		catch(\RuntimeException $e)
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

		$dbg_instance = NULL;
		$quartz->debugtime->newEntry('steam->getgroupmembers', '', $dbg_instance);
		$quartz->steamgroup->getGroupMembers();
		$this->is_member = in_array($steam_id->getSteamID64(), $quartz->steamgroup->members) ? true : false;
		$quartz->debugtime->newEntry('steam->getgroupmembers', 'Fetched steam group members (20 minute cache)', $dbg_instance);

		$q = QueryBuilder::newInstance();
		$q->select('p.*')
			->from('Player p')
			->where('p.STEAMID = ?', $steam_id->getSteamID32())
			->limit(1);

		$row = $q->fetchRow();

		// Any data?
		if($row === false)
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

		// Prep vars
		$data = array();

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
			// halloween
			array('name' => 'Monoculus Stuns', 'value' => $row['EyeBossStuns']),
			array('name' => 'Monoculus Kills', 'value' => $row['EyeBossKills']),
			// misc
			array('name' => 'Sandviches Stolen', 'value' => $row['player_stealsandvich']),
			array('name' => 'People Extinguished', 'value' => $row['player_extinguished']),
			array('name' => 'Times Teleported', 'value' => $row['player_teleported']),
		);

		// Rank class performance...
		$tf2classes = array('Scout' => 'Scout', 'Soldier' => 'Soldier', 'Pyro' => 'Pyro', 'Demo' => 'Demoman', 'Heavy' => 'Heavy', 'Engi' => 'Engineer', 'Medic' => 'Medic', 'Sniper' => 'Sniper', 'Spy' => 'Spy');
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
		$steam->members['temp'] = $steam_id->getSteamID64();

		$dbg_instance = NULL;
		$quartz->debugtime->newEntry('steam->getplayerdata', '', $dbg_instance);
		$data['profile'] = $quartz->steamgroup->getMemberInfo($steam_id->getSteamID64(), false, 60);
		$quartz->debugtime->newEntry('steam->getplayerdata', 'Fetched player data from Steam Web API (60 minute cache)', $dbg_instance);

		// Get the weapondata json file.
		$weapons = JSON::decode(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json');

		// Figure out weapon kill stats.
		$used_weapons = array();
		foreach($weapons as $key => $weapon)
		{
			// Make sure the weapon's column exists, and that the weapon was used before continuing
			if(!isset($row[$weapon[1]]) || $row[$weapon[1]] <= 0)
			{
				continue;
			}
			$used_weapons[$key] = $row[$weapon[1]];
		}
		arsort($used_weapons, SORT_NUMERIC);

		foreach($used_weapons as $weapon_name => $kills)
		{
			$data['weaponkills'][] = array(
				'name'		=> $weapons[$weapon_name][0],
				'kills'		=> $kills,
				'image'		=> $weapons[$weapon_name][2],
				'urlname'	=> $weapon_name,
			);
		}

		// Obtain rank on server... (we're ignoring the circumstance of tied-for-rank here)
		$qr = QueryBuilder::newInstance();
		$qr->select('COUNT(p.STEAMID) as position')
			->from('Player p')
			->where('p.POINTS > ?', $row['POINTS']);
		$res = $qr->fetchRow();
		$data['rank'] = $res['position'] + 1;

		// Some basic stats (points, kills, deaths, kill-death-ratio, kills-per-minute...etc.)
		$data['points'] = $row['POINTS'];
		$data['kills'] = $row['KILLS'];
		$data['deaths'] = $row['Death'];
		$data['kadr'] = ($row['Death'] > 0) ? round(($row['KILLS'] + ($row['KillAssist'] / 2)) / $row['Death'], 2) : $row['KILLS'];
		$data['kdr'] = ($row['Death'] > 0) ? round($row['KILLS'] / $row['Death'], 2) : $row['KILLS'];
		$data['kpm'] = ($row['PLAYTIME'] > 0) ? round($row['KILLS'] / $row['PLAYTIME'], 2) : $row['KILLS'];

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
		$data['playspan'] = vsprintf($format, $playtime);

		// Figure out last-online time.
		$data['lastonline'] = \Scrii\getEventTime($row['LASTONTIME']);

		// Some more vars
		$data['backpackurl'] = rtrim(str_replace('http://steamcommunity.com/', 'http://tf2b.com/', $data['profile']['profileurl']), '/');
		$data['friendlink'] = 'steam://friends/add/' . $steam_id->getSteamID64();
		$data['is_banned'] = (isset($row['BANREASON']) && $row['BANREASON'] != '') ? true : false;
		$data['banreason'] = (isset($row['BANREASON'])) ? $row['BANREASON'] : '';

		// in case steam community mucks up
		if($data['profile']['personaname'])
		{
			$data['playername_trim'] = (strlen($data['profile']['personaname']) > 35) ? substr($data['profile']['personaname'], 0, 32) . ' [...]' : $data['profile']['personaname'];
			$data['playername_full'] = $data['profile']['personaname'];
		}
		else
		{
			$data['playername_trim'] = (strlen($row['NAME']) > 35) ? substr($row['NAME'], 0, 32) . ' [...]' : $row['NAME'];
			$data['playername_full'] = $row['NAME'];
		}


		// Dump vars to template now
		$quartz->template->assignVars(array(
			'playername'		=> $row['NAME'],
			'player_id'			=> $steam_id->getSteamID32(),
			'player_cid'		=> $steam_id->getSteamID64(),
			'player_url'		=> 'http://steamcommunity.com/profiles/' . $steam_id->getSteamID64() . '/',
			'playerdata'		=> $data,
			'group_member'		=> $this->is_member,
		));

		return;
	}
}
