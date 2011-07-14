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

namespace Scrii\TF2Stats\Page;
use \OpenFlame\Framework\Core;

abstract class Base
{
	protected $route;

	protected $template_name = '';

	public function setRoute(\OpenFlame\Framework\Router\RouteInstance $route)
	{
		$this->route = $route;
		return $this;
	}

	public static function newInstance()
	{
		return Core::setObject('page.instance', new static());
	}

	public static function newRoutedInstance(\OpenFlame\Framework\Router\RouteInstance $route)
	{
		$self = static::newInstance();
		$self->setRoute($route);

		return $self;
	}

	public function getTemplateName()
	{
		return $this->template_name;
	}

	abstract public function executePage();
}
