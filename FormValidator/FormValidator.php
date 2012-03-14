<?php

/**
 * WSL_FormValidator
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

class WSL_FormValidator
{
	/**
	 * Requirements
	 * 
	 * @var array
	 */
	private $_requirements;

	/**
	 * Available Rules
	 * 
	 * @var array
	 */
	private $_rules;

	/**
	 * Public Constructor
	 *
	 */
	public function __construct()
	{
		$this->_requirements = array();
		$this->_rules        = array();
		
	    if ($handle = opendir(PEAR_INSTALL_DIR. '/WSL/FormValidator/Rules')) {
            while (false !== ($file = readdir($handle))) {
            	if (strpos($file, '.php') !== false) {
            	    require_once 'WSL/FormValidator/Rules/'. $file;

            	    $fileName       = substr($file, 0, -4);
            	    $this->_rules[] = $fileName;
             	}
            }
        }
	}
	
	/**
	 * Returns the available rules
	 * 
	 * @return array
	 */
	public function getAvailableRules()
	{
		return array_keys($this->_rules);
	}
	
	/**
	 * Adds a new requirement associated to a field
	 *
	 * @param string $field
	 * @param string $errorMsg
	 * @param string $rule
	 * 
	 * @throws Exception
	 */
	public function addRequirement($field, $errorMsg, $rule)
	{
		if (!empty($field) && !empty($rule) && in_array($rule, $this->_rules)) {

			$params = array_slice(func_get_args(), 3);
			
			if (!is_array($this->_requirements[$field])) {
				$this->_requirements[$field] = array();
			}
			
			$className = 'WSL_FormValidator_'. $rule;
			
			array_push($this->_requirements[$field],
			           new $className($errorMsg, $params[0]));

		} else {
			throw new Exception('The field name or the rule is not correct');
		}
	}
	
	/**
	 * Checks if the data fulfills the requirements
	 *
	 * @param array $data
	 * @return array
	 */
	public function validate($data)
	{
		$errors = array();

		foreach ($this->_requirements as $field => $requirements) {
			$value = null;

			if (isset($data[$field])) {
                $value = $data[$field];	
			}

			foreach ($requirements as $requirement) {
                $result = $requirement->check($value);

			    if (!empty($result)) {
				    foreach ($result as $error) {
					    $errors[$field][] = $error;
				    }
			    }	
			}
		}
		
		return $errors;
	}
}

?>