<?php

/**
 * Router for WSL_Dispatcher
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

require_once 'WSL/Dispatcher/Node.php';

class WSL_Dispatcher_Router
{
    /**
     * @var array
     */
    private $_routes;

    /**
     * @var WSL_Dispatcher_Node
     */
    private $_tree;
    
    /**
     * @var array
     */
    private $_reverse;

    /**
     * @var array
     */
    private $_vars;

    /**
     * @var string
     */
    private $_fileTree;

    /**
     * @var string
     */
    private $_fileHashTable;
    
    /**
     * @var boolean
     */
    private $_cache;

    /**
     * Public constructor
     *
     * @param WSL_Config $routes
     * @param boolean $cache
     */
    public function __construct(WSL_Config $routes, $cache = false)
    {
        $this->_tree    = new WSL_Dispatcher_Node('/');
        $this->_reverse = array();
        $this->_vars    = array();
        $this->_cache   = $cache;
        
        $this->_fileTree      = null;
        $this->_fileHashTable = null;
        
        $this->_loadRoutes($routes);
    }

    /**
     * Retrieves the parameters from a URL
     * @see _lookUp
     *
     * @param string $url
     * @return array
     */
    public function toParams($url)
    {
        $node   = null;
        $params = array();
        
        if ($url === '/') {
            $node = $this->_tree;
        } else {
            $pieces = $this->_getPieces($url);

            $level = -1;
            $this->_lookUp($pieces, $this->_tree, $node, $level);
        }

        if (!empty($node)) {
            $params['route']      = $url;
            $params['controller'] = $node->getController();
            $params['action']     = $node->getAction();
            $params['__META__']   = $node->getMetaValues();
            
            $params['__META__']['url'] = $url;

            $params = array_merge($params, $this->_vars);
        } else {
            throw new Exception('Page not found');
        }
        
        return $params;
    }
    
    /**
     * Retrieves an URL from a set of parameters
     * @see _reverseLookup
     *
     * @param array $params
     * @return string
     */
    public function toUrl($params)
    {
        $url        = '/';
        $action     = $params['action'];
        $controller = $params['controller'];
        
        if (!empty($action) && !empty($controller)) {
            $url = $this->_reverseLookup($action, $controller, $params);
        } else {
            throw new Exception('The parameters are not correct');
        }

        return $url;
    }
    
    /**
     * Loads the routes from a config file. It tries to retrieve the object from a 
     * file if the cache is active, otherwise it will generate the tree
     * @see _processRoutes
     *
     * @param WSL_Config $routes
     */
    private function _loadRoutes(WSL_Config $routes)
    {
        $this->_routes = $routes->toArray();
        
        if ($this->_cache) {
            $this->_fileTree      = '/tmp/lp2/' . md5(serialize($routes));
            $this->_fileHashTable = '/tmp/lp2/h' . md5(serialize($routes));
        }
        
        if ($this->_cache && file_exists($this->_fileTree)
                                            && file_exists($this->_fileHashTable)) {
            /*
             * Tries to retrieve the objects from cache
             */
            $this->_tree    = $this->_getFile($this->_fileTree);
            $this->_reverse = $this->_getFile($this->_fileHashTable);
        } else {
            /**
             * Generates the objects
             */
            $this->_processRoutes();
        }
    }
    
    /**
     * Divides an url up into chunks
     *
     * @paramtring $route
     * @return array
     */
    private function _getPieces($route)
    {
        $pieces = array();
        
        if (!empty($route)) {
            $route  = trim($route, '/');
            $pieces = explode('/', $route);
        }
        
        return $pieces;
    }
    
    /**
     * Sets the root node 
     *
     * @param WSL_Dispatcher_Node $node
     * @param array $params
     */
    public function setRoot(WSL_Dispatcher_Node $node, $params)
    {
        $this->_tree->setController($node->getController());
        $this->_tree->setAction($node->getAction());
        
        $this->_processMetaValues($this->_tree, $params);
    }
    
    /**
     * Generates the tree using the routes as branches 
     *
     */
    private function _processRoutes()
    {
        foreach ($this->_routes as $routeId => $route) {
            
            $controller            = $route['controller'];
            $action                = $route['action'];
            $route['meta.routeid'] = $routeId;

            /**
             * Generates the tree
             */
            if ($route['route'] === '/') {
                $node = new WSL_Dispatcher_Node('/', $controller, $action);
                $this->setRoot($node, $route);
            } else {
                $newRoute  = preg_replace('@/([/]+)@', '/', $route['route']);
                $pieces    = $this->_getPieces($newRoute);
                $numPieces = (count($pieces) - 1);
                $active    = false;
                
                foreach ($pieces as $level => $piece) {
                    if ($level === $numPieces) {
                        $node = new WSL_Dispatcher_Node($piece,
                                                                $controller,
                                                                $action,
                                                                true);
                        $active = true;
                    } else {
                        $node = new WSL_Dispatcher_Node($piece,
                                                                $controller,
                                                                $action);
                    }
                    
                    /**
                     * Do the piece contain any variable?
                     */
                    if (preg_match_all('@:([a-z0-9_]+)@', $piece, $vars) > 0) {
                        $regexp   = $piece;
                        $varNames = $vars[1];

                        natsort($varNames);
                        rsort($varNames);

                        foreach ($varNames as $k => $var) {
                            if (isset($route['requirement.' . $var]) && !empty($route['requirement.' . $var])) {
                                $exp = $route['requirement.' . $var];
                            } else {
                                $exp = '[^\/]+';
                            }
                                
                            $regexp = str_replace(':' . $var, '('.$exp.')', $regexp);
                            $node->addVarName($var, $this->_getVarOrder($vars[1], $var));
                        }

                        $node->setExpreg($regexp);
                        
                        if ($level === 0) {
                            if (!$this->_tree->containsExpreg($exp)) {
                                $previousNode =& $this->_tree->addChild($node);
                            } else {
                                $previousNode =& $this->_tree
                                                           ->getChildByExpreg($exp);
                            }
                        } else {
                            if (!$previousNode->containsExpreg($exp)) {
                                $previousNode =& $previousNode->addChild($node);
                            } else {
                                $previousNode =& $previousNode
                                                           ->getChildByExpreg($exp);
                            }
                        }
                    } else {
                        if ($level === 0) {
                            if (!$this->_tree->contains($piece)) {
                                $previousNode =& $this->_tree->addChild($node);
                            } else {
                                $previousNode =& $this->_tree
                                                        ->getChildByLiteral($piece);
                            }
                        } else {
                            if (!$previousNode->contains($piece)) {
                                $previousNode =& $previousNode->addChild($node);  
                            } else {
                                $previousNode =& $previousNode
                                                        ->getChildByLiteral($piece);
                            }
                        }
                    }
                                               
                    $this->_proccessIfActive($active,
                                             $previousNode,
                                              $route);
                }
            }

            /**
             * Generates the reverse hash table
             */
            $pagination = isset($route['requirement.pagination_start'])?'s':'n';
            
            $hash = crc32($action.$controller.$pagination);
            
            $this->_reverse[$hash] = $route;
        }

        if ($this->_cache) {
            $this->_saveFile($this->_fileTree, $this->_tree);
            $this->_saveFile($this->_fileHashTable, $this->_reverse);
        }
    }
    
    /**
     * Gets the order of a variable in an array. If the variable
     * doesn't exist, the method returns -1
     *
     * @param array $vars
     * @param string $varName
     * @return integer
     */
    private function _getVarOrder($vars, $varName)
    {
        $order = -1;
        
        foreach ($vars as $k => $var) {
            if ($var === $varName) {
                $order = $k;
            }
        }
        
        return $order;
    }
    
    /**
     * Actives and adds the meta values into a node
     *
     * @param boolean $active
     * @param WSL_Dispatcher_Node $node
     * @param array $params
     */
    private function _proccessIfActive (&$active, &$node, $params)
    {
        if ($active) {
            $node->setAction($params['action']);
            $node->setController($params['controller']);
            $node->setActive(true);
            $active = false;
            
            $this->_processMetaValues($node, $params);
        }
    }
    
    /**
     * Processes the meta values
     *
     * @param WSL_Dispatcher_Node $node
     * @param array $params
     */
    private function _processMetaValues(&$node, $params)
    {
        foreach ($params as $key => $val) {
            if (strpos($key, 'meta.') !== false) {
                $node->addMetaValue(substr($key, 5), $val);
            }
        }
    }
    
    /**
     * Returns the cached object
     *
     * @param string
     * @return WSL_Dispatcher_Node
     */
    private function _getFile($file)
    {
        $object = unserialize(file_get_contents($file));

        return $object;
    }
    
    /**
     * Saves the object into cache
     * 
     * @param string
     * @param mixed
     * 
     * @return boolean
     */
    private function _saveFile($file, $content)
    {
        $existsDir = true;
        $saved     = false;
        $dir       = dirname($file);
        
        if (!file_exists($dir)) {
            $existsDir = @mkdir($dir, 0755, true);
        }
        
        if ($existsDir) {
            if (file_put_contents($file, serialize($content)) !== false) {
                $saved = true;
            }
        }
        
        return $saved;
    }
    
    /**
     * Walks through the tree structure and performs some computation at
     * each node in the tree
     *
     * @param array $urlPieces
     * @param WSL_Dispatcher_Node $tree
     * @param WSL_Dispatcher_Node $found
     * @param integer $level
     */
    private function _lookUp($urlPieces, $tree, &$found, $level)
    {
        if (empty($found)) {
            $level++;
            $size  = (count($urlPieces)-1);
            $piece = $urlPieces[$level];

            $children = $tree->getChildren();

            foreach ($children as $child) {
                if (!$child->isExpreg()) {
                    if ($child->getLiteral() === $piece) {
                        if (($child->getNumChildren() === 0 ||
                                           $child->isActive()) && $level == $size) {
                            $found = $child;
                        } else if ($level < $size) {
                            $this->_lookUp($urlPieces, $child, $found, $level);
                        }
                    }
                }
            }

            if (empty($found)) {
               foreach ($children as $child) {
                   if ($child->isExpreg()) {
                       $expReg = $child->getExpreg();
    
                       if (preg_match('@^'. $expReg .'$@', $piece, $matches) > 0) {
                           $vars = $child->getVarNames();
    
                           foreach ($vars as $k => $var) {
                           	  if (empty($found)) {
                                  $this->_vars[$var] = $matches[$k+1];
                           	  }
                           }
                          
                           if (($child->getNumChildren() === 0 ||
                                           $child->isActive()) && $level == $size) {
                               $found = $child;
                           } else if ($level < $size) {
                               $this->_lookUp($urlPieces, $child, $found, $level);
                           }
                       }
                   }
               }
            }
        }
    }
    
    /**
     * Checks if a route matches to a set of parameters
     *
     * @param string $action
     * @param string $controller
     * @param array $params
     * @return string
     */
    private function _reverseLookup($action, $controller, $params)
    {
        $pagination = isset($params['pagination_start'])?'s':'n';
        $hash       = crc32($action.$controller.$pagination);
        $url        = '/';
        
        if (isset($this->_reverse[$hash])) {
            $route  = $this->_reverse[$hash];
            $url    = $route['route'];
            $pieces = $this->_getPieces($route['route']);

            foreach ($pieces as $piece) {
                if (preg_match_all('@:([a-z0-9_]+)@', $piece, $vars) > 0) {

                    $varNames = $vars[1];

                    foreach ($varNames as $var) {
                        $url = str_ireplace(':' . $var, $params[$var], $url);
                    }
                }
            }
        }

        return $url;
    }
}

?>
