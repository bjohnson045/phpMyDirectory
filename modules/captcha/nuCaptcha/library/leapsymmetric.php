<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(class_exists("lmcSymmetricMessage", false))
{
	return;
}

/**
 * class lmcSymmetricMessage
 * This class is intended to create a secure transfer protocol that
 * is validated by a symmetric key.  Validity is assumed by knowledge
 * of the enciphering key.
 *
 * This class is intended to be a singleton.  The constructor/destructor
 * is used to ensure that resources are cleaned up properly.
 *
 * Security Features:
 * - It needs to have a timestamp internal to the data package
 * - It should pass a new symmetric key used for further communication
 */
class lmcSymmetricMessage
{
	static private $m_METHODID	= 0;	// Method is for future .. default is 0
	static private $m_type		= 1;
	static private $m_PIPE		= '.';
	static private $m_magic		= "LEAPSM";				// Some magic data to prepend to the encrypted data to ensure validity

	/**
	 * Length of a valid base64 Encoded IV
	 *
	 * @var int
	 */
	static private $sIvLength = 24;

	static private $e_VERSION		= 0;
	static private $e_SENDERID		= 1;
	static private $e_KEYID			= 2;
	static private $e_METHOD		= 3;
	static private $e_IV			= 4;
	static private $e_DATA			= 5;

	/*
	 * Leap custom padding scheme. Similar to pkcs7 but padding is mid-message (after the magic).
	 * This is the default.
	 */
	const PADDING_LEAP  = 'leap';

	/* From PKCS #7: http://tools.ietf.org/html/rfc2315
	    2.   Some content-encryption algorithms assume the
             input length is a multiple of k octets, where k > 1, and
             let the application define a method for handling inputs
             whose lengths are not a multiple of k octets. For such
             algorithms, the method shall be to pad the input at the
             trailing end with k - (l mod k) octets all having value k -
             (l mod k), where l is the length of the input. In other
             words, the input is padded at the trailing end with one of
             the following strings:

                      01 -- if l mod k = k-1
                     02 02 -- if l mod k = k-2
                                 .
                                 .
                                 .
                   k k ... k k -- if l mod k = 0

             The padding can be removed unambiguously since all input is
             padded and no padding string is a suffix of another. This
             padding method is well-defined if and only if k < 256;
             methods for larger k are an open issue for further study.
	 *
	 * rlukashuk:
	 * Some encyption implementations require the padding to exist even if the original message is block aligned.
	 * Use this to force a padding at the end of message.
	 *
	 * Note: this is the same as pkcs5 but it is not restricted to a block size of 8
	 */
	const PADDING_PKCS7 = 'pkcs7';

	/*
	 * The default encryption method, used by most clientlibs
	 */
	const METHOD_AES128_CBC_LEAP = 0;

	/*
	 * Same as METHOD_AES128_CBC_LEAP but payload is run through gzdefalte before padding
	 */
	const METHOD_AES128_CBC_LEAP_GZ = 2;

	/*
	 * Use PKCS7 padding instead of LEAP padding
	 */
	const METHOD_AES128_CBC_PKCS7 = 1;

	// add other methods here ...


	/**
	 * PadPCKS7:
	 * See http://tools.ietf.org/html/rfc2315
	 *
	 * @param string $text
	 * @param int $blocksize
	 * @return string The input string padded out.  The output string is always larger than the input string
	 */
	static private function PadPKCS7 ($text, $blocksize)
	{
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}

	/**
	 * PadPCKS7:
	 * See http://tools.ietf.org/html/rfc2315
	 *
	 * @param string $text
	 * @return string The input with the padding removed or false on error.  The output string is always smaller than the input string.
	 */
	static private function UnpadPKCS7($text)
	{
		$pad = ord($text{strlen($text)-1});
		if ($pad > strlen($text)) return false;
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return false;
		return substr($text, 0, -1 * $pad);
	}

