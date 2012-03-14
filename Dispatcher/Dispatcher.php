<?php

/**
 * WSL_Dispatcher
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

require_once 'WSL/Dispatcher/Router.php';
require_once 'WSL/Config/Config.php';

class WSL_Dispatcher
{
    /**
     * @var array
     */
    private $_routingConfig;

    /**
     * @var WSL_Dispatcher_Router
     */
    private $_router;

    /**
     * Public constructor
     * 
     * @param array $config
     * @param boolean $cache
     */
    public function __construct($routes, $cache = false)
    {
        $this->_router = new WSL_Dispatcher_Router($routes, $cache);
    }

    /**
     * Creating a routable URL path depending on a set of parameters,
     * including the controller name.
     *
     * Parameter Array has to follow the routing.ini config to match
     * a set of params to a controller.
     *
     * The generated url can be used in templates for links to controllers
     *
     * @param array $params
     * @return string
     * @throws Exception
     */
    public function toUrl($params = array())
    {
        $url = null;
        
        try {
            $url = $this->_router->toUrl($params);
        } catch (Exception $e) {
            throw $e;
        }
        
        return $url;
    }

    /**
     *
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function toParams($url)
    {
        $params = array();

        try {
            $params = $this->_router->toParams($url);
        } catch (Exception $e) {
            throw $e;
        }

        return $params;
    }
}
