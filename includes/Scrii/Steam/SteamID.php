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
use \RuntimeException;

/**
 * @ignore
 */
if(!defined('Codebite\\Quartz\\SITE_ROOT')) exit;

class SteamID
{
	private $steamID32 = '';

	private $steamID64 = '';

	public function __construct($steam_id)
	{
		if(ctype_digit($steam_id))
		{
			$this->steamID64 = $steam_id;
			$this->steamID32 = $this->convert64to32($steam_id);
		}
		elseif(preg_match('/^STEAM_0:[01]:[0-9]+/', $steam_id))
		{
			$this->steamID32 = $steam_id;
			$this->steamID64 = $this->convert32to64($steam_id);
		}
		else
		{
			throw new RuntimeException('Invalid data provided; data is not a valid steamid32 or steamid64');
		}
	}

	private function convert32to64($steam_id)
	{
		list( , $m1, $m2) = explode(':', $steam_id, 3);
		list($steam_cid, ) = explode('.', bcadd((((int) $m2 * 2) + $m1), '76561197960265728'), 2);
		return $steam_cid;
	}

	private function convert64to32($steam_cid)
	{
		$id = array('STEAM_0');
		$id[1] = substr($steam_cid, -1, 1) % 2 == 0 ? 0 : 1;
		$id[2] = bcsub($steam_cid, '76561197960265728');
		if(bccomp($id[2], '0') != 1)
		{
			return false;
		}
		$id[2] = bcsub($id[2], $id[1]);
		list($id[2], ) = explode('.', bcdiv($id[2], 2), 2);
		return implode(':', $id);
	}

	public function getSteamID32()
	{
		return $this->steamID32;
	}

	public function getSteamID64()
	{
		return $this->steamID64;
	}
}
