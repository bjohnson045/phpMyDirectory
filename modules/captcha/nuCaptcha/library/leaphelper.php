<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

class lmcHelper
{
	const PUBLISHER_VERSION = 5;

	/**
	 * GetClientKey:
	 * Gets the client key from the global config (leap.config)
	 *
	 * @return lmcTextChunkData
	 */
	static public function GetClientKey()
	{
		return Leap::getClientKey();
	}

	/**
	 * DecodeToken:
	 * Decodes an encoded token response, based on the key passed in
	 *
	 * @param string $enctoken		- the encoded token
	 * @param string $key			- the key to decrypt the token
	 * @return lmcTextChunkData		- the unencrypted text chunk data
	 */
    static public function DecodeToken($enctoken, $key)
    {
        // Is the encoded response definitely not valid?
		if (lmcSymmetricMessage::IsInvalid($enctoken))
		{
			// See if we can decode it - is it an error?
			$clientKey = lmcHelper::GetClientKey();
			$error = LeapException::Import($enctoken, $clientKey->GetChunk("SKEY"));
			if (null == $error)
			{
				throw new Exception("INVALID TOKEN RESPONSE $enctoken", LMSC_INVALIDTRES);
			}
			else
			{
				throw new Exception($error->GetChunk("EMSG"), $error->GetChunk("ECODE"));
			}
		}

		// ***
		// *** Decipher the returned token
		// ***
		$tokenChunk = lmcSymmetricMessage::DecipherMessage($key, $enctoken);

		if (false === $tokenChunk)
		{
			throw new Exception("INVALID TOKEN RESPONSE", LMSC_INVALIDTRES);
		}

		// *** Decode the textchunk
		$chunk = lmcTextChunk::Decode($tokenChunk, "*");

		if( $chunk->ChunkExists("EREPORT") )
		{
			Leap::SetReportingMode( $chunk->GetChunk("EREPORT") );
		}

		if( Leap::GetTestAction() === 'exception-token' )
		{
			throw new Exception("Test exception-token", LMSC_TEST);
		}

		if(0 == strcmp($chunk->GetChunk("TYPE"), "TRES_DISABLED"))
		{
			$disabledReason = 'unknown';
			$version = intval($chunk->GetChunk("VERSION"));
			if( $version >= 1 )
			{
				$disabledReason = $chunk->GetChunk("DISABLED_REASON");
			}
			throw new Exception("Publisher disabled: " . $disabledReason, LMSC_PUBLISHER_DISABLED);
		}
		else if (0 != strcmp($chunk->GetChunk("TYPE"), "TRES"))
		{
			// See if we can decode it - is it an error?
            $clientKey = lmcHelper::GetClientKey();
		    $error = LeapException::Import($enctoken, $clientKey->GetChunk("SKEY"));
		    if (null == $error)
		    {
			      throw new Exception("INVALID TYPE: " . $chunk->GetChunk("TYPE"), LMSC_INVALIDTRES);
		    }
		    else
		    {
			      throw new Exception($error->GetChunk("EMSG"), $error->GetChunk("ECODE"));
		    }
		}

		return $chunk;
	}

