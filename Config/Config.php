<?php
/**
 * @package   WSL_Config
 * @author    Alfonso Jimenez <yo a.t alfonsojimenez.com>
 * @link      http://www.weblogssl.com
 * @copyright Copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 */

/**
 *
 * @package WSL.Config
 */
class WSL_Config
{
    /**
     * @var array
     */
    private $_configArray;
    
    /**
     * @var string
     */
    private $_fileName;
    
    /**
     * Public constructor
     *
     * @param string $fileName
     * @throws Exception
     */
    public function __construct($fileName)
    {
        $this->_configArray = array();
        $this->_fileName    = $fileName;
        
        if (file_exists($fileName)) {
            $this->_configArray = parse_ini_file($fileName, true);
            
            if ($this->_configArray === false) {
            	throw new Exception('The file cannot be opened');
            }
        } else {
        	throw new Exception('The file does not exist: '. $fileName);
        }
    }

    /**
     * Returns a config section:
     *
     * [section]
     * key = "value"
     *
     * as
     *
     * array('key'=>'value')
     *
     * @param string $section
     * @return array
     */
    public function getSection($section)
    {
    	$result = array();
    	
        if (isset($this->_configArray[$section])) {
            $result = $this->_configArray[$section];
        }
        
        return $result;
    }

    /**
     * Retrieves a specific key value from a section
     *
     * @param string $section
     * @param string $key
     * @return string
     */
    public function getValue($section, $key)
    {
    	$result = null;
    	
        if (isset($this->_configArray[$section])) {
        	if (isset($this->_configArray[$section][$key])) {
        		$result = $this->_configArray[$section][$key];
        	}
        }
        
        return $result;
    }

    /**
     * Sets a specific value for a section
     *
     * @param string $section
     * @param string $key
     * @param string $value
     * @return boolean
     * @throws Exception
     */
    public function setValue($section, $key, $value)
    {
    	$return = false;
    	
    	if (!empty($key)) {
            if (!isset($this->_configArray[$section])) {
                $this->_configArray[$section] = array();
            }
        
            $return                             = true;
            $this->_configArray[$section][$key] = $value;
    	} else {
    		throw new Exception('The key cannot be null');
    	}
    	
    	return $result;
    }

    /**
     * Returns the config file name
     * 
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }
    
    /**
     * Merges two WSL_Config objects. If $override is
     * true, the original section values will be overridden by
     * the new ones. Otherwise the shared section values will
     * be ignored.
     * 
     * @param WSL_Config $config
     * @param boolean $override
     */
    public function merge(WSL_Config $config, $override = true)
    {
    	$newConfigArray = $config->toArray();
    	
        foreach ($newConfigArray as $section => $values) {
            if (isset ($this->_configArray[$section])) {
            	$oldSection = $this->_configArray[$section];
                $newSection = $newConfigArray[$section];
                
            	if ($override) {
                    $this->_configArray[$section] = array_merge($oldSection,
                                                                $newSection);
            	} else {
            		$diff = array_diff_key($newSection, $oldSection);
            		
                    $this->_configArray[$section] = array_merge($oldSection, $diff);
            	}
            } else {
            	$this->_configArray[$section] = $values;
            }
        }
    }

    /**
     * Returns the raw config array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_configArray;
    }
}