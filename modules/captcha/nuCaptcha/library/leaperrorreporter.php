<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

/**
 * Class for reporting errors.
 */
class lmcErrorReporter
{
	private static $sAPIKey    = '7cec5019f0fab6711577586d9af03943';
	private static $sHotPadUrl = 'http://airbrakeapp.com/notifier_api/v2/notices';

	private static $sExtra     = Array();

	private function __construct() {}

	private static $sForceNoCurl = false;
	private static $sMode = 'NONE';
	
	public static $MODE_NONE = 'NONE';
	public static $MODE_HOPTOAD = 'HOPTOAD';

	public static function SetReportMode($mode)
	{
		self::$sMode = $mode;
		switch($mode)
		{
			case self::$MODE_NONE: self::$sMode = self::$MODE_NONE; break;
			case self::$MODE_HOPTOAD: self::$sMode = self::$MODE_HOPTOAD; break;
		}
	}

	public static function GetReportMode()
	{
		return self::$sMode;
	}
	
	/**
	 * Disable curl. Used for testing.
	 *
	 * @param bool $status
	 */
	public static function forceNoCurl($status)
	{
		self::$sForceNoCurl = $status;
	}
	
	/**
	 * Send an error report to the logging service.
	 *
	 * @param Exception $e
	 * @param Array $additionalData Additional data to send as key => value
	 * @return bool did the remote end accept the error report?
	 */
	public static function ReportException(Exception $e, Array $additionalData = Array())
	{
		// Only hoptoad is currently supported
		if( self::$sMode != self::$MODE_HOPTOAD )
		{
			return false;
		}

		$key = Leap::GetClientKey();
		
		if(null != $key)
		{
			$additionalData['publisherId'] = $key->GetChunk('CID');
		}

		$additionalData['clientVersion'] = 'PHP '. Leap::GetVersion();

		$additionalData['phpVersion'] = PHP_VERSION_ID;

		if(defined('LM_PLATFORM'))
		{
			$additionalData['platform'] = LM_PLATFORM;
		}
		
		$data = array_merge(self::$sExtra, $additionalData);

		if( method_exists($e, 'GetExtra') )
		{
			$data = array_merge($data, $e->GetExtra());
		}
				
		$xml = self::assembleMessage(
			get_class($e),
			$e->getMessage(),
			$e->getTrace(),
			self::assembleRequestURL(),
			"PHP Client Lib",
			"",
			$_SERVER,
			$data,
			self::assembleServerEnvironment()
		);

		//echo ('<textarea rows="50" cols="80">'.$xml.'</textarea>');
		return self::sendRequest($xml);	
	}

	/**
	 * Pass key/value pairs to the next hoptoad report
	 * @param string $key
	 * @param string $value
	 */
	public static function SetErrorData($key, $value)
	{
		self::$sExtra[$key] = $value;
	}

	private static function assembleServerEnvironment()
	{
		$env = Array(
			'environmentName' => Leap::GetClusterRecord(),			 
		);
		
		return $env;
	}
	
