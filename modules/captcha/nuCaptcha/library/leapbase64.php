<?php
/*
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(class_exists("lmcBase64", false))
{
	return;
}

class lmcBase64
{
	static private $sArraySeparator	= '|';

	/**
	 * Checks if data is valid base64
	 *
	 * @param string $base64Str
	 * @return bool false if invalid, true if valid
	 */
	static public function IsValidBase64($base64Str)
	{
		return preg_match('/[^-,_A-Za-z0-9]/', $base64Str) != 0;
	}
	
	/**
	 * This will encode a string as a url safe Base64 value.
	 *
	 * @param string $text - the text to encode
	 * @return string
	 */
	static public function EncodeString($text)
	{
		return self::UrlSafeEncode($text);	    
	}
	
	/**
	 * This will encode binary data - which is a string
	 *
	 * @param string $binary
	 * @return string
	 */
	static public function EncodeBinary($binary)
	{
		return self::UrlSafeEncode($binary);
	}

	/**
	 * EncodeArray:
	 * This will encode all the elements within an array, determing their encoding scheme
	 * 
	 * Header Format:
	 *   Number of Elements, followed by each element
	 * 
	 * Element Format:
	 *   Key, Value
	 * 
	 *   Key Format:
	 *     Int - Length of the key string
	 *     Seperator
	 *     String - The name of the key
	 *     Seperator
	 * 
	 *   Value Format:
	 *     Element Type
	 *       Integer:
	 *         i
	 *         Seperator
	 *         Number
	 *         Seperator
	 *       String:
	 *         s
	 *         Seperator
	 *         integer length
	 *         Seperator
	 *         String
	 *         Seperator
	 *     
	 * @param array $array
	 */
	static public function EncodeArray($array)
	{
		// Ensure it's an int we're receiving
		if (!is_array($array))
		{
			throw new LeapException("InvalidType");
		}

		// Figure out how many data keys we're encoding
		$numkeys = count($array);
		$sep = self::$sArraySeparator;

		// Start making the big string - store the count
		$data = $numkeys.$sep;
		foreach($array as $key=>$value)
		{
			// Store the key
			$data .= strlen($key).$sep.$key.$sep;

			// Store the value
			if (is_int($value))
			{
				$data .= 'i'.$sep.$value.$sep;
			}
			elseif (is_string($value))
			{
				$data .= 's'.$sep.strlen($value).$sep.$value.$sep;
			}
			else
			{
				throw new LeapException("lmcBase64::Encode: unknown format ".gettype($message), LMSC_INVALIDDATA);
			}
		}
		//print("\narray: $data\n");
		return self::UrlSafeEncode($data);
	}
	
	/**
	 * This will decode a url safe base64 string to the original string
	 *
	 * @param string $text
	 * @param bool $isText 
	 * @return string
	 */
	static public function DecodeString($text)
	{	
		return self::UrlSafeDecode($text);
	}
	
	/**
	 * This will decode binary data - also a string in PHP
	 *
	 * @param string $binary
	 * @return string
	 */
	static public function DecodeBinary($binary)
	{
		return self::UrlSafeDecode($binary);
	}

	/**
	 * ReadIntFromString:
	 * This will take a substring (until array seperator) and turn the portion before that into
	 * an int.  It will update pos to after the seperator.
	 *
	 * @param string $data	- data to read from
	 * @param int $pos - index into data
	 * @return int
	 */
	static private function ReadIntFromString($data, &$pos)
	{
		$newpos = strpos($data, self::$sArraySeparator, $pos);
		if (false === $newpos)
		{
			throw new LeapException("DecodeArray::ReadIntFromString - no int found", LMSC_INVALIDDATA);
		}
		$int = substr($data, $pos, $newpos-$pos);
		$pos = ($newpos+1);
		return $int;
	}

	/**
	 * DecodeArray:
	 * This will decode a series of key->value pairs and put them into an array.
	 *
	 * @param string $array
	 * @return array
	 */
	static public function DecodeArray($array)
	{
		// Get the seperator
		$sep = self::$sArraySeparator;

		// Decode to the array string
		$data = self::UrlSafeDecode($array);
		$out  = array();

		// Figure out the number of elements
		$pos = 0;
		$num = self::ReadIntFromString($data, $pos);

		// Read through each element
		while ($num-- > 0)
		{
			// Read the key
			$keylen = self::ReadIntFromString($data, $pos);
			$key    = substr($data, $pos, $keylen);
			$pos   += ($keylen + 1);
	
			// Read the type identifier
			$type   = substr($data, $pos, 1);
			$pos   += 2;

			// Read the data
			switch ($type)
			{
				case 'i':
					$out[$key]	= self::ReadIntFromString($data, $pos);
					break;
				case 's':
					$strlen		= self::ReadIntFromString($data, $pos);
					$out[$key]	= substr($data, $pos, $strlen);
					$pos	   += ($strlen+1);
					break;
				default:
					throw new LeapException("lmcBase64::Unknown format $type in the array", LMSC_INVALIDDATA);
					break;
			}
		}

		return $out;
	}
	
	/**
	 * Encode Base64 data safe for using in URL's
	 *
	 * @param string $data
	 * @return string
	 */
	private static function UrlSafeEncode ($data)
	{
	    return strtr(base64_encode($data), '+/=', '-_,');
	}
	
	/**
	 * Decode Base64 data from a URL safe format
	 *
	 * @param string data
	 * @return string
	 */
	private static function UrlSafeDecode ($data)
	{
	    return base64_decode(strtr($data, '-_,', '+/='));
	}
}
