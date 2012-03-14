<?php

/**
 * WSL_FormValidator_Rule_Interface
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
interface WSL_FormValidator_Rule_Interface
{
	/**
	 * Public Constructor
	 * 
	 * @param string $errorMsg
	 */
	public function __construct($errorMsg);
	
	/**
	 * Checks if the data fulfills the requirements
	 *
	 * @param mixed $data
	 */
	public function check($data);
	
	/**
	 * Sets the default error message
	 * 
	 * @param string $errorMsg
	 */
	public function setDefaultErrorMsg($errorMsg);
}

?>