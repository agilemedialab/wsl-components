<?php

/**
 * WSL_FormValidator_NoEmpty
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
require_once 'WSL/FormValidator/Rule.php';

final class WSL_FormValidator_NoEmpty extends WSL_FormValidator_Rule
{	
	/**
	 * Checks if the data fulfills the requirements
	 *
	 * @param mixed $data
	 */
	public function check($data)
	{
        $errors = array();
        
        $this->setDefaultErrorMsg('The field is empty');
        
	    if (empty($data)) {
		    $errors[] = $this->errorMsg;	
		}
		
		return $errors;
	}
}

?>