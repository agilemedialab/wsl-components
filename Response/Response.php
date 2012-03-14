<?php
/**
 * @package   WSL_Response
 * @author    Alfonso Jimenez <yo a.t alfonsojimenez.com>
 * @author    Arno Schneider <arnoschn a.t googlemail.com>
 * @link      http://www.weblogssl.com
 * @copyright Copyright 2009 Weblogs SL
 * @license   http://code.google.com/p/wsl-components/wiki/License BSD
 */

require_once 'WSL/Request/Request.php';

/**
 * Response class handling output to the client
 *
 * @package WSL_Response
 */
class WSL_Response
{
    /**
     * Headers that need to be send before the output
     *
     * @var array
     */
    protected static $headers = array();

    protected static $contentType;
    protected static $redirectUrl;
    protected static $redirectType;
    protected static $sendStatusCode;

	protected static $status = array(100 => 'HTTP/1.x 100 Continue',
	                                 200 => 'HTTP/1.x 200 OK',
	                                 201 => 'HTTP/1.x 201 Created',
	                                 202 => 'HTTP/1.x 202 Accepted',
	                                 203 => 'HTTP/1.x 203 Non-Authoritative Information',
	                                 204 => 'HTTP/1.x 204 No Content',
	                                 301 => 'HTTP/1.x 301 Moved Permanently',
	                                 304 => 'HTTP/1.x 304 Not Modified',
	                                 307 => 'HTTP/1.x 307 Temporary Redirect',
	                                 401 => 'HTTP/1.x 401 Access Denied',
	                                 403 => 'HTTP/1.x 403 Forbidden',
	                                 404 => 'HTTP/1.x 404 Not Found',
	                                 405 => 'HTTP/1.x 405 Method Not Allowed',
	                                 408 => 'HTTP/1.x 408 Request Timeout',
	                                 500 => 'HTTP/1.x 500 Internal Server Error',
	                                 502 => 'HTTP/1.x 502 Bad Gateway',
	                                 503 => 'HTTP/1.x 503 Service Unavailable',
	                                 504 => 'HTTP/1.x 504 Gateway Timeout'
	                           );
    /**
     * True if output buffering has been started
     * by WSL_Response::captureOutput
     *
     * @see captureOutput
     * @var boolean
     */
    protected static $capture = false;
 
    /**
     * adding a header that is going to be sent on output
     *
     * @param string $name
     * @param string $value
     */
    public static function addHeader($name, $value = null)
    {
    	self::$headers[$name] = false;
    	
        if (!empty($value)) {
        	self::$headers[$name] = $value;
        }
    }
    
    /**
     * Sets the content type
     * 
     * @param string $type
     *
     */
    public static function setContentType($type)
    {
        self::$contentType = $type;
    }
    
	/**
	 * Sets cache control directives
	 * 
	 * @param mixed $cache
	 * 
	 * @throws Exception
	 */
	public static function setCacheControl($cache)
	{
		if (!empty($cache)) {
			
			if (is_array($cache)) {
				
				$numValues = count($cache);
				$value     = '';
				
				foreach ($cache as $k => $directive) {
					
					if ($k < ($numValues - 1)) {
					    $value .= $directive . ', ';
					} else {
						$value .= $directive;
					}
				}
				
				$cache = $value;
			}
			
			self::setHeader('Cache-Control', $cache);

		} else {
			throw new Exception('The content type cannot be null');
		}
	}
    
    /**
     * Sets the content type to XML
     * @return unknown_type
     */
    public static function sendAsXml()
    {
        self::setContentType('application/xml; charset=UTF-8');
    }

    /**
     * Returns the status code
     * @return int
     */
    public static function getStatusCode()
    {
        return self::$sendStatusCode;
    }

    /**
     * Sets a HTTP status code
     * 
     * @param integer $statusCode
     * @param boolean $render
     * 
     * @return integer
     */
    public static function sendStatusCode($statusCode, $render = true)
    {
    	self::$sendStatusCode = false;
    	
        if (isset(self::$status[(int)$statusCode])) {            
            if ($render) {
                self::$sendStatusCode = $statusCode;
            } else {
                header(self::$status[(int)$statusCode]);
                return true;
            }
        }
        
        return self::$sendStatusCode;
    }

    /**
     * Redirects the enduser instead of outputting content
     *
     * @param string $url
     * @param integer $redirectType
     */
    public static function redirect($url, $redirectType = 302)
    {
        self::$redirectUrl  = $url;
        self::$redirectType = $redirectType;
    }
    
    /**
     * Gets redirect header if set
     * 
     * @return string
     */
    public static function getRedirect()
    {
        return self::$redirectUrl;
    }
    
    /**
     * Starts output buffering
     *
     * @param boolean $gzip
     * @param boolean $useOutputHandler
     */
    public static function captureOutput($gzip = false, $useOutputHandler = true)
    {
        if (!WSL_Request::acceptsEncoding('gzip')) {
            $gzip = false;
        }
        
        self::$capture = true;

        ob_start();
    }
    
    /**
     * Returns the captured chunk
     *
     * @return string
     */
    public static function getPart()
    {
        $part = ob_get_flush();

        return $part;
    }
    
    /**
     * Sends the headers and then the output either directly
     * to the client or returns it as string
     *
     * @return string
     */
    public static function send()
    {
        $content = null;
        
        if (isset(self::$contentType)) {
            header('Content-Type: ' . self::$contentType);
        }
        
        if (isset(self::$sendStatusCode)) {
            /**
             * we send the 404 and clean the rest of the headers,
             * since we only want to send the 404
             */
            header(self::$status[self::$sendStatusCode]);
            self::$headers = array();
        } else if (!empty(self::$redirectUrl)) {
            ob_end_clean();
            header('Location: ' . self::$redirectUrl, true, self::$redirectType);
            exit;
        }

        foreach (self::$headers as $name => $value) {
            header($name . ($value == false ? '' : ': ' . $value));
        }

        $content = ob_get_clean();
            
        self::$capture = false;
        
        return $content;
    }
}
