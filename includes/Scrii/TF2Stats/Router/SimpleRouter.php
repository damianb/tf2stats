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

namespace Scrii\TF2Stats\Router;
use \OpenFlame\Framework\Core;

class SimpleRouter
{
	protected $routes = array();

	public function newRoute($route_name, $route_class)
	{
		if(!class_exists($route_class))
		{
			throw new \RuntimeException('Invalid class specified to route to');
		}
		
		$this->routes[(string) $route_name] = $route_class;
		
		return $this;
	}
	
	public function getPage($page)
	{
		$page = strtolower($page);
		if(!isset($this->routes[(string) $page]))
		{
			$page = 'error';
		}
		
		$class = $this->routes[(string) $page];
	
		$instance = new $class();
		if($page == 'error')
		{
			$instance->setErrorCode(404);
		}
		
		return $instance;
	}
}
