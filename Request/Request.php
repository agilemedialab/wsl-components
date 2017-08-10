<?php
/**
 * WSL_Request
 *
 * PHP version 5
 *
 * @category  HTTP
 * @package   WSL_Request
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @author    "Arno Schneider" <arnoschn@googlemail.com>
 * @copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 * @link      http://www.weblogssl.com
 *
 */

/**
 * Request class giving access to the Request Parameters
 *
 * @category  HTTP
 * @package   WSL_Request
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @author    "Arno Schneider" <arnoschn@googlemail.com>
 * @copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 * @link      http://www.weblogssl.com
 */
class WSL_Request
{
    /**
     * Flag to determine whether there are dynamic GET or POST parameters
     *
     * @var boolean
     */
    private static $_dynamic = false;

    /**
     * Gets a value from the POST data by name
     *
     * @param string  $name      Variable Name
     * @param string  $validator Validator Name
     * @param string  $purifier  Purifier Name
     * @param boolean $required  Is it a required parameter?
     * 
     * @return mixed
     * @throws Exception
     */
    public static function getPostVar($name, $validator = null, $purifier = null, $required = false)
    {
        $value = null;

        if ($required && (!isset($_POST[$name]) || empty($_POST[$name]))) {
            throw new RequiredException($name);
        } else {
            $value = self::getVar('POST', $name, $validator, $purifier);
        }

        return $value;
    }

    /**
     * Gets a value from the GET data by name
     *
     * @param string  $name      Variable Name
     * @param string  $validator Validator Name
     * @param string  $purifier  Purifier Name
     * @param boolean $required  Is it a required parameter?
     * 
     * @return mixed
     * @throws Exception
     */
    public static function getGetVar($name, $validator = null, $purifier = null, $required = false)
    {
        $value = null;

        if ($required && (!isset($_GET[$name]) || empty($_GET[$name]))) {
            throw new RequiredException($name);
        } else {
            $value = self::getVar('GET', $name, $validator, $purifier);
        }

        return $value;
    }

    /**
     * Gets a value
     *
     * @param string  $type      HTTP Request Type  
     * @param string  $name      Variable Name
     * @param string  $validator Validator Name
     * @param boolean $purifier  Purifier Name
     * 
     * @return mixed
     * @throws Exception
     */
    public static function getVar($type, $name, $validator = null, $purifier = null)
    {
        $value     = null;
        $validates = true;

        if ($type === 'POST' && isset($_POST[$name])) {
            $value = $_POST[$name];
        } else if (isset($_GET[$name])) {
            $value = $_GET[$name];
        }

        if ($value !== null) {
            if ($validator !== null) {
                $validates = false;

                if (!is_callable($validator)) {
                    throw new InvalidCallbackException($validator);
                } else {       
                    if (call_user_func($validator, $value)) {
                        $validates = true;
                    } else {
                        throw new ValidationException($name, $value);
                    }
                }
            } else {
                trigger_error('No validator is defined', E_USER_WARNING);
            }
    
            if ($validates && ($purifier !== null)) {
                if (!is_callable($purifier)) {
                    throw new InvalidCallbackException($purifier);
                } else {
                    $value = call_user_func($purifier, $value);
                }
            }
        }

        return $value;
    }
    
    /**
     * Gets $_FILES
     *
     * @return array
     */
    public static function getFiles()
    {
        $value = null;

        if (!empty($_FILES['file']) && $_FILES['file']['error'] === 0) {
            $value = & $_FILES['file'];
        }

        return $value;
    }
    
    /**
     * Get the request path
     *
     * @return string
     */
    public static function getPath()
    {
        global $_SERVER;

        $path = null;
    
        if (isset($_SERVER['REDIRECT_URL'])) {
            $path = $_SERVER['REDIRECT_URL'];
        } else if (isset($_SERVER['REQUEST_URI']) && isset($_SERVER['QUERY_STRING'])) {
            /**
             * Get rid of query string, to just have the path
             */
            $path = $_SERVER['REQUEST_URI'];
            $path = str_replace('?' . $_SERVER['QUERY_STRING'], '', $path);
        }

        return $path;
    }
    
    /**
     * Returns the client IP address
     * 
     * @return string
     */
    public static function getIp()
    {
        $ip = getenv('HTTP_CLIENT_IP');

        if (empty($ip)) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');

            if (empty($ip)) {
                $ip = getenv('REMOTE_ADDR');

                if (empty($ip)) {
                    $ip = 'unknown';
                }
            }
        }