	/**
	 * GetMethodDetails:
	 * Get specifics on the method mode
	 *
	 * @param int $method see method constants
	 * @param string $cipher out param
	 * @param string $mode out param
	 * @param string $padding out param
	 */
	static private function GetMethodDetails($method, &$cipher, &$mode, &$padding, &$compress)
	{
		switch($method)
		{
			case self::METHOD_AES128_CBC_LEAP:
				$cipher = MCRYPT_RIJNDAEL_128;
				$mode  = MCRYPT_MODE_CBC;
				$padding = self::PADDING_LEAP;
				$compress = false;
				break;

			case self::METHOD_AES128_CBC_LEAP_GZ:
				$cipher = MCRYPT_RIJNDAEL_128;
				$mode  = MCRYPT_MODE_CBC;
				$padding = self::PADDING_LEAP;
				$compress = true;
				break;

			case self::METHOD_AES128_CBC_PKCS7:
				$cipher = MCRYPT_RIJNDAEL_128;
				$mode  = MCRYPT_MODE_CBC;
				$padding = self::PADDING_PKCS7;
				$compress = false;
				break;

			default:
				throw new LeapException('Invalid method ' . $method, LMSC_INVALIDDATA);
		}
	}

	/**
	 * SymmetricEncipher:
	 * This will encipher a message and return it (as binary)
	 *
	 * @param binary $key
	 * @param binary $iv
	 * @param binary $message
	 * @param int $method See method constants
	 * @return binary
	 */
	static public function SymmetricEncipher($key, $iv, &$message, $method=self::METHOD_AES128_CBC_LEAP)
	{
		self::GetMethodDetails($method, $cipher, $mode, $padding, $compress);

		if( $compress )
		{
			$message = gzcompress($message, 5);
		}

		if( $padding == self::PADDING_LEAP )
		{
			// Figure out how much padding is required.  If full key size, don't pad.
			$keysize = (lmcSymmetricMessage::GetKeySizeInBits($method) / 8);
			$carry   = ((strlen(lmcSymmetricMessage::$m_magic) + 1 + strlen($message)) % $keysize);
			$padsize = ($keysize - $carry);		// Will be -1 .. Must +1 on decode

			// If no padding required
			if (0 == $carry)
			{
				// No extra padding required
				$msg = lmcSymmetricMessage::$m_magic."0".$message;
			}
			else
			{
				// Add the padding
				$msg = lmcSymmetricMessage::$m_magic.str_repeat(dechex($padsize), $padsize+1).$message;
			}

			// Encipher the data
			$enc = mcrypt_encrypt($cipher, $key, $msg, $mode, $iv);
			return $enc;
		}
		else if( $padding == self::PADDING_PKCS7 )
		{
			$msg = self::PadPKCS7(lmcSymmetricMessage::$m_magic."0".$message, lmcSymmetricMessage::GetKeySizeInBytes($method));
			$enc = mcrypt_encrypt($cipher, $key, $msg, $mode, $iv);
			return $enc;
		}

		throw new LeapException('Invalid padding method ' . $padding, LMSC_INVALIDDATA);
	}

	/**
	 * SymmetricDecipher:
	 * This will decipher a message and return it
	 *
	 * @param binary $key
	 * @param binary $iv
	 * @param binary $encmessage - the message
	 * @param bool $throw - whether or not to throw an exception on an error
	 * @param int $method - See method constants
	 * @return binary/bool - the deciphered message, or false if an error
	 */
	static public function SymmetricDecipher($key, $iv, &$encmessage, $throw=true, $method=self::METHOD_AES128_CBC_LEAP)
	{
		self::GetMethodDetails($method, $cipher, $mode, $padding, $compress);

		// Decipher the message
		$dec =  mcrypt_decrypt(	$cipher, $key, $encmessage, $mode, $iv);

		// Check to see that our magic is on the front
		$magic = substr($dec, 0, strlen(lmcSymmetricMessage::$m_magic));
		if (0 != strcmp($magic, lmcSymmetricMessage::$m_magic))
		{
			if (true === $throw)
			{
				throw new LeapException(
					sprintf(
						"Invalid key - could not decipher. Magic (%s) does not match expected (%s)",
						$magic,
						lmcSymmetricMessage::$m_magic
					),
					LMSC_INVALIDKEY
				);
			}
			else return false;
		}

		if( $padding == self::PADDING_PKCS7 )
		{
			// remove additional pkcs7 padding
			$dec =  self::UnpadPKCS7($dec);
			if( false === $dec )
			{
				throw new LeapException('Error unpadding PKCS7', LMSC_INVALIDDATA);
			}
		}
		else if( $padding != self::PADDING_LEAP )
		{
			throw new LeapException('Invalid padding method ' . $padding, LMSC_INVALIDDATA);
		}

		// Get our padding size
		$pad = hexdec(substr($dec, strlen(lmcSymmetricMessage::$m_magic), 1))+1;

		// Return the decoded data
		$res =  substr($dec, strlen(lmcSymmetricMessage::$m_magic)+$pad);

		if( $compress )
		{
			$res = gzuncompress($res);
		}
		return $res;
	}