	private static function assembleRequestURL()
	{
		if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)
		{
			$protocol = 'https';
		} else
		{
			$protocol = 'http';
		}
		$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$path = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		$query_string = isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING']) ? ('?' . $_SERVER['QUERY_STRING']) : '';
		return "{$protocol}://{$host}{$path}{$query_string}";
	}
	
	private static function assembleMessage($class,
											$message,
											Array $stackTrace,
											$url,
											$component,
											$action,
											Array $cgiData,
											Array $sessionData,
											Array $serverEnvironment)
	{
		// clean input
		$class = self::forXML($class);
		$message = self::forXML($message);
		$url = self::forXML($url);
		$action = self::forXML($action);
		$component = self::forXML($component);

		//
		$xmlOut = self::getRequestHeader();
		
		// add the error section
		$xmlOut .= "\t<error>\n";
		$xmlOut .= "\t\t<class>$class</class>\n";
		$xmlOut .= "\t\t<message>$message</message>\n";
		
		// backtrace
		$xmlOut .= "\t\t<backtrace>\n";
		
		foreach($stackTrace as $index => $step)
		{			
			$method = self::forXML($step['function']);
			
			$file = 'n/a';
			if(true === array_key_exists('file', $step))
			{
				$file = $step['file'];
			}
			elseif(true === array_key_exists('class', $step))
			{
				$file = $step['class'];
			}
			
			$line = 0;
			
			if(true === array_key_exists('line', $step))
			{
				$line = $step['line'];
			}
			
			
			$xmlOut .= "\t\t\t";
			
			$xmlOut .= sprintf('<line method="%s" file="%s" number="%d"/>',
							   $method,
							   $file,
							   $line
							   );
			
			$xmlOut .= "\n";
		}
		
		$xmlOut .= "\t\t</backtrace>\n";
		$xmlOut .= "\t</error>\n";
		
		// add the request section
		$xmlOut .= "\t<request>\n";
		$xmlOut .= "\t\t<url>$url</url>\n";
		$xmlOut .= "\t\t<component>$component</component>\n";
		$xmlOut .= "\t\t<action>$action</action>\n";
		
		// CGI
		$xmlOut .= "\t\t<cgi-data>\n";
		$xmlOut .= self::buildVarArray("\t\t\t", $cgiData);
		$xmlOut .= "\t\t</cgi-data>\n";
		
		// Session
		if(0 != sizeof($sessionData))
		{
			$xmlOut .= "\t\t<session>\n";
			$xmlOut .= self::buildVarArray("\t\t\t", $sessionData);
			$xmlOut .= "\t\t</session>\n";
		}
		else
		{
			//$xmlOut .= "\t\t<session/>\n";	
		}
		
		$xmlOut .= "\t</request>\n";
		
		// server enviornment section
		$xmlOut .=<<<EOS
	<server-environment>
		<environment-name>$serverEnvironment[environmentName]</environment-name>
	</server-environment>

EOS;
		
		$xmlOut .= self::getRequestFooter();
		
		return $xmlOut;
	}
	
	private static function buildVarArray($preamble, Array $data)
	{
		$xmlOut = "";
		foreach($data as $key => $value)
		{
			$xmlOut .= "$preamble<var key='$key'>".self::forXML($value)."</var>\n";
			//$xmlOut .= "$preamble<var key='$key'><![CDATA[$value]]></var>\n";
		}
		
		return $xmlOut;
	}

	private static function forXML($str)
	{
		
		$str = strval($str);
		if( $str == null || $str == '' )
		{
			return $str;
		}
		$str = htmlentities($str);
		$str = str_replace("'", "&apos;", $str);
		return $str;
		//return "<![CDATA[$str]]>";
	}
	
	private static function getRequestHeader()
	{
		$header =<<<EOH
<?xml version="1.0" encoding="UTF-8"?>
<notice version="2.0">
  <api-key>%s</api-key>
  <notifier>
    <name>Leap PHP Notifier</name>
    <version>1.0.0</version>
    <url>http://www.leapmarketing.com/</url>
  </notifier>

EOH;
		return sprintf($header, self::$sAPIKey);
	}
	
	private static function getRequestFooter()
	{
		return '</notifier>';
	}
	
	private static function sendRequest($xml)
	{
		if(true === self::isCurlAvailable())
		{
			return self::sendRequestWithCurl($xml);
		}
		else
		{
			return self::sendRequestWithoutCurl($xml);
		}
	}
	
	private static function sendRequestWithCurl($xml)
	{
		$headerString = Array(
			"Accept: text/xml, application/xml",
			"Content-Type: text/xml",
			//"Content-Length: " . strlen($xml)
		);
	
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_URL,            self::$sHotPadUrl);
		curl_setopt($ch, CURLOPT_POST,           true);
		curl_setopt($ch, CURLOPT_HEADER,         false);
		curl_setopt($ch, CURLOPT_POSTFIELDS,     $xml);
		curl_setopt($ch, CURLOPT_HTTPHEADER,     $headerString);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
		$result = curl_exec($ch);
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
				
		/*if(200!=$status)
		{
			var_dump($result);
		}*/
		return $status == 200 ? true : false;
	}
	
	private static function sendRequestWithoutCurl($xml)
	{
		$params = array(
			'http' => array(
				'method' => 'POST',
				'content' => $xml,
			)
		);
		
		$params['http']['headers']  = "Accept: text/xml, application/xml\n";
		$params['http']['headers'] .= "Content-Type: text/xml\n";
		
		$ctx = stream_context_create($params);
		
		$fp = @fopen(self::$sHotPadUrl, 'rb', false, $ctx);
		
		if (!$fp)
		{
			//throw new Exception("Problem with $url, $php_errormsg");
			return false;
		}
		
		$response = @stream_get_contents($fp);
		if ($response === false) {
			//throw new Exception("Problem reading data from $url, $php_errormsg");
			return false;
		}
		
		return true;
	}
	
	private static function isCurlAvailable()
	{
		if(true === self::$sForceNoCurl)
		{
			// must be testing..
			return false;
		}
		
		if  (in_array ('curl', get_loaded_extensions()))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
	
}