<?php
/**
 *
 *===================================================================
 *
 *  scrii
 *-------------------------------------------------------------------
 * @category    scrii
 * @package     scrii
 * @author      Damian Bushong
 * @copyright   (c) 2010 - 2011 scrii.com
 * @license     GPLv3
 *
 *===================================================================
 *
 */

namespace Scrii\Steam;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Dependency\Injector;
use \OpenFlame\Framework\Utility\JSON;
use \OpenFlame\Dbal\QueryBuilder;
use \InvalidArgumentException;
use \RuntimeException;

// http://developer.valvesoftware.com/wiki/Steam_Web_API#GetPlayerSummaries_.28v0001.29
// https://partner.steamgames.com/documentation/community_data
// http://www.phpfreaks.com/forums/index.php?topic=266151.0
class Group
{
	public $members = array();

	public $member_count = 0;

	public $unavailable = true;

	public $fetch_time = 0;

	protected $steam_group_url = '';

	protected $web_api_key = '';

	public function __construct($steam_group_url, $web_api_key)
	{
		$this->steam_group_url = $steam_group_url;
		$this->web_api_key = $web_api_key;
	}

	public function getGroupMembers($force = false)
	{
		$injector = Injector::getInstance();
		$cache = $injector->get('cache');

		$steam_data = NULL;
		if(!$force)
		{
			$steam_data = $cache->loadData('steam_memberdata');
		}

		if($steam_data === NULL)
		{
			$xml = false;
			$xml_string = @file_get_contents(rtrim($this->steam_group_url, '/') . '/memberslistxml/?xml=1');

			// suppress xml parsing errors
			libxml_use_internal_errors(true);
			if($xml_string != false)
			{
				$xml = new \SimpleXMLElement($xml_string);
			}
			libxml_use_internal_errors(false);

			if($xml !== false)
			{
				/*if(\Scrii\full_compare($xml->groupID64, self::STEAM_GROUP_ID))
				{
					throw new RuntimeException('Invalid steam group data returned');
				}*/
				foreach($xml->members->steamID64 as $member)
				{
					$steam_data['members'][] = (string) $member;
				}
				$steam_data['member_count'] = $xml->memberCount;
				$steam_data['unavailable'] = false;
				$ttl = 1200; // 20 minutes
			}
			else
			{
				$steam_data['unavailable'] = true;
				$ttl = 300; // 5 minutes
			}

			$steam_data['fetch_time'] = time();

			$cache->storeData('steam_memberdata', $steam_data, $ttl);
		}

		// Is steam unavailable?
		$this->unavailable = $steam_data['unavailable'];
		$this->fetch_time = $steam_data['fetch_time'];
		if(!$this->unavailable)
		{
			$this->members = $steam_data['members'];
			$this->member_count = $steam_data['member_count'];
		}
	}

	public function getMemberInfo($steam_id, $force = false, $ttl = 2)
	{
		// check for invalid steam id
		if(!ctype_digit($steam_id))
		{
			return false;
		}

		$injector = Injector::getInstance();
		$cache = $injector->get('cache');

		$member_data = NULL;
		if(!$force)
		{
			$member_data = $cache->loadData('steam_member_' . $steam_id);
		}

		if($member_data === NULL)
		{
			// ex profile: 76561198012908563
			// aaand pull user data here
			$url = sprintf('http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%1$s&steamids=%2$s', $this->web_api_key, $steam_id);
			$json = file_get_contents($url);
			if($json === false)
			{
				// store false in the cache to prevent API slamming
				$member_data = false;
			}
			else
			{
				try
				{
					$data = JSON::decode($json);
					$member_data = $data['response']['players'][0];
				}
				catch(RuntimeException $e)
				{
					// store false in the cache to prevent API slamming
					$member_data = false;
				}
			}

			$cache->storeData('steam_member_' . $steam_id, $member_data, $ttl * 60 /* defaults to two minutes */);
		}

		return $member_data;
	}
}