	/**
	 * This will generate an initialization vector.  This can be passed
	 * in the open with no loss to security.
	 *
	 * @return string (binary)
	 */
	static public function GenerateIV($method=self::METHOD_AES128_CBC_LEAP)
	{
		self::GetMethodDetails($method, $cipher, $mode, $padding, $compress);
		return self::MCryptRandom(mcrypt_get_iv_size($cipher, $mode));
	}

	/**
	 * Use mcrypte_create_iv to generate random data. Uses MCRYPT_DEV_URANDOM
	 * as a source
	 *
	 * @param int $size Size in bytes of random data to generate
	 * @return string
	 */
	static private function MCryptRandom($size)
	{
		lmcGlobalPerformance::EnterSection('GenerateMcryptIV');

		// From http://ca2.php.net/manual/en/function.mcrypt-create-iv.php
		// Prior to 5.3.0, MCRYPT_RAND was the only one supported on Windows.
		if(false === lmcHelper::checkWindowsVersion(50300))
		{
			$iv = mcrypt_create_iv($size, MCRYPT_RAND);
		}
		else
		{
			$iv = mcrypt_create_iv($size, MCRYPT_DEV_URANDOM);
		}
		lmcGlobalPerformance::LeaveSection('GenerateMcryptIV');

		return $iv;
	}

	/**
	 * GenerateSymmetricKey:
	 * This will generate a random symmetric key.
	 *
	 * @return string (binary) - generated key
	 */
	static public function GenerateSymmetricKey($method=self::METHOD_AES128_CBC_LEAP)
	{
		return self::MCryptRandom(self::GetKeySizeInBytes($method));
	}

	/**
	 * GetKeySizeInBits:
	 * This will do as the name says.  Return the key size in bits.
	 *
	 * @return int
	 */
	static public function GetKeySizeInBits($method=self::METHOD_AES128_CBC_LEAP)
	{
		// Note: all methods are currently 128-bit
		return (self::GetKeySizeInBytes($method) * 8);	// See comments above - GenerateSymmetricKey
	}

	/**
	 * Report the default key size, in bytes.
	 *
	 * @return int
	 */
	static public function GetKeySizeInBytes($method=self::METHOD_AES128_CBC_LEAP)
	{
		// Note: all methods are currently 128-bit
	    return 16;
	}

	/**
	 * EncipherMessage:
	 * This will encipher a message with a symmetric cipher defined by
	 * the $method parameter.  It will encode the output into a message structure.
	 *
	 * @param string $base64key - the key, encoded as base64
	 * @param string $message - the message to encode.
	 * @param int $senderid - the id of the sender (encoded in the message structure)
	 * @param int $keyid - the id of the key that was used
	 * @return string - SymmetricKeyStructure(v1)
	 */
	static public function EncipherMessage($base64key, &$message, $senderid, $keyid, $method = self::METHOD_AES128_CBC_LEAP)
	{
		// Grab the pipe character
		$pipe = lmcSymmetricMessage::$m_PIPE;

		// Generate the IV
		$iv = lmcSymmetricMessage::GenerateIV($method);

		// Encipher the message and return the package
		$encoded = lmcSymmetricMessage::$m_type . $pipe.
				$senderid . $pipe.
				$keyid . $pipe.
				$method . $pipe.
				lmcBase64::EncodeBinary($iv) . $pipe.
				lmcBase64::EncodeBinary(lmcSymmetricMessage::SymmetricEncipher(lmcBase64::DecodeBinary($base64key), $iv, $message, $method));

		return $encoded;
	}