	/**
	 * CreateRequestChunk:
	 * Creates a request lmcTextChunk. Adds all of the environment data to the chunk.
	 *
	 * @param string $type			- the type of the chunk (probably either "VREQ" or "TREQ")
	 * @param bool $addRFDefault	- true if you want to default the referrer data to "" if it can't be found
	 * @return lmcTextChunk
	 */
	static public function CreateRequestChunk($type, $addRFDefault = true)
    {
		$chunk = new lmcTextChunk($type);

		// Publisher version
		$chunk->AddChunk("PUB_VER", self::PUBLISHER_VERSION);

		// Store the time stamp
		$chunk->AddChunk("TIME", time());

		// Store the session id
		$chunk->AddChunk("SESID", session_id());

		// Store the user IP and X-Forward
		if(array_key_exists('REMOTE_ADDR', $_SERVER))
		{
			$chunk->AddChunk("IP", $_SERVER['REMOTE_ADDR']);
		}
		else
		{
			$chunk->AddChunk("IP", "UNKNOWN");	
		}
		
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER))
		{
			  $xf = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else
		{
			  $xf = "";
		}
		$chunk->AddChunk("XF", $xf);

		// record the user agent
		if (true == array_key_exists('HTTP_USER_AGENT', $_SERVER))
		{
			  $chunk->AddChunk('UA', $_SERVER['HTTP_USER_AGENT']);
		}
		else
		{
			  // empty string -- we don't have it
			  $chunk->AddChunk('UA', '');
		}

		// record the uri
		if( true == array_key_exists('REQUEST_URI', $_SERVER ) )
		{
			$chunk->AddChunk('RU', self::GetRequestString());
		}
		else
		{
			$chunk->AddChunk('RU', "");
		}
		
		// record the referrer
		if (true == array_key_exists('HTTP_REFERER', $_SERVER))
		{
			$chunk->AddChunk('RF', $_SERVER['HTTP_REFERER']);
		}
		else if ( $addRFDefault == true )
		{
			$chunk->AddChunk('RF', "");
		}

		return $chunk;
	}


	/**
	 * This will generate a constant ID for a web user.  IT WILL NOT LIKELY CHANGE OVER TIME.
	 *
	 * This should not be used as a secure value.
	 */
	static public function GenerateWebUserID()
	{
		static $server_keys = array
		(
			'REMOTE_ADDR',
			'HTTP_X_FORWARDED_FOR',
			// Certain crappy browsers set HTTP_ACCEPT to */* after a page refresh.
			// Since it's not static between requests we cannot use it here.
			// Some 'crappy browsers' user agents from hoptoad that were showing this behavior:
				// Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; MRA 4.6 (build 01425))
				// Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; MRA 4.3 (build 01218); .NET CLR 1.1.4322)
				// Mozilla/5.0 (X11; U; Linux; i686; en-US; rv:1.6) Gecko Galeon/1.3.14
				// Mozilla/5.0 (X11; U; Linux x86_64; de; rv:1.9) Gecko/2008061017 Firefox/3.0
				// Opera/9.0 (Windows NT 5.1; U; en)
			//'HTTP_ACCEPT',
			'HTTP_USER_AGENT',
			'HTTP_ACCEPT_LANGUAGE',
			'HTTP_ACCEPT_ENCODING'
		);

		$id = '';
		foreach ($server_keys as $key)
		{
			if (array_key_exists($key, $_SERVER))
			{
				$id .= $_SERVER[$key];
			}
		}

		return md5($id);
	}
	
	/**
	 * Extract a request string from the server var.
	 */ 
	static private function GetRequestString()
	{
		$protocol = ( array_key_exists('HTTPS', $_SERVER) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http');
		return $protocol . '://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'];
	}
	
	/**
	 * Determine if the the current OS is Windows and check the PHP version.
	 *
	 * There are several cases where functionality in Windows doesn't work unless
	 * we're running a recent version.
	 *
	 * @param int $phpMinVersion minimum version of PHP -- 50300 for 5.3.0
	 * @return bool true == meets (Windows && Version) || (!Windows), false == Windows && version is too old
	 */
	static public function checkWindowsVersion($phpMinVersion)
	{
		if(false === array_key_exists('SERVER_SOFTWARE', $_SERVER))
		{
			// server software not set -- not running under a web server
			return true;
		}
		
		if((stristr($_SERVER['SERVER_SOFTWARE'], 'win32') !== false || stristr($_SERVER['SERVER_SOFTWARE'], 'microsoft') !== false)
			&& PHP_VERSION_ID < $phpMinVersion)
		{
			return false;
		}
		
		return true;
	}

	static public function GetIVFromToken($token)
	{
		$iv = '';
		if( null != $token )
		{
			$s = explode('.',$token);
			if( count($s) > 5 )
			{
				$iv = $s[5];
			}
		}
		return $iv;
	}

	/**
	 * Check if dns_get_record() is supported on this platform
	 *
	 * @return bool
	 */
	public static function isDnsGetRecordSupported()
	{
		$getDnsRecordSupported = true;


		if(false === function_exists('dns_get_record'))
		{
			$getDnsRecordSupported = false;
		}
		elseif(false === lmcHelper::checkWindowsVersion(50300))
		{
			$getDnsRecordSupported = false;
		}

		return $getDnsRecordSupported;
	}

	public static function messageModeToMessageMethod($mmode)
	{
		if( $mmode == Leap::MMODE_COMPRESSED_ENCRYPTED )
		{
			return lmcSymmetricMessage::METHOD_AES128_CBC_LEAP_GZ;
		}
		return lmcSymmetricMessage::METHOD_AES128_CBC_LEAP;
	}
}
