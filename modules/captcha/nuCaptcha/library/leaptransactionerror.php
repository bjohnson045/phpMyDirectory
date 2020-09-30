<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

class lmcTransactionError extends lmcTransactionInterface
{
    private $mFailureHTML = "";
	private $mLeapErrorCode = LMSC_UNKNOWN;

	/**
	 * Constructor:
	 * Stores the input for later on
	 *
	 * @param string $failureHTML		- the html to return in GetWidget()
	 * * @param Exception $e		- the exception
	 */
    public function __construct($failureHTML, $e)
    {
        $this->mFailureHTML = $failureHTML;
		$this->mLeapErrorCode = LMSC_UNKNOWN;
		if( method_exists($e, 'getCode') )
		{
			$this->mLeapErrorCode = $e->getCode();
		}
    }

	/**
	 * Initialize:
	 * Doesn't do anything for this particular class
	 */
	public function Initialize(lmcTextChunk $treq, $tokenkey)
	{
	}

	/**
	 * Not doing anything with sockets, so just make it an empty function
	 */
	protected function CheckSocketRead()
	{
	}

	/**
	 * GetPersistentData:
	 * Returns the error persistent data
	 *
	 * @return string
	 */
	public function GetPersistentData()
    {
        return self::getTransactionErrorPersistentData($this->mLeapErrorCode);
    }

	/**
	 * GetLinks:
	 *
	 * @return string
	 */
	public function GetLinks()
	{
		return "";
	}

	/**
	 * GetHTML:
	 * Returns the error HTML
	 *
	 * @return string
	 */
	public function GetHTML($position = "left")
	{
		return $this->mFailureHTML;
	}

	public function GetJavascript(
		$isFlashTransparent = false,
		$setFocusToAnswerBox = false,
		$position = Leap::POSITION_LEFT
	)
	{
		return "";
	}

	public function GetJavascriptToReinitialize(
			$isFlashTransparent = false,
			$setFocusToAnswerBox = false,
			$position = Leap::POSITION_LEFT
	)
	{
		return "";
	}

	public function GetJSONToReinitialize(
			$extraParameters = null,
			$isFlashTransparent = false,
			$setFocusToAnswerBox = false,
			$position = Leap::POSITION_LEFT
	)
	{
		return "";
	}

	public function GetWidget(
			$isFlashTransparent = false,
			$setFocusToAnswerBox = false,
			$position = Leap::POSITION_LEFT,
			$lang = Leap::LANGUAGE_ENGLISH,
			$skin = 'default',
			$tabIndex = null
	)
	{
		return $this->GetHTML($position);
	}

	public function GetJavascriptReinitializeFunctionName()
	{
		return "";
	}

	public function GetTransactionID()
	{
		return "";
	}

	protected function decodeTRESChunk(lmcTextChunkData $TRES)
	{

	}

	static public function getTransactionErrorPersistentData($errorCode)
	{
		$chunk = new lmcTextChunk('PDINVALID');
		$chunk->AddChunk('TIME', time());
		$chunk->AddChunk('PUID', lmcHelper::GenerateWebUserID());
		$chunk->AddChunk('PSDATA', LM_INVALID_CONNECTION_PERSISTENT_DATA);
		$chunk->AddChunk('ECODE', intval($errorCode));
		$chunk->AddChunk('EREPORT', Leap::GetReportingMode());
		return $chunk->Export();
	}

	static public function isTransactionError($pdata, $errorCode = null)
	{
		try
		{
			$chunk = lmcTextChunk::Decode($pdata, 'PDINVALID');

			if( $chunk->ChunkExists('EREPORT') )
			{
				Leap::SetReportingMode($chunk->GetChunk('EREPORT'));
			}

			// Does the ID match?
			$puid = lmcHelper::GenerateWebUserID();
			if ($puid  != $chunk->GetChunk('PUID'))
			{
				throw new LeapException('id does not match.', LMSC_INVALIDPERSISTENT, '',
						array('puid.current' => $puid, 'puid.prev' => $chunk->GetChunk('PUID')));
			}

			// Is it stale?
			$time_diff = (time() - $chunk->GetChunk('TIME'));
			if ($time_diff > (60 * 10)) // short lifetime
			{
				throw new LeapException('Persistent data is stale.', LMSC_INVALIDPERSISTENT, '', array('time_diff' => $time_diff));
			}

			if( $chunk->GetChunk('PSDATA') !== LM_INVALID_CONNECTION_PERSISTENT_DATA )
			{
				throw new LeapException('PersistentData is not invalid', LMSC_INVALIDPERSISTENT, '', array('psdata' => $chunk->GetChunk('PSDATA')));
			}

			if( null !== $errorCode )
			{
				if( intval($errorCode) != $chunk->GetChunk('ECODE') )
				{
					throw new LeapException('PersisentData contains wrong error code', LMSC_INVALIDPERSISTENT);
				}
			}
		}
		catch(LeapException $e)
		{
			return false;
		}

		return true;
	}
}
