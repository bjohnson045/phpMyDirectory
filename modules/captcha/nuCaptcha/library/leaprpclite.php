<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(class_exists("lmcRPCLite", false))
{
	return;
}

/**
 * DO NOT PRINT ANYTHING IN THIS FUNCTION.  ALL SERVERS USE THIS CODE TO
 * TALK TO OTHER SERVERS.  ECHO'ing HERE WILL INVALIDATE THEIR OUTPUT
 *
 */

/**
 * Leap RPC Client. Used to connect to Leap servers
 */
class lmcRPCLite
{
    /**
     * User agent string to use when connecting to server
     *
     * @var string
     */
    private static $sUserAgent = 'LEAP-RPC-PHP2 1.0.13738';
    
    /**
     * holds the socket connect
     *
     * @var resource
     */
    private $mSocket  = false;
    
    /**
     * url_parse() version of the server
     *
     * @var array
     */
    private $mServer  = false;
    
    /**
     * Message to send to the server
     *
     * @var string
     */
    private $mMessage = false;
    
    /**
     * Send the request to the server. Does not block.
     *
     * @return bool
     */
    public function SendRequest()
    {
		$urn = $this->mServer;
	
		$query =   "POST " . $this->mServer['path'] . " HTTP/1.0\r\n"
			 . "User-Agent: " . self::$sUserAgent . "\r\n"
			 . "Host: " . $this->mServer['host']  . "\r\n"
			 . "Connection: close\r\n"
			 . "Content-Type: application/x-www-form-urlencoded\r\n"
			 . "Content-Length: " . strlen('req='.$this->mMessage) . "\r\n\r\nreq="
			 . $this->mMessage;
		
		$e = false;
		try
		{
			/*
			 * surpress fsockopen errors. I've experimented with adding an
			 * error handler here, but I think the exception gives sufficient
			 * deteails.
			 */
			if ($this->mSocket = @fsockopen($this->mServer['host'], $this->mServer['port'], $errno, $errstr, 1))
			{				
				if (($errno != 0) || (get_resource_type($this->mSocket) != 'stream'))
				{
					$e = new LeapException(sprintf("Could not connect to %s:%d: %s",
							$this->mServer['host'],
							$this->mServer['port'],
							$e->GetMessage()
							), LMSC_COULDNOTCONNECT);
				}
				else
				{
					// See if we can post the data
					if (!fputs($this->mSocket, $query, strlen($query)))
					{
						$e = new LeapException(sprintf("Could not send data to %s:%d",
													$this->mServer['host'],
													$this->mServer['port']
													), LMSC_COULDNOTCONNECT);
					}
				}
			}
			else
			{
				$e = new LeapException(sprintf("Could not connect to %s:%d",
							$this->mServer['host'],
							$this->mServer['port']
							), LMSC_COULDNOTCONNECT);
			}
		}
		catch (Exception $e)
		{
			throw new LeapException(sprintf("Could not connect to %s:%d: %s",
							$this->mServer['host'],
							$this->mServer['port'],
							$e->GetMessage()
							), LMSC_COULDNOTCONNECT);
		}
		
		if ( $e != false )
		{
			throw $e;
		}
		
		return true;
    }
    
    /**
     * Read the result from the socket. May block if the server hasn't
     * completed sending the result.
     *
     * @return string
     */
    public function GetResult()
    {
		// Read all of the data response
		$rbuf = "";
		while (false === feof($this->mSocket))
		{
			$t = fgets($this->mSocket);

			if(false === $t)
			{
				throw new Exception("Error reading from socket.");
			}

			$rbuf .= $t;
		}
		
		fclose($this->mSocket);
		
		if (empty($rbuf))
		{
			$server = $this->mServer;
			throw new LeapException("Could not read from server: '$server[host]'", LMSC_COULDNOTCONNECT);
		}
		
		// split into array, headers and content.
		$hunks = explode("\r\n\r\n", trim($rbuf));
		if (!is_array($hunks) or count($hunks) < 2)
		{
			$server = $this->mServer;
			throw new LeapException("Could not read from server: '$server[host]'", LMSC_COULDNOTCONNECT);
        }
        
		$header  = $hunks[count($hunks) - 2];
		$body    = $hunks[count($hunks) - 1];
		$headers = explode("\n", $header);
		if (!lmcRPCLite::VerifyHttpResponse($headers))
		{
			$server = $this->mServer;
			throw new LeapException("Could not read from server: '$server[host]'", LMSC_COULDNOTCONNECT);
		}

		$message = urldecode(trim($body));
		
		return $message;
    }
    
    static private function VerifyHttpResponse($headers = null)
    {
		if (!is_array($headers) or count($headers) < 1)
		{
			return false;
		}
		
		switch (trim(strtolower($headers[0])))
		{
			case 'http/1.0 100 ok':
			case 'http/1.0 200 ok':
			case 'http/1.1 100 ok':
			case 'http/1.1 200 ok':
				return true;
			break;
			
			default:
			break;
        }
        
		return false;
    }
    
    /**
     * Create a new lmcRPCLite object for querying Leap servers.
     *
     * @param string $server URL of the server to connect to
     * @param string $mesage Message to send to the server
     */
    public function __construct($server, $message)
    {
		$this->mMessage = $message;
		$this->mServer  = parse_url($server);

		// make sure parse_url worked
		if(false == $this->mServer || false === array_key_exists('host', $this->mServer))
		{
			throw new LeapException("'$server' is an invalid URL", LMSC_COULDNOTCONNECT);
		}
		
		// set missing values for the server
		if(false === isset($this->mServer['port']))
		{
			$this->mServer['port'] = 80;
		}
		
		
		// if there's no path, set it to /
		if(false === isset($this->mServer['path']))
		{
			$this->mServer['path'] = '/';
		}
		
		// make sure there is a / on the end of the path
		if('/' != substr($this->mServer['path'], -1, 1))
		{
			$this->mServer['path'] .= '/';
		}
    }
    
    /**
     * Synchronous leap request
     *
     * @param string $server URL of the server to query
     * @param string $message encoded message to send
     * @return string
     */
    static public function Call($server, $message)
    {
		$client = new lmcRPCLite($server, $message);

		$client->SendRequest();

		return $client->GetResult();
    }
}
