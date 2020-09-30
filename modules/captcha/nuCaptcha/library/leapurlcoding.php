<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

/**
 * Encode/decode param strings
 */
if(false === class_exists('lmcUrlCoding', false))
{
	class lmcUrlCoding
	{
		/**
		 * Use URL encoding to flatten out an object.
		 *
		 * @param Array $data complex data structure to encode.
		 * @return string something like key[0]=value&key[1]=value
		 */
		public static function encodeStructure(Array $data)
		{
			// special case: no keys: return ''
			if(0 == sizeof($data))
			{
				return '';
			}

			$toJoin = Array();
			$keyPath = Array();

			foreach($data as $index => $currentEntry)
			{
				$subPieces = self::processEncodeStructure($keyPath, $index, $currentEntry);
				$toJoin = array_merge($toJoin, $subPieces);
			}

			return join("&", $toJoin);
		}

		private static function processEncodeStructure(Array $keyPath, $index, $currentEntry)
		{
			$subPieces = Array();
			$newIndex  = urlencode($index);

			if( is_array($currentEntry) )
			{
				$keyPath[] = $newIndex;
				foreach($currentEntry as $key => $value)
				{
					$newKey   = urlencode($key);

					if(true === is_array($value))
					{
						$subPieces = array_merge($subPieces, self::processEncodeStructure($keyPath, $key, $value));
					}
					else
					{
						$newValue = self::smartEncodeValue($value);
						$subPieces[] = $newKey . '[' . join('][', $keyPath) . ']=' . $newValue;
					}
				}
			}
			else
			{
				$subPieces[] = $newIndex . '=' . self::smartEncodeValue($currentEntry);
			}

			return $subPieces;
		}

		private static function smartEncodeValue($value)
		{
			if(is_bool($value))
			{
				return $value ? "true" : "false";
			}
			elseif(is_int($value))
			{
				return $value;
			}
			else
			{
				return urlencode('"' . $value . '"');
			}
		}

		private static function smartDecodeValue($value)
		{
			switch($value)
			{
				case "true":
					return true;
				case "false":
					return false;
				default:
					$newString = urldecode($value);
					if('"' == substr($newString, 0, 1))
					{
						$newString = substr($newString, 1);
					}

					if('"' == substr($newString, strlen($newString) - 1))
					{
						$newString = substr($newString, 0, strlen($newString) - 1);
					}

					return $newString;
			}
		}

		/**
		 * Parse a complex URL string and return it as an array.
		 *
		 * @param string $string
		 * @return array
		 */
		public static function decodeStructure($string)
		{
			// special case -- empty string. Return Array()
			if('' == $string)
			{
				return Array();
			}

			$toReturn = Array();
			if( null == $string )
			{
				return $toReturn;
			}

			$pieces = explode('&', $string);

			foreach($pieces as $piece)
			{
				$subPieces = explode('=', $piece, 2);

				// this is how we detect if it's a structure to decode:
				// there should be two values here
				if(2 != sizeof($subPieces))
				{
					return Array();
				}
				
				list($fullKey, $value) = $subPieces;

				$value = self::smartDecodeValue($value);

				list($key, $pathPieces) = self::parseFullKey($fullKey);
				self::setValue($toReturn, $key, $pathPieces, $value);
			}

			return $toReturn;
		}

		/**
		 * Parse a key[path][info] into Array(key, Array(path, info))
		 *
		 * @param string $fullKey
		 * @return Array (key, Array(PathInfo))
		 */
		private static function parseFullKey($fullKey)
		{
			$keyPos = strpos($fullKey, '[');
			if( $keyPos == null )
			{
				return Array(urldecode($fullKey), Array());
			}

			$keyName    = substr($fullKey, 0, $keyPos);
			$pathString = substr($fullKey, $keyPos);

			$pathString = str_replace("][", "\n", $pathString);
			$pathString = str_replace("]",  "",   $pathString);
			$pathString = str_replace("[",  "",   $pathString);
			$pathPieces = explode("\n", $pathString);

			foreach( $pathPieces as $i => $p )
			{
				$pathPieces[$i] = urldecode($p);
			}
			return Array($keyName, $pathPieces);
		}

		/**
		 * Find the correct node for a key/value and set the value.
		 */
		private static function setValue(Array &$toReturn, $key, Array $pathPieces, $value)
		{
			$lastPiece =& $toReturn;
			foreach($pathPieces as $piece)
			{
				if(false === array_key_exists($piece, $lastPiece))
				{
					$lastPiece[$piece] = Array();
				}

				$lastPiece =& $lastPiece[$piece];
			}

			$lastPiece[$key] = $value;
		}
	}
}
