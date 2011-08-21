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

// Required constants for Quartz and OpenFlame Framework
define('Codebite\\Quartz\\SITE_ROOT', dirname(__DIR__));

// weapon kill data array
$weapons = array(
	// multi
	'fryingpan'				=> array('Frying Pan', 'KW_fryingpan', 'frying_pan.png'),
	'paintrain'				=> array('Pain Train', 'KW_paintrain', 'pain_train.png'),
	'halfzatoichi'			=> array('The Half-Zatoichi', 'KW_katana', 'half-zatoichi.png'),
	'telefrag'				=> array('Telefrag', 'KW_telefrag', 'noicon.png'),
	'pistol'				=> array('Pistol', 'KW_Pistl', 'pistol.png'),
	'shotgun'				=> array('Shotgun', 'KW_Stgn', 'shotgun.png'),
	'pumpkinbomb'			=> array('Pumpkin Bomb', 'KW_pumpkin', 'pumpkin.png'),
	'lugermorph'			=> array('The Lugermorph', 'KW_maxgun', 'maxgun.png'),
	'bleed'					=> array('Bleed Kill', 'KW_bleed_kill', 'bleed.png'),

	// scout
	'bat'					=> array('Bat', 'KW_Bt', 'bat.png'),
	'scattergun'			=> array('Scattergun', 'KW_Sg', 'scattergun.png'),
	'sandman'				=> array('The Sandman', 'KW_sandman', 'sandman.png'),
	'bonktaunt'				=> array('BONK! Tauntkill', 'KW_taunt_scout', 'home_run.png'),
	'sandmanball'			=> array('Sandman Ball', 'KW_ball', 'sandman_ball.png'),
	'forceanature'			=> array('The Force-A-Nature', 'KW_force_a_nature', 'force_a_nature.png'),
	'shortstop'				=> array('The Shortstop', 'KW_short_stop', 'shortstop.png'),
	'holymackerel'			=> array('The Holy Mackerel', 'KW_holy_mackerel', 'holy_mackerel.png'),
	'candycane'				=> array('The Candy Cane', 'KW_candy_cane', 'candy_cane.png'),
	'bostonbasher'			=> array('The Boston Basher', 'KW_boston_basher', 'boston_basher.png'),
	'sunonastick'			=> array('Sun-on-a-Stick', 'KW_sun_bat', 'sun-on-a-stick.png'),
	'fanowar'				=> array('Fan O\'War', 'KW_warfan', 'fan_owar.png'),
	'threeruneblade'		=> array('Three-Rune Blade', 'KW_witcher_sword', 'three-rune_blade.png'),
	'sodapopper'			=> array('The Soda Popper', 'KW_soda_popper', 'soda_popper.png'),
	'winger'				=> array('The Winger', 'KW_the_winger', 'winger.png'),
	'atomizer'				=> array('The Atomizer', 'KW_atomizer', 'atomizer.png'),

	// soldier
	'rocketlauncher'		=> array('Rocket Launcher', 'KW_Rkt', 'rocketlauncher.png'),
	'shovel'				=> array('Shovel', 'KW_Shvl', 'shovel.png'),
	'equalizer'				=> array('The Equalizer', 'KW_unique_pickaxe', 'equalizer.png'),
	'directhit'				=> array('The Direct Hit', 'KW_rocketlauncher_directhit', 'direct_hit.png'),
	'grenadetaunt'			=> array('Soldier Grenade Taunt', 'KW_taunt_soldier', 'kamikaze.png'),
	'wormstaunt'			=> array('Worms Grenade Taunt', 'KW_worms_grenade', 'hhg.png'),
	'blackbox'				=> array('The Black-Box', 'KW_blackbox', 'black_box.png'),
	'libertylauncher'		=> array('The Liberty Launcher', 'KW_liberty_launcher', 'liberty_launcher.png'),
	'reserveshooter'		=> array('The Reserve Shooter', 'KW_reserve_shooter', 'reserve_shooter.png'),
	'disciplinaryaction'	=> array('The Disciplinary Action', 'KW_disciplinary_action', 'disciplinary_action.png'),
	'marketgardener'		=> array('The Market Gardener', 'KW_market_gardener', 'market_gardener.png'),
	'mantreads'				=> array('The Mantreads', 'KW_mantreads', 'mantreads.png'),
	'cowmangler5000'		=> array('The Cow Mangler 5000', 'KW_mangler', 'cow_mangler_5000.png'), // a.k.a. "The Stolen Polycount Engineer Update"
	'righteousbison'		=> array('The Righteous Bison', 'KW_bison', 'righteous_bison.png'),
	'original'				=> array('The Original', 'KW_QuakeRL', 'original.png'),

	// pyro
	'fireaxe'				=> array('Fireaxe', 'KW_Axe', 'fireaxe.png'),
	'flamethrower'			=> array('Flamethrower', 'KW_Ft', 'flamethrower.png'),
	'deflectarrow'			=> array('Deflected Arrow', 'KW_deflect_arrow', 'deflect_arrow.png'),
	'deflectflare'			=> array('Deflected Flare', 'KW_deflect_flare', 'deflect_flare.png'),
	'deflectrocket'			=> array('Deflected Rocket', 'KW_deflect_rocket', 'deflect_rocket.png'),
	'deflectgrenade'		=> array('Deflected Grenade', 'KW_deflect_promode', 'deflect_grenade.png'),
	'deflectsticky'			=> array('Deflected Stickybomb', 'KW_deflect_sticky', 'deflect_sticky.png'),
	'hadouken'				=> array('Hadouken Tauntkill', 'KW_taunt_pyro', 'hadouken.png'),
	'axtinguisher'			=> array('The Axtinguisher', 'KW_Axtinguisher', 'axtinguisher.png'),
	'flaregun'				=> array('The Flare Gun', 'KW_Flaregun', 'flare_gun.png'),
	'backburner'			=> array('The Backburner', 'KW_backburner', 'backburner.png'),
	'homewrecker'			=> array('The Homewrecker', 'KW_sledgehammer', 'homewrecker.png'),
	'powerjack'				=> array('The Powerjack', 'KW_powerjack', 'powerjack.png'),
	'degreaser'				=> array('The Degreaser', 'KW_degreaser', 'degreaser.png'),
	'backscratcher'			=> array('The Back Scratcher', 'KW_back_scratcher', 'back_scratcher.png'),
	'volcanoaxe'			=> array('Sharpened Volcano Fragment', 'KW_lava_axe', 'sharpened_volcano_fragment.png'),
	'maul'					=> array('The Maul', 'KW_maul', 'maul.png'),
	'detonator'				=> array('The Detonator', 'KW_detonator', 'detonator.png'),
	'postalpummeler'		=> array('The Postal Pummeler', 'KW_mailbox', 'postal_pummeler.png'),
	'deflectmangler'		=> array('Deflected Cow Mangler Shot', 'KW_ManglerReflect', 'noicon.png'),

	// demoman
	'bottle'				=> array('Bottle', 'KW_Bttl', 'bottle.png'),
	'grenadelauncher'		=> array('Grenade Launcher', 'KW_Gl', 'grenade_launcher.png'),
	'stickylauncher'		=> array('Stickybomb Launcher', 'KW_Sky', 'stickybomb_launcher.png'),
	'targincharge'			=> array('The Targin\' Charge', 'KW_demoshield', 'chargin_targe.png'),
	'eyelander'				=> array('The Eyelander', 'KW_sword', 'eyelander.png'),
	'swordtauntkill'		=> array('Sword Tauntkill', 'KW_taunt_demoman', 'decapitation.png'),
	'scottishresistance'	=> array('The Scottish Resistance', 'KW_sticky_resistance', 'scottish_resistance.png'),
	'scotsmanskullcutter'	=> array('The Scotsman\'s Skullcutter', 'KW_battleaxe', 'scotsmans_skullcutter.png'),
	'horsmannsheadtaker'	=> array('The Horsemann\'s Headtaker', 'KW_headtaker', 'horseless_headless_horsemanns_headtaker.png'),
	'caber'					=> array('The Ullapool Caber', 'KW_ullapool_caber', 'ullapool_caber.png'),
	'lochnload'				=> array('The Loch-n-Load', 'KW_lochnload', 'loch-n-load.png'),
	'claidheamhmor'			=> array('The Claidheamh Mor', 'KW_claidheamohmor', 'claidheamh_mor.png'),
	'caberexplode'			=> array('Ullapool Caber Explosion', 'KW_ullapool_caber_explosion', 'ullapool_caber_explode.png'),
	'persianpersuader'		=> array('The Persian Persuader', 'KW_persian_persuader', 'persian_persuader.png'),
	'splendidscreen'		=> array('The Splendid Screen', 'KW_splendid_screen', 'splendid_screen.png'),
	'nessiesnineiron'		=> array('Nessie\'s Nine Iron', 'KW_golfclub', 'nessies_nine_iron.png'),

	// heavy
	'minigun'				=> array('Minigun', 'KW_Cg', 'minigun.png'),
	'fists'					=> array('Fists', 'KW_Fsts', 'fists.png'),
	'natascha'				=> array('Natascha', 'KW_natascha', 'natascha.png'),
	'kgb'					=> array('The Killer Gloves of Boxing', 'KW_gloves', 'kgb.png'),
	'fisttaunt'				=> array('Heavy Fist Tauntkill', 'KW_taunt_heavy', 'showdown.png'),
	'gru'					=> array('Gloves of Running Urgently', 'KW_urgentgloves', 'gru.png'),
	'ironcurtain'			=> array('The Iron Curtain', 'KW_iron_curtain', 'iron_curtain.png'),
	'brassbeast'			=> array('The Brass Beast', 'KW_brassbeast', 'brass_beast.png'),
	'warriorsspirit'		=> array('The Warrior\'s Spirit', 'KW_bearclaws', 'warriors_spirit.png'),
	'fistsofsteel'			=> array('The Fists of Steel', 'KW_steelfists', 'fists_of_steel.png'),
	'tomislav'				=> array('Tomislav', 'KW_tomislav', 'tomislav.png'),
	'familybusiness'		=> array('The Family Business', 'KW_family_business', 'family_business.png'),
	'evictionnotice'		=> array('The Eviction Notice', 'KW_eviction_notice', 'eviction_notice.png'),

	// engineer
	'wrench'				=> array('Wrench', 'KW_Wrnc', 'wrench.png'),
	//'sentry'				=> array('Sentry', 'KW_Sntry', 'sentry1.png'), // redundant
	'l1sentry'				=> array('Level 1 Sentry', 'KW_SntryL1', 'sentry1.png'),
	'l2sentry'				=> array('Level 2 Sentry', 'KW_SntryL2', 'sentry2.png'),
	'l3sentry'				=> array('Level 3 Sentry', 'KW_SntryL3', 'sentry3.png'),
	'frontierjustice'		=> array('The Frontier Justice', 'KW_frontier_justice', 'frontier_justice.png'),
	'wrangler'				=> array('The Wrangler', 'KW_wrangler_kill', 'wrangler.png'),
	'gunslinger'			=> array('The Gunslinger', 'KW_robot_arm', 'gunslinger.png'),
	'southernhospitality'	=> array('The Southern Hospitality', 'KW_southern_hospitality', 'southern_hospitality.png'),
	'gunslingertaunt'		=> array('Gunslinger Tauntkill', 'KW_robot_arm_blender_kill', 'gunslinger_triple_punch.png'),
	'frontierjusticetaunt'	=> array('Frontier Justice Tauntkill', 'KW_taunt_guitar_kill', 'dischord.png'),
	'jag'					=> array('The Jag', 'KW_wrench_jag', 'jag.png'),
	'minisentry'			=> array('Combat Mini-Sentry Gun', 'KW_minisentry', 'minisentry.png'),
	'widowmaker'			=> array('Widowmaker', 'KW_Widowmaker', 'widowmaker.png'),
	'shortcircuit'			=> array('Short Circuit', 'KW_Short_Circuit', 'short_circuit.png'),

	// medic
	'bonesaw'				=> array('Bonesaw', 'KW_Bnsw', 'bonesaw.png'),
	'syringegun'			=> array('Syringe Gun', 'KW_Ndl', 'syringegun.png'),
	'blutsauger'			=> array('The Blutsauger', 'KW_blutsauger', 'blutsauger.png'),
	'ubersaw'				=> array('The Ubersaw', 'KW_Ubersaw', 'ubersaw.png'),
	'vitasaw'				=> array('The Vita-Saw', 'KW_battleneedle', 'vita-saw.png'),
	'amputator'				=> array('The Amputator', 'KW_amputator', 'amputator.png'),
	'crusaderscrossbow'		=> array('Crusader\'s Crossbow', 'KW_healingcrossbow', 'crusaders_crossbow.png'),
	'overdose'				=> array('The Overdose', 'KW_proto_syringe', 'overdose.png'),
	'solemnvow'				=> array('The Solemn Vow', 'KW_solemn_vow', 'solemn_vow.png'),

	// sniper
	'kukri'					=> array('Kukri', 'KW_Mctte', 'kukri.png'),
	'smg'					=> array('Sub-machine Gun', 'KW_Smg', 'smg.png'),
	'sniperrifle'			=> array('Sniper Rifle', 'KW_Spr', 'sniperrifle.png'),
	'huntsman'				=> array('The Huntsman', 'KW_tf_projectile_arrow', 'huntsmanhs.png'),
	'huntsmantaunt'			=> array('Skewer Tauntkill', 'KW_taunt_sniper', 'skewer.png'),
	'flamingarrow'			=> array('Flaming Huntsman Arrow', 'KW_compound_bow', 'flaming_huntsman.png'),
	'tribalmansshiv'		=> array('The Tribalman\'s Shiv', 'KW_tribalkukri', 'tribalmans_shiv.png'),
	'bushwacka'				=> array('The Bushwacka', 'KW_bushwacka', 'bushwacka.png'),
	'sydneysleeper'			=> array('The Sydney Sleeper', 'KW_sleeperrifle', 'sydney_sleeper.png'),
	'bazaarbargain'			=> array('The Bazaar Bargain', 'KW_bazaar_bargain', 'bazaar_bargain.png'),
	'shahanshah'			=> array('The Shahanshah', 'KW_shahanshah', 'shahanshah.png'),
	'machina'				=> array('The Machina', 'KW_Machina', 'machina.png'),
	'machinapenetation'		=> array('Machina (Penetration Kill)', 'KW_Machina_DoubleKill', 'machina_penetrate.png'),

	// spy
	'knife'					=> array('Knife', 'KW_Kn', 'backstab.png'),
	'revolver'				=> array('Revolver', 'KW_Mgn', 'revolver.png'),
	'ambassador'			=> array('The Ambassador', 'KW_ambassador', 'ambassador.png'),
	'bigkill'				=> array('The Big Kill', 'KW_samrevolver', 'samgun.png'),
	'eternalreward'			=> array('Your Eternal Reward', 'KW_eternal_reward', 'your_eternal_reward.png'),
	'letranger'				=> array('L\'Etranger', 'KW_letranger', 'letranger.png'),
	'kunai'					=> array('Conniver\'s Kunai', 'KW_kunai', 'connivers_kunai.png'),
	'enforcer'				=> array('The Enforcer', 'KW_enforcer', 'enforcer.png'),
	'bigearner'				=> array('The Big Earner', 'KW_big_earner', 'big_earner.png'),
	'diamondback'			=> array('The Diamondback', 'KW_Diamondback', 'diamondback.png'),
);

$json = json_encode($weapons);
file_put_contents(\Codebite\Quartz\SITE_ROOT . '/data/config/weapondata.json', $json);

echo 'JSON file creation/update successful';
