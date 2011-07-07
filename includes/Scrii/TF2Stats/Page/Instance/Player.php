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
		$steam_cid = $input->getInput('GET::steam', '')
			->disableFieldJuggling()
			->getClean();

		$steam->getGroupMembers();
		$this->is_member = in_array($steam_cid, $steam->members) ? true : false;
		$steam_id = \Scrii\TF2Stats\steamCommunityToSteamId($steam_cid);

		if($steam_id === false)
		{
			$router = $injector->get('simplerouter');
			$error = $router->getPage('error');
			Core::setObject('page', $error);
			$error->setErrorCode(404);
			$error->executePage();
			return;
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
		$timezone = new \DateTimeZone('America/Chicago');
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
			array('name' => 'Built Object - Tele Entrance/Exit', 'value' => $row['BOTeleporterentrace']),
			//array('name' => 'Built Object - Tele Exit', 'value' => $row['BOTeleporterExit']),
			array('name' => 'Sappers Removed', 'value' => $row['KOSapper']),
			// spy actions
			array('name' => 'Backstabs', 'value' => $row['K_backstab']),
			array('name' => 'Sappers Placed', 'value' => $row['BOSapper']),
			array('name' => 'Sentries Destroyed', 'value' => $row['KOSentrygun']),
			array('name' => 'Dispensers Destroyed', 'value' => $row['KODispenser']),
			array('name' => 'Tele Entrances Destroyed', 'value' => $row['KOTeleporterEntrace']),
			array('name' => 'Tele Exits Destroyed', 'value' => $row['KOTeleporterExit']),
			array('name' => 'Feigned Deaths', 'value' => $row['player_feigndeath']),
			// capture related
			array('name' => 'Points Captured', 'value' => $row['CPCaptured']),
			array('name' => 'Captures Blocked', 'value' => $row['CPBlocked']),
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
		$weapons = array(
			// multi
			array('Frying Pan', 'KW_fryingpan', 'frying_pan.png'),
			array('Pain Train', 'KW_paintrain', 'pain_train.png'),
			array('Telefrag', 'KW_telefrag', 'telefrag.png'),
			array('Pistol', 'KW_Pistl', 'pistol.png'),
			array('Shotgun', 'KW_Stgn', 'shotgun.png'),
			array('Pumpkin Bomb', 'KW_pumpkin', 'pumpkin.png'),
			array('The Lugermorph', 'KW_maxgun', 'maxgun.png'),
			array('Bleed Kill', 'KW_bleed_kill', 'bleed.png'),

			// scout
			array('Bat', 'KW_Bt', 'bat.png'),
			array('Scattergun', 'KW_Sg', 'scattergun.png'),
			array('The Sandman', 'KW_sandman', 'sandman.png'),
			array('BONK! Tauntkill', 'KW_taunt_scout', 'home_run.png'),
			array('Sandman Ball', 'KW_ball', 'sandman_ball.png'),
			array('The Force-A-Nature', 'KW_force_a_nature', 'force_a_nature.png'),
			array('The Shortstop', 'KW_short_stop', 'shortstop.png'),
			array('The Holy Mackerel', 'KW_holy_mackerel', 'holy_mackerel.png'),
			array('The Candy Cane', 'KW_candy_cane', 'candy_cane.png'),
			array('The Boston Basher', 'KW_boston_basher', 'boston_basher.png'),
			array('Sun-on-a-Stick', 'KW_sun_bat', 'sun-on-a-stick.png'),
			array('Fan O\'War', 'KW_warfan', 'fan_owar.png'),
			array('Three-Rune Blade', 'KW_witcher_sword', 'three-rune_blade.png'),
			array('The Soda Popper', 'KW_soda_popper', 'soda_popper.png'),
			array('The Winger', 'KW_the_winger', 'winger.png'),
			array('The Atomizer', 'KW_atomizer', 'atomizer.png'),

			// soldier
			array('Rocket Launcher', 'KW_Rkt', 'rocketlauncher.png'),
			array('Shovel', 'KW_Shvl', 'shovel.png'),
			array('The Equalizer', 'KW_unique_pickaxe', 'equalizer.png'),
			array('The Direct Hit', 'KW_rocketlauncher_directhit', 'direct_hit.png'),
			array('Soldier Grenade Taunt', 'KW_taunt_soldier', 'kamikaze.png'),
			array('Worms Grenade Taunt', 'KW_worms_grenade', 'hhg.png'),
			array('The Black-Box', 'KW_blackbox', 'black_box.png'),
			array('The Half-Zatoichi', 'KW_katana', 'half-zatoichi.png'),
			array('The Liberty Launcher', 'KW_liberty_launcher', 'liberty_launcher.png'),
			array('The Reserve Shooter', 'KW_reserve_shooter', 'reserve_shooter.png'),
			array('The Disciplinary Action', 'KW_disciplinary_action', 'disciplinary_action.png'),
			array('The Market Gardener', 'KW_market_gardener', 'market_gardener.png'),
			array('The Mantreads', 'KW_mantreads', 'mantreads.png'),

			// pyro
			array('Fireaxe', 'KW_Axe', 'fireaxe.png'),
			array('Flamethrower', 'KW_Ft', 'flamethrower.png'),
			array('Hadouken Tauntkill', 'KW_taunt_pyro', 'hadouken.png'),
			array('The Axtinguisher', 'KW_Axtinguisher', 'axtinguisher.png'),
			array('The Flare Gun', 'KW_Flaregun', 'flare_gun.png'),
			array('The Backburner', 'KW_backburner', 'backburner.png'),
			array('The Homewrecker', 'KW_sledgehammer', 'homewrecker.png'),
			array('The Powerjack', 'KW_powerjack', 'powerjack.png'),
			array('The Degreaser', 'KW_degreaser', 'degreaser.png'),
			array('The Back Scratcher', 'KW_back_scratcher', 'back_scratcher.png'),
			array('Sharpened Volcano Fragment', 'KW_lava_axe', 'sharpened_volcano_fragment.png'),
			array('The Maul', 'KW_maul', 'maul.png'),
			array('The Detonator', 'KW_detonator', 'detonator.png'),
			array('The Postal Pummeler', 'KW_mailbox', 'postal_pummeler.png'),

			// demoman
			array('Bottle', 'KW_Bttl', 'bottle.png'),
			array('Grenade Launcher', 'KW_Gl', 'grenade_launcher.png'),
			array('Stickybomb Launcher', 'KW_Sky', 'stickybomb_launcher.png'),
			array('The Targin\' Charge', 'KW_demoshield', 'chargin_targe.png'),
			array('The Eyelander', 'KW_sword', 'eyelander.png'),
			array('Sword Tauntkill', 'KW_taunt_demoman', 'decapitation.png'),
			array('The Scottish Resistance', 'KW_sticky_resistance', 'scottish_resistance.png'),
			array('The Scotsman\'s Skullcutter', 'KW_battleaxe', 'scotsmans_skullcutter.png'),
			array('The Horseless Headless Horsemann\'s Headtaker', 'KW_headtaker', 'horseless_headless_horsemanns_headtaker.png'),
			array('The Ullapool Caber', 'KW_ullapool_caber', 'ullapool_caber.png'),
			array('The Loch-n-Load', 'KW_lochnload', 'loch-n-load.png'),
			array('The Claidheamh Mor', 'KW_claidheamohmor', 'claidheamh_mor.png'),
			array('Ullapool Caber Explosion', 'KW_ullapool_caber_explosion', 'ullapool_caber_explode.png'),
			array('The Persian Persuader', 'KW_persian_persuader', 'persian_persuader.png'),
			array('The Splendid Screen', 'KW_splendid_screen', 'splendid_screen.png'),
			array('Nessie\'s Nine Iron', 'KW_golfclub', 'nessies_nine_iron.png'),

			// heavy
			array('Minigun', 'KW_Cg', 'minigun.png'),
			array('Fists', 'KW_Fsts', 'fists.png'),
			array('Natascha', 'KW_natascha', 'natascha.png'),
			array('The Killer Gloves of Boxing', 'KW_gloves', 'kgb.png'),
			array('Heavy Fist Tauntkill', 'KW_taunt_heavy', 'showdown.png'),
			array('The Gloves of Running Urgently', 'KW_urgentgloves', 'gru.png'),
			array('The Iron Curtain', 'KW_iron_curtain', 'iron_curtain.png'),
			array('The Brass Beast', 'KW_brassbeast', 'brass_beast.png'),
			array('The Warrior\'s Spirit', 'KW_bearclaws', 'warriors_spirit.png'),
			array('The Fists of Steel', 'KW_steelfists', 'fists_of_steel.png'),
			array('Tomislav', 'KW_tomislav', 'tomislav.png'),
			array('The Family Business', 'KW_family_business', 'family_business.png'),
			array('The Eviction Notice', 'KW_eviction_notice', 'eviction_notice.png'),

			// engineer
			array('Wrench', 'KW_Wrnc', 'wrench.png'),
			//array('Sentry', 'KW_Sntry', 'sentry1.png'), // redundant
			array('Level 1 Sentry', 'KW_SntryL1', 'sentry1.png'),
			array('Level 2 Sentry', 'KW_SntryL2', 'sentry2.png'),
			array('Level 3 Sentry', 'KW_SntryL3', 'sentry3.png'),
			array('The Frontier Justice', 'KW_frontier_justice', 'frontier_justice.png'),
			array('The Wrangler', 'KW_wrangler_kill', 'wrangler.png'),
			array('The Gunslinger', 'KW_robot_arm', 'gunslinger.png'),
			array('The Southern Hospitality', 'KW_southern_hospitality', 'southern_hospitality.png'),
			array('Gunslinger Tauntkill', 'KW_robot_arm_blender_kill', 'gunslinger_triple_punch.png'),
			array('Frontier Justice Tauntkill', 'KW_taunt_guitar_kill', 'dischord.png'),
			array('The Jag', 'KW_wrench_jag', 'jag.png'),
			array('Combat Mini-Sentry Gun', 'KW_minisentry', 'minisentry.png'),

			// medic
			array('Bonesaw', 'KW_Bnsw', 'bonesaw.png'),
			array('Syringe Gun', 'KW_Ndl', 'syringegun.png'),
			array('The Blutsauger', 'KW_blutsauger', 'blutsauger.png'),
			array('The Ubersaw', 'KW_Ubersaw', 'ubersaw.png'),
			array('The Vita-Saw', 'KW_battleneedle', 'vita-saw.png'),
			array('The Amputator', 'KW_amputator', 'amputator.png'),
			array('Crusader\'s Crossbow', 'KW_healingcrossbow', 'crusaders_crossbow.png'),
			array('The Overdose', 'KW_proto_syringe', 'overdose.png'),
			array('The Solemn Vow', 'KW_solemn_vow', 'solemn_vow.png'),

			// sniper
			array('Kukri', 'KW_Mctte', 'kukri.png'),
			array('Sub-machine Gun', 'KW_Smg', 'smg.png'),
			array('Sniper Rifle', 'KW_Spr', 'sniperrifle.png'),
			array('The Huntsman', 'KW_tf_projectile_arrow', 'huntsmanhs.png'),
			array('Flaming Huntsman Arrow', 'KW_compound_bow', 'flaming_huntsman.png'),
			array('The Tribalman\'s Shiv', 'KW_tribalkukri', 'tribalmans_shiv.png'),
			array('The Bushwacka', 'KW_bushwacka', 'bushwacka.png'),
			array('The Sydney Sleeper', 'KW_sleeperrifle', 'sydney_sleeper.png'),
			array('The Bazaar Bargain', 'KW_bazaar_bargain', 'bazaar_bargain.png'),
			array('The Shahanshah', 'KW_shahanshah', 'shahanshah.png'),

			// spy
			array('Knife', 'KW_Kn', 'backstab.png'),
			array('Revolver', 'KW_Mgn', 'revolver.png'),
			array('The Ambassador', 'KW_ambassador', 'ambassador.png'),
			array('The Big Kill', 'KW_samrevolver', 'samgun.png'),
			array('Your Eternal Reward', 'KW_eternal_reward', 'your_eternal_reward.png'),
			array('L\'Etranger', 'KW_letranger', 'letranger.png'),
			array('Conniver\'s Kunai', 'KW_kunai', 'connivers_kunai.png'),
			array('The Enforcer', 'KW_enforcer', 'enforcer.png'),
			array('The Big Earner', 'KW_big_earner', 'big_earner.png'),
		);

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
		$data['playername_trim'] = (strlen($data['profile']['personaname']) > 23) ? substr($data['profile']['personaname'], 0, 19) . ' [...]' : $data['profile']['personaname'];
		$data['playername_full'] = $data['profile']['personaname'];

		// Dump vars to template now
		$template->assignVars(array(
			'test'				=> 'Test variable',
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
