<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(!class_exists("LeapException", false))
{

class LeapException extends Exception
{
	private $mKey = "";
	private $mExtra = array();

	/**
	 * ExportException: (static)
	 * This will convert the exception data into an lmcTextChunk package.  Outside of DEBUG
	 * it will not send any useful data to someone who happens to get the message.
	 *
	 * @param string $message	- Error Message
	 * @param int $code			- Error Code (servers/libs/errorcodes.php)
	 * @param string $file		- Filename of where the exception occured
	 * @param int $line			- Line in the file where the exception occured
	 * @param string $trace		- Stacktrace
	 * @return string
	 */
	static public function ExportException($message, $code, $file, $line, $trace)
	{
		$tc = new lmcTextChunk("ERROR");
		$tc->AddChunk("ECODE", $code);
		$tc->AddChunk("ELINE", $line);
		$tc->AddChunk("EHFILE", md5($file));
		if (LM_DEBUG)
		{
			$tc->AddChunk("ERROR", $message);
			$tc->AddChunk("EFILE", $file);
			$tc->AddChunk("TRACE", $trace);
		}
		return $tc->Export();
	}

	/**
	 * ExportCryptoException: (static)
	 * This will convert the exception data into a partially encrypted lmcTextChunk package.
	 * It will only encipher plain text portions that may leak data.  If it is unable
	 * to use the crypto kep passed in it will default to the non-crypto version.
	 *
	 * @param string $message	- Error Message
	 * @param int $code			- Error Code (servers/libs/errorcodes.php)
	 * @param string $file		- Filename of where the exception occured
	 * @param int $line			- Line in the file where the exception occured
	 * @param string $trace		- Stacktrace
	 * @param binary  $skey 	- Crypto Key to use
	 * @return string
	 */
	static public function ExportCryptoException($message, $code, $file, $line, $trace, $skey)
	{
		// Validate the key.  If not valid just use a leap exception
		switch (strlen($skey))
		{
			case 16:
				break;
			case 22:		// If base64 encoded, decode it
			$skey = lmcBase64::DecodeString($skey);
			break;
			default:
				return LeapException::ExportException($message, $code, $file, $line, $trace);
		}

		$iv = lmcSymmetricMessage::GenerateIV();
		$tc = new lmcTextChunk("CERROR");
		$tc->AddChunk("ECODE", $code);
		$tc->AddChunk("ELINE", $line);
		$tc->AddChunk("EHFILE", md5($file));
		$tc->AddChunk("IV", $iv);
		$tc->AddChunk("CERROR", lmcSymmetricMessage::SymmetricEncipher($skey, $iv, $message, lmcHelper::messageModeToMessageMethod(Leap::GetMessageMode())));

		if (LM_DEBUG)
		{
			$tc->AddChunk("ERROR", $message);
			$tc->AddChunk("EFILE", $file);
			$tc->AddChunk("TRACE", $trace);
		}
		return $tc->Export();
	}

	/**
	 * Import: (static)
	 * This will convert a string from an exported exception into an lmcTextChunkData object
	 *
	 * @param string $exception
	 * @param binary $skey
	 * @return lmcTextChunkData
	 */
	static public function Import($exception, $skey="")
	{
		try
		{
			$chunk = lmcTextChunk::Decode($exception, "*");
			switch ($chunk->GetChunk("TYPE"))
			{
				// Some other type?  Not a valid exception.  Return null
				default:
					return null;

					// Normal unenciphered message
				case "ERROR":
					break;

					// Enciphered message, decipher what we can
				case "CERROR":
					{
						if (22 == strlen($skey))
						{
							$skey = lmcBase64::DecodeString($skey);
						}

						try
						{
							// Decipher the CERROR if possible and place it in the ERROR
							$chunk->AddChunk("ERROR", lmcSymmetricMessage::SymmetricDecipher($skey, $chunk->GetChunk("IV"), $chunk->GetChunk("CERROR"), true));
							$chunk->AddChunk("EFILE", $chunk->GetChunk("EHFILE")." - ".$chunk->GetChunk("EFILE"));
						}
						catch (Exception $e)
						{
							$chunk->AddChunk("ERROR", $chunk->GetChunk("CERROR", false));
							$chunk->AddChunk("EFILE", $chunk->GetChunk("EHFILE", false));
						}
					}
					break;
			}

			// Create the EMSG chunk
			$error 	= $chunk->GetChunk("ERROR", false);
			$line	= $chunk->GetChunk("ELINE", false);
			$file 	= $chunk->GetChunk("EFILE", false);

			$emsg	= "Error: \"$error\"\nLine: \"$line\"\nFile: \"$file\"\n";
			if ($chunk->ChunkExists("TRACE"))
			{
				$emsg .= "Stacktrace: \n".$chunk->GetChunk("TRACE", false)."\n";
			}

			$chunk->AddChunk("EMSG", $emsg);
			return $chunk;
		}
		// Problem opening the exception?  Just exit.
		catch (Exception $e)
		{
			return null;
		}
	}

	/**
	 * Export:
	 * Export the exception to an lmcTextChunk->Export()
	 *
	 * @return string
	 */
	public function Export()
	{
		return LeapException::ExportCryptoException($this->getMessage(), $this->getCode(), $this->GetFile(), $this->getLine(), $this->getTraceAsString(), $this->mKey);
	}

	/**
	 * LeapException constructor:
	 * This will construct the exception object.  If you have a crypto key that you
	 * can use, pass it as the third option.  This will enable us to send more
	 * information about the error back to the user.
	 *
	 * @param string $message		- Error message that occured
	 * @param int $code				- Error Code (servers/libs/errorcodes.php)
	 * @param binary $skey			- Binary Symmetric Key
	 * @param array $extra			- Extra error data for error reporting
	 */
	public function __construct($message, $code, $skey="", $extra=null)
	{
		parent::__construct($message, $code);
		$this->mKey = $skey;
		if( $extra != null )
		{
			$this->mExtra = $extra;
		}
	}

	/**
	 * GetExtra:
	 * Return extra error data
	 *
	 * @return array
	 */
	public function GetExtra()
	{
		return $this->mExtra;
	}
}

} // finish off the special if at the top to keep this class from being included twice (once for the client, once for the dev version)

?>