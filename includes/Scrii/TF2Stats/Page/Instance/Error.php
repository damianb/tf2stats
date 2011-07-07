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

namespace Scrii\TF2Stats\Page\Instance;
use \OpenFlame\Framework\Core;
use \OpenFlame\Framework\Dependency\Injector;

class Error extends \Scrii\TF2Stats\Page\Base
{
	protected $error_code = 0;

	protected $template_name = 'error.twig.html';
	
	public function setErrorCode($error_code)
	{
		$this->error_code = (int) $error_code;
	}

	public function executePage()
	{
		$injector = Injector::getInstance();
		$template = $injector->get('template');
		$header = $injector->get('header');

		$header->setHTTPStatus($this->error_code);
		try
		{
			$error_string = $header->getHTTPStatusHeader();
		} 
		catch(\LogicException $e)
		{
			$header->setHTTPStatus(500);
			$error_string = $header->getHTTPStatusHeader();
		}
		
		$error_string = str_replace('HTTP/1.0 ', '', $error_string);
		
		$template->assignVars(array(
			'error_code'	=> $this->error_code,
			'error_string'	=> $error_string,
		));

		return;
	}
}
