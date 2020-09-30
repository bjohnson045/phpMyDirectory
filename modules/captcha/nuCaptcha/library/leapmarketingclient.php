<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

define("LEAP_LIBRARY_VERSION", 'Dev: $Change: 13738 $');

// check requirements
//// required for checks
require_once(dirname(__FILE__) . "/leaphelper.php");

// PHP_VERSION_ID is available as of PHP 5.2.7, if our 
// version is lower than that, then emulate it
if (!defined('PHP_VERSION_ID')) {
    $version = explode('.', PHP_VERSION);

    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if( PHP_VERSION_ID < 50100 ) {
    die('<div style="color:red;">The NuCaptcha API requires PHP version 5.1 or higher. You are currently running PHP version ' . PHP_VERSION. '</div>');
}

if( !extension_loaded('mcrypt') ) {
    die('<div style="color:red;">The NuCaptcha API requires the PHP <a href="http://www.php.net/manual/en/book.mcrypt.php">mcrypt</a> module.</div>');
}

if(false == defined('LM_DEBUG'))
{
    define('LM_DEBUG', false);
}

// on windows platforms prior to 5.3.0, make sure
// srand has been called.
if(false === lmcHelper::checkWindowsVersion(50300))
{
	srand();
}

define('LM_INVALID_CONNECTION_PERSISTENT_DATA', 'INVALID TOKEN CONNECTION');
define('LM_FAILURE_HTML', '<div id="nucaptcha-error-widget"><input type="hidden" name="lmsubmitted" value="1"/></div>');

// define some things here for the rest of the world
// In the non-client version, these are defined in leapglobal.config, which is loaded by leapcore.php
if (!defined('LM_DEFAULTCONFIG'))
{
	define('LM_DEFAULTCONFIG', '/etc/httpd/conf.d/leap/leap.config');
}

if (!defined('LM_TOKEN_NUMMESSAGES'))
{
    define('LM_TOKEN_NUMMESSAGES', 5);
}
if (!defined('LM_RANDOMFILE'))
{
    define('LM_RANDOMFILE', '/var/lib/leap/iv/random');
}
if(!defined('LM_FALLBACK_TOKEN_SERVER'))
{
	define('LM_FALLBACK_TOKEN_SERVER', 'http://token.alt.nucaptcha.com/' );
}
if(!defined('LM_FALLBACK_VALIDATE_SERVER'))
{
	define('LM_FALLBACK_VALIDATE_SERVER', 'http://validate.alt.nucaptcha.com/' );
}

/*
 * apps like wordpress require files to be included this way
 */
require_once(dirname(__FILE__) . "/leapstatuscodes.php");
require_once(dirname(__FILE__) . "/leapclientexception.php");
require_once(dirname(__FILE__) . "/leapperformance.php");
require_once(dirname(__FILE__) . "/leapglobalperformance.php");
require_once(dirname(__FILE__) . "/leapbase64.php");
require_once(dirname(__FILE__) . "/leaptextchunk.php");
require_once(dirname(__FILE__) . "/leapsymmetric.php");
require_once(dirname(__FILE__) . "/leaprpclite.php");
require_once(dirname(__FILE__) . "/leaptransactioninterface.php");
require_once(dirname(__FILE__) . "/leappublishertransaction.php");
require_once(dirname(__FILE__) . "/leaptransactionerror.php");
require_once(dirname(__FILE__) . "/leapconfigfile.php");
require_once(dirname(__FILE__) . "/leapresponse.php");
require_once(dirname(__FILE__) . "/leapvalidationinterface.php");
require_once(dirname(__FILE__) . "/leappublishervalidation.php");
require_once(dirname(__FILE__) . "/leapclusterpicker.php");
require_once(dirname(__FILE__) . "/leaperrorreporter.php");
require_once(dirname(__FILE__) . "/leapurlcoding.php");

function SetLeapErrorCode($code, $msg)
{
    Leap::SetErrorCode($code, $msg);
}

class Leap
{
	// Constants for template selection
	const TEMPLATE_DEFAULT        = 0;
	const TEMPLATE_FLASHONLY      = 1;
	const TEMPLATE_JAVASCRIPTONLY = 2;

	// Constants for leap player positioning
	const POSITION_LEFT   = 'left';
	const POSITION_RIGHT  = 'right';
	const POSITION_CENTER = 'center';
	
	// Constants for captcha purpose
		
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for account creation captchas.
	 *
	 * @var string
	 */
	const PURPOSE_CREATE_ACCOUNT = 'create_account';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for password reset captchas.
	 *
	 * @var string
	 */
	const PURPOSE_PASSWORD_RESET = 'password_reset';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for login form captchas.
	 *
	 * @var string
	 */
	const PURPOSE_LOGIN = 'login';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for captcha on posting new content (blog post creation, forum thread creation, wiki page creation etc)
	 *
	 * @var string
	 */
	const PURPOSE_POST = 'post';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for captcha on commenting on existing content (blog comments, new article comments, forum replies etc)
	 *
	 * @var string
	 */
	const PURPOSE_COMMENT = 'comment';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for captcha on editing existing content (editing existing posts, wiki editing etc)
	 *
	 * @var string
	 */
	const PURPOSE_EDIT = 'edit';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for captcha on voting (article votes, likes/dislikes, polls etc) 
	 *
	 * @var string
	 */
	const PURPOSE_VOTE = 'vote';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use for captchas on actions that will send users content (newsletter subscribe/unsubscribe, forum watch etc) 
	 *
	 * @var string
	 */
	const PURPOSE_SEND =  'send';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use when captcha is protecting a generic action to ensure the user is human (ticket purchase, limited action etc) 
	 *
	 * @var string
	 */
	const PURPOSE_ACTION = 'action';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Use when no other purpose constants apply.
	 *
	 * @var string
	 */
	const PURPOSE_GENERIC = 'generic';
	
	/**
	 * Purpose constant passed into Leap::InitializeTransaction.
	 * Default purpose used when not explicitly set.
	 *
	 * @var string
	 */
	const PURPOSE_UNKNOWN = 'unknown';

	const LANGUAGE_ENGLISH = "eng";
	const LANGUAGE_FRENCH  = "fre";
	const LANGUAGE_GERMAN  = "deu";
	const LANGUAGE_SPANISH  = "spa";
	const LANGUAGE_ITALIAN  = "ita";
	const LANGUAGE_RUSSIAN  = "rus";
	const LANGUAGE_CHINESE  = "zho";

	/**
	 * Message modes used when communicating with NuCaptcha servers.
	 *
	 * MMODE_COMPRESSED_ENCRYPTED is the default.
	 *
	 * @var string
	 */
	const MMODE_COMPRESSED_ENCRYPTED  = "compressed-encrypted";
	const MMODE_ENCRYPTED  = "encrypted";

	// IMPORTANT: If you update any PURPOSE_ constants you will also need to update the validation code in lmcPublisherTransaction ctor.
	
	/**
	 * The last error code (should be one of the leap error codes in leaperrorcodes.php)
	 * You can retrieve this with Leap::GetErrorCode()
	 *
	 * @var int
	 */
	static private $mLastErrorCode = LMSC_NOTRANSACTION;

	/**
	 * A message to go with the last error code, to provide more information
	 * You can retrieve this with Leap::GetErrorString()
	 *
	 * @var string
	 */
	static private $mLastErrorMessage = "InitializeTransaction Not Called.";

	/**
	 * Stores hints to be set for the next transaction
	 *
	 * @var Array
	 */
	static private $sHints = Array();

	/**
	 * Message mode used when communicating with NuCaptcha servers
	 * @var string
	 */
	static private $sMessageMode = self::MMODE_ENCRYPTED;

	/**
	 * @var lmcTextChunkData
	 */
	static private $sClientKey        = false;

	static private $sMasterTokenServer    = 'http://token.nucaptcha.com';
	static private $sClusterRecord    = "clusters.nucaptcha.com";
	static private $sForceTokenServer = false;
	static private $sValidateOnError = true;
	static private $sTestAction = '';

	/**
	 * @var lmcTransactionInterface
	 */
	static private $sLastTransaction = null;




	/** !EXPORT
	 * Parse a config file and set the values for this transactions.
	 *
	 * Ensure you store this configuration file in a location NOT ACCESSIBLE by web users. *
	 * The default config file location is stored in LM_DEFAULTCONFIG.  It's best to not *
	 * have your config inside your webroot.  If you must have it in your webroot, protect it *
	 * with an .htaccess file.
	 *
	 * You can retrive your config file from the Publisher Dashboard <http://console.nucaptcha.com> *
	 * and going to the download section.  Click the download link located at the top of the screen after you login.
	 *
	 * @param string $configFilePath - Path to the configuration file.  Download it at <http://console.nucaptcha.com>.
	 */
	public static function LoadConfigFile($configFilePath)
	{
		self::ResetConfig();

		// we want any errors here to bubble up.
		$localConfig = new lmcConfigFile($configFilePath);
		self::SetClientKey($localConfig->GetGlobal('clientKey'));

		// the rest are optional
		try
		{
			self::SetClusterRecord($localConfig->GetGlobal('clusterRecord'));
		}
		catch(Exception $e)
		{

		}

		try
		{
			self::SetForceTokenServer($localConfig->GetGlobal('forceTokenServer'));
		}
		catch(Exception $e)
		{

		}
	}

	/** !EXPORT
	 * Set a client key for the transaction.  Alternative to LoadConfigFile.
	 *
	 * This is the preferred method of storing your client key in PHP as it removes the need *
	 * to share or secure your config file.
	 *
	 * You can retrive your client key from the Publisher Dashboard <http://console.nucaptcha.com> *
	 * and going to the download section.  Click the download link located at the top of the screen after you login.
	 *
	 * @param string $clientKey - Your private key.  Get it at <http://console.nucaptcha.com>.
	 */
	public static function SetClientKey($clientKey)
	{
		try
		{
			self::$sClientKey = lmcTextChunk::Decode($clientKey, 'CLIENTKEY');
		}
		catch(Exception $e)
		{
			self::SetErrorCode(LMSC_CLIENTKEYNOTSET, 'Invalid client key: ' . $clientKey);
		}
	}

	/**
	 * Set the cluster record for the transaction. Used for testing.
	 * @param string clusterRecord
	 * @return
	 */
	public static function SetClusterRecord($clusterRecord)
	{
		self::$sClusterRecord = $clusterRecord;
	}

	/**
	 * Force a token server. Used for testing.
	 * @param forceTokenServer
	 * @return
	 */
	public static function SetForceTokenServer($forceTokenServer)
	{
		self::$sForceTokenServer = $forceTokenServer;
	}

	/**
	 * Get the current client key.
	 * @return lmcTextChunkData
	 */
	static public function GetClientKey()
	{
		return self::$sClientKey;
	}

	/**
	 * Get the current cluster record.
	 * @return string
	 */
	public static function GetClusterRecord()
	{
		return self::$sClusterRecord;
	}

	/**
	 * Get the master token server record
	 */
	public static function GetMasterTokenServer()
	{
		return self::$sMasterTokenServer;
	}

	/**
	 * Get the configuration setting for the forced token server.
	 * @return string
	 */
	public static function GetForceTokenServer()
	{
		return self::$sForceTokenServer;
	}

	/**
	 * Gets the message mode used when communicating with NuCaptcha's servers
	 */
	public static function GetMessageMode()
	{
		return self::$sMessageMode;
	}

	/**
	 * Sets the message mode used when communicating with NuCaptcha's servers
	 * @return string
	 */
	public static function SetMessageMode($mmode)
	{
		switch($mmode)
		{
			case self::MMODE_COMPRESSED_ENCRYPTED:
			case self::MMODE_ENCRYPTED:
				self::$sMessageMode = $mmode;
				break;
			default:
				throw new LeapException('Invalid message mode ' . $mmode, LMSC_INVALIDDATA . '.  See Leap::MMODE_* constants.');
		}
	}

	/** 
	 * Enables Test Mode. This allows sending the answer as 'correct' or 'wrong'. When the validation occurs,
	 * these values will be used to determine if the request is successful.
	 */
	public static function EnableTestMode()
	{
		self::$sHints['TEST_ANSWER'] = 1;
	}
	
	/**
	 * Turn off Test Mode.
	 */
	public static function DisableTestMode()
	{
		if(true === array_key_exists('TEST_ANSWER', self::$sHints))
		{
			unset(self::$sHints['TEST_ANSWER']);
		}
	}

	/**
	 * Specify how NuCaptcha validate should perform in the rare case of an error (ie connectivity issue with NuCaptcha servers, invalid client key etc)
	 * By default, validateOnError is set to true.  
	 * @param bool validateOnError specify if validate should succeed on an error.
	 */
	public static function SetValidateOnError($validateOnError)
	{
		self::$sValidateOnError = ($validateOnError ? true : false);
	}

	/**
	 * See SetValidateOnError
	 * @return bool
	 */
	public static function GetValidateOnError()
	{
		return self::$sValidateOnError;
	}

	/**
	 * Make sure a client key is set.
	 */
	private static function CheckForClientKey()
	{
		if (false == self::$sClientKey)
		{
			throw new LeapException('Client key has not yet been set.',	LMSC_CLIENTKEYNOTSET);
		}
	}

	/** !EXPORT
	 * This will get a token and create the lmcTransaction object.
	 *	 
	 * @param string $userData			- Any data you want associated with this token
	 * @param bool $useSSL				- If the client uses SSL, set this to true to avoid browser warnings
	 * @param string $campaignProfile           - Campaign ID to request from token server. The server may not respond with the campaign if it's not available or campaign selection is not available in your account.
	 * @param string $purpose			- one of Leap::PURPOSE_GENERIC Leap::PURPOSE_CREATE_ACCOUNT Leap::PURPOSE_PASSWORD_RESET Leap::PURPOSE_LOGIN Leap::PURPOSE_POST Leap::PURPOSE_COMMENT Leap::PURPOSE_EDIT Leap::PURPOSE_VOTE Leap::PURPOSE_SEND Leap::PURPOSE_ACTION
	 * @param string $preferredLanguage - one of the Leap::LANGUAGE_ constants, indicating which language to render the player in, and to pick captchas for
	 * @return lmcTransactionInterface 	- the transaction object created
	 */
	static public function InitializeTransaction($userData = null, $useSSL = false, $campaignProfile = null, $purpose = Leap::PURPOSE_UNKNOWN, $preferredLanguage=Leap::LANGUAGE_ENGLISH)
	{
		try
		{
			self::CheckForClientKey();

			$transactionObject = new lmcPublisherTransaction(
							"SetLeapErrorCode",
							LM_FAILURE_HTML,
							$useSSL,
							$campaignProfile,
							null,
							$purpose,
							$preferredLanguage
					);

			self::$sLastTransaction =  self::InitializeTransactionObject($userData, $transactionObject);
			return self::$sLastTransaction;
		}
		catch (Exception $e)
		{
			self::HandleException($e);
			return self::createTransactionError($e);
		}
	}

	static private function DecodePublicPersistentData($persistentData, $unique_id)
	{
		// If unique_id is false, then the data isn't encoded.  Return the passed in data
		if (false === $unique_id) return $persistentData;

		// Decode and import inot the chunk
		$clientkey = Leap::GetClientKey();
		$chunk = lmcTextChunk::Decode(lmcSymmetricMessage::DecipherMessage($clientkey->GetChunk('SKEY'), $persistentData), 'PDPUBLIC');

		if( $chunk->ChunkExists("EREPORT") )
		{
			Leap::SetReportingMode($chunk->GetChunk("EREPORT"));
		}

		// Retrieve the data
		return $chunk->GetChunk('PSDATA');
	}

	/** !EXPORT
	 * This will validate the transaction and return true/false if it was correct.
	 *
	 * This function is for those that don't want to deal with an object. Just call it and get a true/false answer.
	 *
	 * The error code will give more detail if not correct.  (leaperrorcodes.php)
	 *
	 * @param string $persistentData	- Data stored to recreate the lmcTransaction object
	 * @param string $response			- What the user responded with (false = figure it out ourself)
	 * @param string $unique_id			- ID that matches the one used in GetPersistentDataForPublicStorage() if that was used
	 * @return bool 					- true = success, false = failure.  Check the error code
	 */
	static public function ValidateTransaction($persistentData, $response = false, $unique_id = false)
	{
		try
		{
			self::CheckForClientKey();

			// If they used cookie storage, then decipher the persistentData
			$persistentData = self::DecodePublicPersistentData($persistentData, $unique_id);
			
			// Check for invalid persistent data
			if( $persistentData == null || !is_string($persistentData) )
			{
				throw new LeapException('Invalid persistent data', LMSC_INVALIDPERSISTENT);
			}

			if( $persistentData === '' )
			{
				throw new LeapException('Empty persistent data', LMSC_INVALIDPERSISTENT);
			}

			if( lmcTransactionError::isTransactionError($persistentData) )
			{
				// Check for validate on error (enabled by default)
				if( true === self::$sValidateOnError )
				{
					return true;
				}
				throw new LeapException('Invalid persistent data (bad token)', LMSC_INVALIDPERSISTENT);
			}

			$validator = self::Validate($persistentData, $response);

			$vc = $validator->GetValidationCode();
			switch($vc)
			{
				case LMSC_WRONG: // follow through
				case LMSC_EMPTY:
					return false;
				case LMSC_CORRECT:
					return true;
			}
		}
		catch (Exception $e)
		{
			lmcErrorReporter::SetErrorData('pdata', strval($persistentData));
			lmcErrorReporter::SetErrorData('unique_id', strval($unique_id));
			self::HandleException($e);
		}

		return false;
	}

	/** !EXPORT
	 * This will validate the transaction and return the validation object.
	 *
	 * The returned object can be used to get the validation code, or the response code, for more information.
	 *
	 * The error code will give more detail if not correct. (leaperrorcodes.php)
	 *
	 * @param string $persistentData	- Data stored to recreate the lmcTransaction object
	 * @param string $response			- What the user responded with (false = figure it out ourself)
	 * @param string $unique_id			- ID that matches the one used in GetPersistentDataForPublicStorage() if that was used
	 * @return lmcValidationInterface 	- object to query about the validation
	 */
	static public function Validate($persistentData, $response = false, $unique_id = false)
	{
		$validator = false;

		$persistentData = self::DecodePublicPersistentData($persistentData, $unique_id);

		if(false === $response || null === $response)
		{
		    $response = self::GetResponse($persistentData);
		}

		$validator = new lmcPublisherValidation();

		self::ValidateTransactionObject($persistentData, $response, $validator);

		return $validator;
	}

	/** !EXPORT
	 * Parses $_POST for the user's response to the captcha.
	 * A lmcResponse object is always returned, even if $_POST['lmanswer'] isn't set.
	 *
	 * You can check $response->GetAnswer() === false to see if it was found or not
	 *
	 * @param string $persistentData	- Data stored to recreate the transaction object
	 * @param boolean $fill				- true if you want the response object automatically filled with data from the $_POST variables
	 * @return lmcResponse
	 */
	static public function GetResponse($persistentData, $fill = true)
	{
		if (is_string($persistentData))
		{
			try
			{
				// create the new response
				$a = new lmcResponse($persistentData);

				// fill it in, if requested
				if($fill != false)
				{
					foreach($a->GetFields() AS $field)
					{
  						if(true === array_key_exists($field, $_POST))
						{
							$a->SetVar($field, $_POST[$field]);
						}
					}
				}

				return $a;
			}
			catch (Exception $e)
			{
				lmcErrorReporter::SetErrorData('pdata', strval($persistentData));
				self::HandleException($e);
			}
		}
		
		// create a dummy response and return it
		return new lmcResponse(null);
	}

	/**
	 * Sets the error code stored by the Leap class.
	 *
	 * Error codes are located in the file: leaperrorcodes.php
	 *
	 * @param int $code			- The LMSC_ error code to save
	 * @param string $message	- The message to save with it
	 * @return int				- The code
	 */
	static public function SetErrorCode($code, $message="")
	{
		self::$mLastErrorCode = $code;
		self::$mLastErrorMessage = $message;
		return $code;
	}

	// Static class.  Don't allow it to be constructed
	private function __construct()
	{
	}

	/**
	 * InitializeTransactionObject:
	 * This will get a token and create the lmcTransaction object.
	 * Takes the transaction object already created. This function will call the Initialize
	 * method on the transaction, and return the transaction. If there is an exception, or a
	 * connection problem, this function will return a dummy error transaction object.
	 *
	 * @param string $userData				- Any data you want associated with this token
	 * @param lmcTransactionInterface $t		- the transaction object to initialize and return
	 * @return lmcTransactionInterface 		- the transaction object created
	 */
	static protected function InitializeTransactionObject($userData, $t)
	{
		try
		{
			// Create the symmetric key
			/*
			 * LEAP-1378
			 *
			 * Double encode here because lmcSymmetricMessage() tries to decode
			 * it twice.
			 */
			$tokenkey	= lmcBase64::EncodeBinary(lmcSymmetricMessage::GenerateSymmetricKey());

			// Create the token request data
			$chunk = self::CreateTokenRequestData($userData, $tokenkey);

			// Default our error
			self::SetErrorCode(LMSC_OK);

			// Initialize the transaction
			$t->Initialize($chunk, $tokenkey);

			// default the error code
			self::SetErrorCode(LMSC_OK);

			// return the error code
			return $t;
		}
		catch (Exception $e)
		{
			self::HandleException($e);

			// handle this by using the error transaction type
			return self::createTransactionError($e);
		}
	}

	/**
	 * ValidateTransaction:
	 * This will validate the transaction. Use the $validator object to check whether or not it was true
	 * The error code will give more detail if not correct.
	 * This function takes an object derived from lmcValidationInterface, and calls it's
	 * ValidateTransaction method.
	 *
	 * @param string $persistentData			- Data stored to recreate the lmcTransaction object
	 * @param lmcResponse $response				- What the user responded with (false = figure it out ourself)
	 * @param lmcValidatorInterface $validator	- object that can validate
	 */
	static protected function ValidateTransactionObject($persistentData, lmcResponse $response, &$validator)
	{
		if (!is_string($persistentData))
		{
			self::SetErrorCode(LMSC_INVALIDPERSISTENT, "Persistent data must be a string!");
			return;
		}

		try
		{
			$response = self::GetValidResponse($persistentData, $response);
			if (null === $response)
			{
				// error code already set in GetValidResponse()
				return;
			}

			$validator->ValidateTransaction($persistentData, $response);
			$code = $validator->GetValidationCode();

			self::SetErrorCode($code);
		}
		catch (Exception $e)
		{
			self::HandleException($e);
			return;
		}
	}

	/**
	 * HandleException:
	 * This handles an exception, creating an appropriate message, and saving it
	 * and the error code using self::SetErrorCode
	 *
	 * @param Exception $e				- the exception to deal with
	 * @return bool 					- true = success, false = failure.  Check the error code
	 */
	static public function HandleException($e)
	{
		$msg = $e->getMessage();
		if (LM_DEBUG)
		{
			$msg .= "\nLeap Library Debug Details:\n";
			$msg .= $e->getFile().":(".$e->getLine().")";
			$msg .= $e->getTraceAsString();
		}

		$ec = LMSC_UNKNOWN;
		if( method_exists($e, 'getCode') )
		{
			$ec = $e->getCode();
		}

		if( $ec != LMSC_CLIENTKEYNOTSET &&
			$ec != LMSC_INVALIDCONFIGFILE &&
			$ec != LMSC_PUBLISHER_DISABLED )
		{
			lmcErrorReporter::ReportException($e, Array('leapErrorCode' => $ec));
		}
		self::SetErrorCode($ec, $msg);
	}

	/**
	 * CreateTokenRequestData:
	 * Creates and returns a token request chunk.
	 *
	 * @param string $userData			- Any data that the publisher wants to be sent to the Leap token server
	 * @param string $tokenkey			- the key that the token server should use to encrypt the token response with
	 * @return lmcTextChunk 				- the text chunk for the token request
	 */
	static protected function CreateTokenRequestData( $userData, $tokenkey )
	{
		// ***
		// *** Create the Token Request
		// ***
		$chunk = lmcHelper::CreateRequestChunk("TREQ");

		// Store the symmetric key
		$chunk->AddChunk("SKEY", $tokenkey);

		// check for hints
		$hints       = self::checkForHints($userData);
		$hintsString = null;
		
		if(true === is_array($hints))
		{
			// merge any global hints
			if(0 < sizeof(self::$sHints))
			{
				$hints = array_merge($hints, self::$sHints);	
			}
			
			// re-encode into urlcoding format
			$hintsString = lmcUrlCoding::encodeStructure($hints);
			
			if(true === array_key_exists('userData', $hints))
			{
				$userData = $hints['userData'];
			}
			else
			{
				$userData = false;
			}
		}
		elseif(sizeof(self::$sHints))
		{
			// no hints passed in, but we have global hints.
			$hintsString = lmcUrlCoding::encodeStructure(self::$sHints);
		}

		// Store the user id if it's a string.  Otherwise just skip past it.
		if ((false !== $userData) && is_string($userData))
		{
			$chunk->AddChunk("USERDATA", $userData);
		}
		
		if(null !== $hintsString)
		{
			$chunk->AddChunk('HINTS', $hintsString);
		}

		return $chunk;
	}
	
	/**
	 * Check to see if userData is really 'hints'
	 *
	 * @param string $userData
	 * @return array or false if userData is not a hint. Will also return false if json_decode is not available
	 */
	static private function checkForHints($userData)
	{
		// backwards compatible: check json first
		if(false === function_exists('json_decode'))
		{
			return false;
		}
		
		// Hints may be JSON or urlCoding. Check both.
		$hints = json_decode($userData, true);
		
		if(false === is_array($hints))
		{
			// now try urlcoding
			try
			{
				$hints = lmcUrlCoding::decodeStructure($userData);
				return $hints;
			}
			catch(Exception $e)
			{
				return false;
			}
		}
		
		return $hints;
	}

	/**
	 * GetValidResponse:
	 * This takes in persistent data and a response (which can be false or invalid) and returns
	 * a valid lmcResponse object or null. It first validates the passed in response object.
	 * If the input $response is invalid, null is returned to signal an error.
	 * If it's null, or false, a valid response object is created.
	 *
	 *
	 * @param string $persistentData	- Data stored to recreate the lmcTransaction object
	 * @param lmcResponse $response		- Response object, or null to create one.
	 * @return object 			- the response object; $response if valid, null if $response is invalid, or a new lmcResponse object
	 */
	static protected function GetValidResponse($persistentData, lmcResponse $response = null)
	{
	    // Do we want to figure out the response ourselves?
	    if ((false === $response) || is_null($response))
	    {
			$response = self::GetResponse($persistentData);
	    }

	    // if it's still false, no response
	    if(false === $response->GetAnswer())
	    {
			self::SetErrorCode(LMSC_EMPTY, "No answer from user");
			return null;
	    }

	    return $response;
	}

	/** 
	 * This will return the LMSC_ define for the last request.
	 *
	 * Error codes are located in the file: leaperrorcodes.php
	 *
	 * @return int - LMSC_ error code of the last error.
	 */
	static public function GetErrorCode()
	{
		self::ForceConnectionCompletion();
		return self::$mLastErrorCode;
	}

	/** 
	 * This will return any string data associated with the last requests error code.
	 *
	 * @return string - The error message of the last error.
	 */
	static public function GetErrorString()
	{
		self::ForceConnectionCompletion();
		return self::$mLastErrorMessage;
	}

	/** !EXPORT
	 * This will return the LMSC_ define for the last request.
	 *
	 * Status codes are located in the file: leapstatuscodes.php
	 *
	 * @return int - LMSC_ status code of the last request.
	 */
	static public function GetStatusCode()
	{
		return self::GetErrorCode();
	}

	/** !EXPORT
	 * This will return any string data associated with the last requests status code.
	 *
	 * @return string - The status message of the last request.
	 */
	static public function GetStatusString()
	{
		return self::GetErrorString();
	}
	
	/**
	 * Make sure we've finished our connection by calling $t->GetLinks()
	 */
	static private function ForceConnectionCompletion()
	{
		if(null !== self::$sLastTransaction)
		{
			self::$sLastTransaction->ForceConnectionCompletion();
		}
	}

    /** !EXPORT
     * Determine if the form was submitted or if this is the first time loading the NuCaptcha.
     *
     * @return bool - True if submitted, false otherwise.
     */
    static public function WasSubmitted()
    {
		if(true === array_key_exists(LM_FIELD_SUBMITTED, $_POST))
		{
			return true;
		}
		else
		{
			return false;
		}
    }

	/**
	 * resets config state before loading configs.
	 */
	private static function ResetConfig()
	{
		self::$sClientKey        = false;
		self::$sClusterRecord    = 'clusters.nucaptcha.com';
		self::$sForceTokenServer = false;
	}
	
	/** !EXPORT
     * Get the version number
     *
     * @return string
     */
    static public function GetVersion()
    {
    	return '1.0.13738';
    }

	private static function createTransactionError($e)
	{
		return new lmcTransactionError(LM_FAILURE_HTML, $e);
	}

	/**
	 * Sets the error reporting modes
	 * @param string $modes A CSV string of preferred reporting modes.  Uses the first supported method.
	 */
	public static function SetReportingMode($modes)
	{
		$supported = Array('NONE', 'HOPTOAD');
		$m = explode(',', $modes);
		$mode = lmcErrorReporter::$MODE_NONE;
		for($i = 0; $i < count($m); $i++ )
		{
			for($j = 0; $j < count($supported); $j++)
			{
				if( $supported[$j] == $m[$i] )
				{
					$mode = $m[$i];
					break;
				}
			}
		}
		lmcErrorReporter::SetReportMode($mode);
	}

	public static function GetReportingMode()
	{
		return lmcErrorReporter::GetReportMode();
	}

	/**
	 * For internal test use only
	 * @param string $testAction
	 */
	public static function SetTestAction($testAction)
	{
		self::$sTestAction = $testAction;
	}

	/**
	 * For internal test use only
	 * @param string $testAction
	 */
	public static function GetTestAction()
	{
		return self::$sTestAction;
	}

	/**
	 * Get the unique id for this transaction
	 * @param string unencrypted persistent data.  if using public persistent data you must call Leap::DecodePublicPersistentData first
	 */
	public static function GetTransactionID($persistentData)
	{
		try
		{
			$validator = new lmcPublisherValidation();
			return $validator->GetTransactionID($persistentData);
		}
		catch(Exception $e)
		{
		}
		return "";
	}
}