        return $ip;
    }
    
    /**
     * Gets the referer
     * 
     * @return string
     */
    public static function getReferer()
    {
        global $_SERVER;
        $referer = null;
    
        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }
    
        return $referer;
    }
    
    /**
     * Gets the modified time
     *
     * @return string
     */
    public static function getModifiedTime()
    {
        global $_SERVER;
        $time = null;
    
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $time = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
        }
    
        return $time;
    }
    
    /**
     * Flags this request as dynamic
     *
     * @param boolean $dynamic Set a dynamic request
     * 
     * @return void
     */
    public static function setDynamic($dynamic = true)
    {
        self::$_dynamic = $dynamic;
    }

    /**
     * Returns whether we have a dynamic request (GET or POST parameters)
     *
     * @return boolean
     */
    public static function isDynamic()
    {
        return (self::$_dynamic || (count($_GET) + count($_POST)) > 0);
    }

    /**
     * Checks if the server accepts a certain encoding
     *
     * @param string $encoding Encoding
     * 
     * @return boolean
     */
    public static function acceptsEncoding($encoding)
    {
        static $encodings;
    
        if (!isset($encodings)) {
            if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
                $encodings = explode(',', $_SERVER['HTTP_ACCEPT_ENCODING']);
            } else {
                $encodings = array();
            }
        }
    
        return in_array($encoding, $encodings);
    }

    /**
     * Does this request have POST parameters?
     *
     * @return boolean
     */
    public static function isPost()
    {
        return !empty($_POST);
    }
    
    /**
     * Does this request have GET parameters?
     *
     * @return boolean
     */
    public static function isGet()
    {
        return !empty($_GET);
    }
    
    /**
     * Get browser languages
     * 
     * @return array
     */
    public static function getBrowserLanguages()
    {
        $res = array();

        $languages = preg_replace('/q=[0-9\.]+,*/', '', @$_SERVER['HTTP_ACCEPT_LANGUAGE']);

        $browserAcceptedLanguages = str_replace('-', '_', strtolower($languages));
        $browserLanguages         = array_diff(explode(';|,', $browserAcceptedLanguages . ','), array(''));
               
        foreach ($browserLanguages as $lang) {
            $parts = explode('_', $lang);
            
            if (isset($parts[1]) && !in_array($parts[0], $browserLanguages)) {
                $browserLanguages[] = $parts[0];
            }
        }

        if (!empty($browserLanguages)) {
            $res = array_unique($browserLanguages);
        }
    
        return $res;
    }

    /**
     * Returns the client UserAgent
     * 
     * @return string
     */
    public static function getUserAgent()
    {
        $userAgent = 'Unknown';

        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
        }

        return $userAgent;
    }
}

/**
 * ValidationException
 *
 * PHP version 5
 *
 * @category  HTTP
 * @package   WSL_Request
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 * @link      http://www.weblogssl.com
 *
 */

class ValidationException extends Exception
{
    /**
     * Public constructor
     * 
     * @param string $name  Variable Name
     * @param string $value Variable Value
     * 
     * @return void
     */
    public function __construct($name, $value)
    {
        $message = 'The variable '.$name.' with value '.$value.' does not validate';
    
        parent::__construct($message);
    }
}

/**
 * RequiredException
 *
 * PHP version 5
 *
 * @category  HTTP
 * @package   WSL_Request
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 * @link      http://www.weblogssl.com
 *
 */

class RequiredException extends Exception
{
    /**
     * Public constructor
     * 
     * @param string $name Variable Name
     * 
     * @return void
     */
    public function __construct($name)
    {
        $message = $name.' cannot be null';
    
        parent::__construct($message);
    }
}

/**
 * InvalidCallbackException
 *
 * PHP version 5
 *
 * @category  HTTP
 * @package   WSL_Request
 * @author    "Alfonso Jimenez" <yo@alfonsojimenez.com>
 * @copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 * @link      http://www.weblogssl.com
 *
 */

class InvalidCallbackException extends Exception
{
    /**
     * Public constructor
     * 
     * @param string $name Callback Function
     * 
     * @return void
     */
    public function __construct($name)
    {
        $message = $name.' is not callable';
    
        parent::__construct($message);
    }
}