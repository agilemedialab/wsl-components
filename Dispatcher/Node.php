<?php

/**
 * Node Object for WSL_Dispatcher
 *
 * PHP version 5
 *
 * @package   WSL_Dispatcher
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @link      http://www.weblogssl.com
 * @copyright Copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 *
 */

class WSL_Dispatcher_Node
{
    /**
     * Stores the number of children
     *
     * @var integer
	 */
    private $_numChildren;

    /**
     * Stores the children
     *
     * @var array
     */
    private $_children;

    /**
     * @var array
     */
    private $_childrenLiteral;
    
    /**
     * Stores the Controller
     *
     * @var string
     */
    private $_controller;
    
    /**
     * Stores the action
     *
     * @var string
     */
    private $_action;
    
    /**
     * @var string
     */
    private $_literal;
    
    /**
     * Stores the variable names
     *
     * @var array
     */
    private $_varName;
    
    /**
     * Stores the associated meta values
     *
     * @var array
     */
    private $_metaValues;
    
    /**
     * True if the node is active, false otherwise
     *
     * @var boolean
     */
    private $_active;

    /**
     * Stores the regular expression
     *
     * @var string
     */
    private $_expreg;

    /**
     * True if the node is a regular expression, false otherwise
     *
     * @var boolean
     */
    private $_isExpreg;

    /**
     * Public constructor
     *
     * @param string $literal
     * @param string $controller
     * @param string $action
     * @param boolean $active
     */
    public function __construct($literal, $controller = null, $action = null,
                                $active = false)
    {
        $this->_numChildren     = 0;
        $this->_controller      = $controller;
        $this->_action          = $action;
        $this->_literal         = $literal;
        $this->_active          = $active;
        $this->_children        = array();
        $this->_childrenLiteral = array();
        $this->_vars            = array();
        $this->_varName         = array();
        $this->_metaValues      = array();
        $this->_isExpreg        = false;
    }
    
    /**
     * Returns the number of children
     *
     * @return integer
     */
    public function getNumChildren()
    {
        return $this->_numChildren;
    }

    /**
     * Returns the controller name
     *
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }
    
    /**
     * Returns the action name
     *
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }
    
    /**
     * Returns the value of the literal attribute
     *
     * @return string
     */
    public function getLiteral()
    {
        return $this->_literal;
    }
    
    /**
     * If it exists, returns the regular expression (if exists)
     *
     * @return string
     */
    public function getExpreg()
    {
        return $this->_expreg;
    }
    
    /**
     * Returns the variable name
     *
     * @return string
     */
    public function getVarNames()
    {
        return $this->_varName;
    }
    
    /**
     * Returns a certain meta value
     *
     * @param string $key
     * @return string
     * @throws Exception
     */
    public function getMetaValue($key)
    {
        $value = null;
        
        if (isset($this->_metaValues[$key])) {
            $value = $this->_metaValues[$key];
        } else {
            throw new Exception('The meta value does not exist');
        }
        
        return $value;
    }

    /**
     * Returns all the meta values
     *
     * @return array
     */
    public function getMetaValues()
    {
        return $this->_metaValues;
    }

    /**
     * Returns the children nodes
     *
     * @return array
     */
    public function &getChildren()
    {
        return $this->_children;
    }

    /**
     * Set the controller value
     *
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
    }
    
    /**
     * Set the action value
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }
    
    /**
     * Set the literal value
     *
     * @param string $literal
     */
    public function setLiteral($literal)
    {
        $this->_literal = $literal;
    }
    
    /**
     * Sets a regular expression
     *
     * @param string $expreg
     */
    public function setExpreg($expreg)
    {
        $this->_expreg   = $expreg;
        $this->_isExpreg = true;
    }
    
    /**
     * Adds a new child and returns its reference
     *
     * @param WSL_Dispatcher_Node $node
     * @return WSL_Dispatcher_Node
     */
    public function &addChild(WSL_Dispatcher_Node &$node)
    {
        $this->_children[]        = $node;
        $this->_childrenLiteral[] = $node->getLiteral();
        
        $this->_numChildren++;
        
        return $this->_children[(count($this->_children) - 1)];
    }
    
    /**
     * Tries to find a child by a literal and returns its reference
     *
     * @param string $literal
     * @return WSL_Dispatcher_Node
     */
    public function &getChildByLiteral($literal)
    {
        $child = null;
        $found = false;

        for ($i = 0; $i < $this->_numChildren && !$found; $i++) {
            if ($this->_children[$i]->getLiteral() === $literal) {
                $child =& $this->_children[$i];
                $found = true;
            }
        }

        return $child;
    }
    
    /**
     * Tries to find a child by a regular expression and returns its reference
     *
     * @param string $expreg
     * @return WSL_Dispatcher_Node
     */
    public function &getChildByExpreg($expreg)
    {
        $child = null;
        $found = false;

        for ($i = 0; $i < $this->_numChildren && !$found; $i++) {
            if ($this->_children[$i]->getExpreg() === $expreg) {
                $child =& $this->_children[$i];
                $found = true;
            }
        }

        return $child;
    }
    
    /**
     * (Dis)Actives a node. Active means the node is a leaf 
     *
     * @param boolean $active
     * @throws Exception
     */
    public function setActive($active)
    {
        if (is_bool($active)) {
            $this->_active = $active;
        } else {
            throw new Exception('The parameter needs to be a boolean value');
        }
    }

    /**
     * Adds a variable name
     *
     * @param string $name
     * @param integer $order
     */
    public function addVarName($name, $order)
    {
        $this->_varName[$order] = $name;
    }
    
    /**
     * Adds a new meta value
     *
     * @param string $key
     * @param string $value
     * @throws Exception
     */
    public function addMetaValue($key, $value)
    {
        if (!empty($key) || !empty($value)) {
            $this->_metaValues[$key] = $value;
        } else {
            throw new Exception('The key and the value cannot be null');
        }
    }
    
    /**
     * Checks if the node is active (is a leaf)
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->_active;
    }
    
    /**
     * Checks if the node contains a literal in its children nodes
     *
     * @param string $literal
     * @return boolean
     */
    public function contains($literal)
    {
        return in_array($literal, $this->_childrenLiteral);
    }
    
    /**
     * Checks if the node is a regular expression
     *
     * @return boolean
     */
    public function isExpreg()
    {
        return $this->_isExpreg;
    }

    /**
     * Checks if one of the children contains a certain regular expression
     *
     * @param string $expreg
     * @return boolean
     */
    public function containsExpreg($expreg)
    {
        $contains = false;

        for ($i = 0; $i < $this->_numChildren && !$contains; $i++) {
            if ($this->_children[$i]->getExpreg() === $expreg) {
                $contains = true;
            }
        }

        return $contains;
    }
}

?>