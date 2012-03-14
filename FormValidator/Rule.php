<?php

/**
 * WSL_FormValidator_Rule
 *
 * PHP version 5
 *
 * @package   WSL_FormValidator
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @link      http://www.weblogssl.com
 * @copyright Copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 *
 */

require_once 'WSL/FormValidator/Interface.php';

class WSL_FormValidator_Rule implements WSL_FormValidator_Rule_Interface
{
	/**
	 * Parameters
	 *
	 * @var $params
	 */
	public $params;
	
	/**
	 * Error Message
	 * 
	 * @var $errorMsg
	 */
	public $errorMsg;
	
	/**
	 * Public Constructor
	 *
	 * @param string $errorMsg
	 */
	public function __construct($errorMsg)
	{
		$this->errorMsg = $errorMsg;
		$this->params   = array_slice(func_get_args(), 1);
	}

	/**
	 * Checks if the data fulfills the requirements
	 *
	 * @param mixed $data
	 */
	public function check($data)
	{
		
	}
	
	/**
	 * Sets the default error message
	 * 
	 * @param string $errorMsg
	 */
	public function setDefaultErrorMsg($errorMsg)
	{
	    if ($this->errorMsg === null) {
        	$this->errorMsg = $errorMsg;
        }
	}
}

?>