	/**
	 * DecipherMessage:
	 * This will decipher a given message with a given key.  It will ensure that
	 * the senderid is correct before it even tries.
	 *
	 * @param string $base64key - the key to use to decipher
	 * @param string $encmessage - message structure output from EncipherMessage
	 * @param int $method - optional out param.  set if not null.
	 * @return string - deciphered message
	 */
	static public function DecipherMessage($base64key, &$encmessage, &$method=self::METHOD_AES128_CBC_LEAP)
	{
		// Explode the message into its parts
		$msg = self::SplitEncMessage($encmessage);

		self::VerifyVersion($msg);

		// Ensure the IV is the correct size
		if (self::$sIvLength != strlen($msg[lmcSymmetricMessage::$e_IV]))
		{
			throw new LeapException("Invalid encoded IV Length: ".strlen($msg[lmcSymmetricMessage::$e_IV]), LMSC_INVALIDIVLENGTH);
		}

		$methodid = intval($msg[lmcSymmetricMessage::$e_METHOD]);

		$res = lmcSymmetricMessage::SymmetricDecipher(
					lmcBase64::DecodeBinary($base64key),
					lmcBase64::DecodeBinary($msg[lmcSymmetricMessage::$e_IV]),
					lmcBase64::DecodeBinary($msg[lmcSymmetricMessage::$e_DATA]),
					true,
					$methodid
				);

		// set out param and return
		$method = $methodid;

		return $res;
	}

	/**
	 * Split an encrypted message into its elements
	 *
	 * @param string $encmessage
	 * @return array
	 */
	static private function SplitEncMessage($encmessage)
	{
	    return explode(self::$m_PIPE, $encmessage);
	}

	/**
	 * Make sure the version matches in the encrypted message. This is a good
	 * way to validate if the message was exploded properly.
	 *
	 * Throws a LeapException if the version doesn't match.
	 *
	 * @param array $msg exploded version of the encrypted token
	 */
	static private function VerifyVersion(Array $msg)
	{
	    // Check to see if it's the correct type
	    if (self::$m_type != $msg[self::$e_VERSION])
	    {
			$error = sprintf("DecipherMessage unexpected version number: '%s' -- expected %s Full Token: '%s'",
				$msg[self::$e_VERSION],
				self::$m_type,
				join(self::$m_PIPE, $msg)
			);

			throw new LeapException($error, LMSC_INVALIDVERSION);
	    }
	}

	/**
	 * GetSenderID:
	 * This will return the SenderID encoded inside the message structure
	 *
	 * @param string $encmessage - the encoded message structure
	 * @return int - int SenderID
	 */
	static public function GetSenderID(&$encmessage)
	{
	    // Explode the message into its parts.  We don't really need the whole
	    // message, just the first 2 elements.  Those are guaranteed to be in
	    // the first 16 bytes
	    $msg = self::SplitEncMessage(substr($encmessage, 0, 16));

		// verify that we got at least 2 elements
		if(sizeof($msg) <= 2)
		{
			$error = sprintf(
				"Encrypted message did not break into enough pieces (only %s). Token: '%s'",
				sizeof($msg),
				$encmessage
			);
			throw new LeapException($error, LMSC_INVALIDVERSION);
		}

	    self::VerifyVersion($msg);

	    // return the sender ID
	    return $msg[lmcSymmetricMessage::$e_SENDERID];
	}

	/**
	 * GetKeyID:
	 * This will return the KeyID encoded inside the message structure
	 *
	 * @param string $encmessage - the encoded message structure
	 * @return int - int KeyID
	 */
	static public function GetKeyID(&$encmessage)
	{
	    // Explode the message into its parts.  We don't really need the whole
	    // message, just the first 3 elements.  Those are guaranteed to be in
	    // the first 32 bytes
	    $msg = self::SplitEncMessage(substr($encmessage, 0, 32));

	    self::VerifyVersion($msg);

	    // return the sender ID
	    return $msg[lmcSymmetricMessage::$e_KEYID];
	}

