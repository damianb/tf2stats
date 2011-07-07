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
 * @copyright   (c) 2010 - 2011 scrii.com
 * @license     GPLv3
 *
 *===================================================================
 *
 */

namespace Scrii\TF2Stats\Page;
use \OpenFlame\Framework\Core;

abstract class Base
{
	protected $template_name = '';
	
	public static function newInstance()
	{
		return Core::setObject('page.instance', new static());
	}
	
	public function getTemplateName()
	{
		return $this->template_name;
	}

	abstract public function executePage();
}