	/**
	 * Returns the method id of the symmetric message
	 *
	 * @param string $encmessage
	 * @return int
	 */
	static public function GetMethod($encmessage)
	{
	    $pieces = self::SplitEncMessage($encmessage);

	    self::VerifyVersion($pieces);

	    return intval($pieces[self::$e_METHOD]);
	}

	/**
	 * Returns the IV of the symmetric message
	 *
	 * @param string $encmessage
	 * @return string
	 */
	static public function GetIv($encmessage)
	{
	    // for asymmetric keys, the IV is the 6th element
	    $pieces = self::SplitEncMessage($encmessage);

	    self::VerifyVersion($pieces);

	    return $pieces[self::$e_IV];
	}

	/**
	 * IsInvalid:
	 * This will determine whether a message is definitely INVALID.  This does not
	 * mean the message is valid, it just looks for markers that indicate that it's
	 * definitely not valid
	 *
	 * We should be able to quickly test the message form for correctness, given:
	 * version(int)
	 * senderid(int)
	 * keyid(int)
	 * method(int)
	 * iv(string(24))
	 * message(binary encoded string)
	 *
	 * @param string $encmessage
	 * @return bool
	 */
	static public function IsInvalid(&$encmessage)
	{
		try
		{
			self::IsInvalidException($encmessage);
			return false;
		}
		catch(LeapException $e)
		{
			return true;
		}
	}

	/**
	 * Same as IsInvalid, but throws an exception instead.
	 *
	 * @param string $encmessage
	 */
	static public function IsInvalidException(&$encmessage)
	{
		if(strlen($encmessage) == 0)
		{
			throw new LeapException("encmessage is empty", LMSC_INVALIDDATA);
		}

		// Explode the message
		$msg = self::SplitEncMessage($encmessage);

		// VERSION element must be an int or numeric
		if ( true !== ctype_digit($msg[lmcSymmetricMessage::$e_VERSION]) )
		{
		    throw new LeapException('VERSION is not numeric (' . $msg[lmcSymmetricMessage::$e_VERSION] . ')', LMSC_INVALIDDATA);
		}

		// If the version isn't proper
		if (lmcSymmetricMessage::$m_type != $msg[lmcSymmetricMessage::$e_VERSION])
		{
		    throw new LeapException('type does not match', LMSC_INVALIDDATA);
		}

		// SENDERID element must be an int or numeric
		if ( true !== ctype_digit($msg[lmcSymmetricMessage::$e_SENDERID]) )
		{
		    throw new LeapException('SENDERID is not numeric', LMSC_INVALIDDATA);
		}

		// KEYID element must be an int or numeric
		if ( true !== ctype_digit($msg[lmcSymmetricMessage::$e_KEYID]) )
		{
		    throw new LeapException('KEYID is not numeric', LMSC_INVALIDDATA);
		}

		// METHOD element must be an int or numeric
		if ( true !== ctype_digit($msg[lmcSymmetricMessage::$e_METHOD]) )
		{
		    throw new LeapException('METHOD is not numeric', LMSC_INVALIDDATA);
		}

		if (self::$sIvLength != strlen($msg[lmcSymmetricMessage::$e_IV]))
		{
		    throw new LeapException('length of IV is not 24 (' . strlen($msg[lmcSymmetricMessage::$e_IV]) . ')', LMSC_INVALIDDATA);
		}

		// DATA/MESSAGE must be base64
		// FIXME this should really be a validator method inside lmcBase64
		$pattern = "/[^-,_A-Za-z0-9]/";
		if ( preg_match($pattern, $msg[lmcSymmetricMessage::$e_DATA]) )
		{
			throw new LeapException("Payload is not base64 encoded", LMSC_INVALIDDATA);
		}

		// Everything seems ok
		return false;
	}
}
