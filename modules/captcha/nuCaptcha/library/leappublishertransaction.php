<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

/**
 * Default transaction interface.
 */
class lmcPublisherTransaction extends lmcTransactionInterface
{
	/**
	 * The javascript linsk to embed
	 *
	 * @var string
	 */
	private $mLinks = "";

	/**
	 * Extra fields to send onto the javascript
	 *
	 * @var bool
	 */
	private $mExtraJavaScriptFields = false;

	/**
	 * The HTML to return in GetWidget() if there was an error
	 *
	 * @var string
	 */
	private $mHTMLOnFailure = "";

	/**
	 * Should the transaction use SSL?
	 *
	 * @var bool
	 */
	private $mUseSSL = false;

	/**
	 * the template to display to the user (2 for gif, 3 for JavaScript, 4 for Flash)
	 * @var int
	 */
	private $mTemplateSelector = null;
	
	/**
	 * the captcha pupose for this transaction.  see Leap::PURPOSE_ constants.
	 * @var int
	 */
	private $mPurpose = null;

	/**
	 * The HTML sent back from the token server
	 *
	 * @var string
	 */
	private $mHTML = "";

	/**
	 * Campaign ID to request in the TREQ
	 * @var string
	*/
	private $mCampaignProfile = null;

	/**
	 * Resource Server URL
	 * @var string
	 */
	private $mResourceServer = null;
	
	/**
	 * CSS Skin name (not path)
	 * @var string
	 */
	private $mSkin = "default";

	/**
	 * Widget language
	 * @var string
	 */
	private $mDefaultLang = null;

	/**
	 * Decodes the token from the token response from the token server, and stores some data from it
	 *
	 * @param lmcTextChunkData $decoded
	 */
	protected function decodeTRESChunk(lmcTextChunkData $TRES)
	{
		$this->mResourceServer			= $TRES->GetChunk("RSERV");
		if (0 == strlen($this->mHTML))
		{
			$this->mHTML				= $TRES->GetChunk("HTML");
		}
		$this->mLinks					= $TRES->GetChunk("LINKS");
		$this->mExtraJavaScriptFields	= $TRES->GetChunk("JSVALUES2");
	}

	/**
	 * GetLinks:
	 * Gets a chunk of HTML code that has a list of <script type="text/javascript" src="myscript.js"></script> blocks
	 *
	 * @return string
	 */
	public function GetLinks()
	{
		$this->CheckSocketRead();

		// should be the resource server returned by the token server
		return $this->mLinks;
	}

	public function GetTransactionID()
	{
		$this->CheckSocketRead();
		
		return lmcHelper::GetIVFromToken($this->getToken());
	}

	/**
	 * GetJavascriptInternal:
	 * Gets the javascript code to run the leap stuff
	 * Should call it inline, or in the onLoad function for the document
	 * You should be able to insert the javascript code directly into a function.
	 * It will be a list of calls, like: callLeapFunction1(); callLeapFunction2();
	 * So if you're not going to embed it inside javascript of your own, you'll have to wrap it in a
	 * <script type="text/javascript"></script> block
	 *
	 * @param array $parameters - see the parent implementation of this function for more info
	 * @return string
	 */
	protected function GetJavascriptInternal($parameters)
	{
		if ($this->getStatusInfo() !== 0)
		{
			return "";
		}
		
		return $this->GenerateJavascriptFunctionCall($parameters, "lmLoadPlayer");
	}

	/**
	 * GetJavascriptParameters:
	 * Gets an array of javascript variables to reinitialize through ajax with.
	 * Called by the parent class to get the values.
	 *
	 * @return array
	 */
	protected function GetJavascriptParameters()
	{
		$this->CheckSocketRead();

		$dataRequest = $this->getDataServer();
		$token = $this->getToken();
		$resourceServer = $this->mResourceServer;
			
		$imagePath = $this->GetImagePath($resourceServer);

		$values = array();
		
		$values["dataRequest"] = $this->EscapeString($dataRequest);
		$values["token"] = $this->EscapeString($token);
		$values["resourceServer"] = $this->EscapeString($resourceServer);
		$values["w"] = 312;
		$values["h"] = 286;
		
		$values["validationFields"] = $this->CreateValidationFieldJSON();
		$values["cssSkin"] = $this->EscapeString($this->mSkin);
		
		// left over from non-css implementation
		// these can be removed when we force css
		{
			$values["skin"] = $this->EscapeString($this->GetImagePath($resourceServer));
			
			$values["webRequest"] = false;
			$values["imagePath"] = $this->EscapeString($imagePath);
			$values["showFlashVars"] = false;
			
			/*
			* LEAP-1221 - this is required until a new version of resources is up.
			* The old version still looks for skinJSON, so this is a bit of
			* backwards compatibility. Resources >= 51 should do it.
			*/
			$values["skinJSON"] = '[]';
		}

		// deal with the extra values now
		$values = $this->AppendExtraValuesFromTokenServer($values);

		// allow derived classes a shot next
		$values = $this->AppendJavascriptVariables($values);

		return $values;
	}

	/**
	 * AppendExtraValuesFromTokenServer:
	 * Appends values from the token server to the values being fed to the javascript
	 *
	 * @param array $values - the array to add values to
	 * @return array
	 */
	private function AppendExtraValuesFromTokenServer($values)
	{
		if (is_array($this->mExtraJavaScriptFields))
		{
			foreach ($this->mExtraJavaScriptFields as $k => $v)
			{
				if( is_numeric($v) )
				{
					$values[$k] = $v;
				}
				else if( is_bool($v) )
				{
					$values[$k] = $v ? "true" : "false";
				}
				else
				{
					$values[$k] = $this->EscapeString($v);
				}
			}
		}
		return $values;
	}

	/**
	 * CreateValidationFieldJSON:
	 * Takes the validation fields from the token response and creates a JSON array
	 * that the javascript can use to know what fields to send when/if the publisher writes
	 * some javascript code to get the validation field values (to do an ajax validation).
	 *
	 * @return string
	 */
	private function CreateValidationFieldJSON()
	{
		$list = $this->getResponseFieldData();
		
		$values = Array();

		foreach($list as $value)
		{
			$values[] = $this->EscapeString($value['varname']);
		}

		$fields = '[' . join(',', $values) . ']';

		return $fields;
	}

	/**
	 * Constructor:
	 * Saves the input variables for use later on in Initialize()
	 *
	 * @param $errorCallback			- a callback to call on error of some form. Should take an int and a message.
	 * @param $htmlOnFailure			- the HTML to display to the user on failure of some kind. This will be returned in GetWidget() on failure of some kind.
	 * @param bool $useSSL				- If the client uses SSL, set this to true to avoid browser warnings
	 * @param string $profile           - CampaignID/Profile to use.
	 * @param int $templateSelector     - the template to display to the user -- see TEMPLATE_* constants
	 * @param string $purpose         - a string providing information regarding the captcha usage for this transaction.  see Leap::PURPOSE_ constants.
	 * @param string $defaultLang       - widget language
	 */
	public function __construct($errorCallback, $htmlOnFailure, $useSSL = false, $campaignProfile = null, $templateSelector = null, $purpose = null, $defaultLang = Leap::LANGUAGE_ENGLISH)
	{
		// validate input
		switch ($templateSelector)
		{
			case null:
			case Leap::TEMPLATE_DEFAULT:
			case Leap::TEMPLATE_FLASHONLY:
			case Leap::TEMPLATE_JAVASCRIPTONLY:
				break;
			default:
				throw new LeapException("Invalid template selector " . $templateSelector, LMSC_INVALIDDATA);
		}
		
		switch ($purpose)
		{
			case Leap::PURPOSE_UNKNOWN;
			case Leap::PURPOSE_CREATE_ACCOUNT:
			case Leap::PURPOSE_PASSWORD_RESET:
			case Leap::PURPOSE_LOGIN:
			case Leap::PURPOSE_POST:
			case Leap::PURPOSE_COMMENT:
			case Leap::PURPOSE_EDIT:
			case Leap::PURPOSE_VOTE:
			case Leap::PURPOSE_SEND:
			case Leap::PURPOSE_ACTION:
			case Leap::PURPOSE_GENERIC:
				break;
			default:
				throw new LeapException("Invalid purpose $purpose: " . $purpose, LMSC_INVALIDDATA);
		}

		// save this for later on
		$this->mHTMLOnFailure           = $htmlOnFailure;
		$this->mUseSSL                  = $useSSL;
		$this->mCampaignProfile         = $campaignProfile;
		$this->mTemplateSelector        = $templateSelector;
		$this->mPurpose                 = $purpose;
		$this->mDefaultLang             = $defaultLang;

		$this->setErrorCallback($errorCallback);
	}

	/**
	 * @param lmcTextChunk $chunk - The text chunk, with data to initialize; see derived classes for more info
	 * @param string $tokenkey - The key to send with the token request to the server, that it can encrypt messages with
	 */
	public function Initialize(lmcTextChunk $treq, $tokenkey)
	{
		// save this for later
		$this->setSessionKey($tokenkey);

		$this->mAnswers = array();

		if(true === $this->mUseSSL)
		{
			$treq->AddChunk('USESSL', 1);
		}

		if (null != $this->mCampaignProfile)
		{
			$treq->AddChunk('CAID', $this->mCampaignProfile);
		}

		if (false != $this->mTemplateSelector)
		{
			$treq->AddChunk('TEMPLATE', $this->mTemplateSelector);
		}
		
		if (null != $this->mPurpose)
		{
			$treq->AddChunk('PURPOSE', $this->mPurpose);
		}

		if(null != $this->mDefaultLang)
		{
			$treq->AddChunk('DEFAULTLANG', $this->mDefaultLang);
		}
		
		if(defined('LM_PLATFORM'))
		{
			$treq->AddChunk('PLATFORM', LM_PLATFORM);
		}

		$this->sendTREQ($treq);
	}

	/**
	 * GetHTML:
	 * Gets the HTML code that needs to be embedded in a website.
	 *
	 * @param $position - How you want the player positioned.  ('left', 'center', 'right')
	 * @return string
	 */
	public function GetHTML($position = Leap::POSITION_LEFT)
	{
		$this->CheckSocketRead();

		if ($this->getStatusInfo() !== 0)
		{
			return $this->mHTMLOnFailure;
		}

		// left over from non css implementation
		// this can be removed once we force css
		{
			switch (strtolower($position))
			{
				default:
				case strtolower(Leap::POSITION_LEFT):
					return $this->mHTML;
				case strtolower(Leap::POSITION_CENTER):
					return str_replace('class="nucaptcha"', 'class="nucaptcha nucaptcha_center"', $this->mHTML);
				case strtolower(Leap::POSITION_RIGHT):
					return str_replace('class="nucaptcha"', 'class="nucaptcha nucaptcha_right"', $this->mHTML);
			}
		}
		return $this->mHTML;
	}

	/**
	 * Gets the ip address of the web user.
	 *
	 * @return string
	 */
	private function GetIpAddress()
	{
		// get the ip, for error tracking
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * EscapeString:
	 * Adds the quotes to a string
	 *
	 * @param string $str
	 * @return string
	 */
	protected function EscapeString($str)
	{
		return '"' . $str . '"';
	}

	/**
	 * EscapeString:
	 * Removes "'s from a string
	 *
	 * @param string $str
	 * @return string
	 */
	protected function UnescapeString($str)
	{
		if(0 === strpos($str, '"'))
		{
			$str = substr($str, 1);
		}

		if('"' == substr($str, strlen($str) - 1))
		{
			$str = substr($str, 0, strlen($str) - 1);
		}

		return $str;
	}

	/**
	 * GetJavascriptParametersInternal:
	 * Gets an array of javascript variables to initialize with.
	 *
	 * @param boolean $isFlashTransparent - true if you want to set the wmode of the Flash swf to transparent
	 * @param boolean $setFocusToAnswerBox - true if you want the text entry box on the NuCaptcha player to be focused once it's loaded
	 * @param string $position
	 * @param int $tabIndex
	 * @return array
	 */
	private function GetJavascriptParametersInternal($isFlashTransparent, $setFocusToAnswerBox, $position, $tabIndex=null)
	{
		$parameters = $this->GetJavascriptParameters();
		
		$parameters["isTransparent"] = $isFlashTransparent;
		$parameters["highlightAnswerBox"] = $setFocusToAnswerBox;
		$parameters["tabIndex"] = $tabIndex === null ? -1 : intval($tabIndex);
		$parameters["position"] = $this->EscapeString($position);
		$parameters["language"] = $this->EscapeString(strtoupper($this->mDefaultLang));

		// get the ip, for error tracking
		$parameters['ipAddress'] = $this->EscapeString($this->GetIpAddress());

		
		return $parameters;
	}

	/**
	 * Gets the javascript code to run the leap stuff.
	 *
	 * Should call it inline, or in the onLoad function for the document You should be able to insert the javascript code directly into a function.
	 * It will be a list of calls, like: callLeapFunction1(); callLeapFunction2();
	 *
	 * So if you're not going to embed it inside javascript of your own, you'll have to wrap it in a <script type="text/javascript"></script> block
	 *
	 * @param boolean $isFlashTransparent - true if you want to set the wmode of the Flash swf to transparent
	 * @param boolean $setFocusToAnswerBox - true if you want the text entry box on the NuCaptcha player to be focused once it's loaded
	 * @param string $position - One of Leap::POSITION_LEFT, Leap::POSITION_RIGHT or Leap::POSITION_CENTER. Default is LEFT.
	 * @return string
	 */
	public function GetJavascript($isFlashTransparent = false,
								  $setFocusToAnswerBox = false,
								  $position = Leap::POSITION_LEFT)
	{
		$parameters = $this->GetJavascriptParametersInternal($isFlashTransparent, $setFocusToAnswerBox, $position);

		return $this->GetJavascriptInternal($parameters);
	}



	/**
	 * Gets the javascript code to reinitialize the players with a new token.
	 *
	 * Used to do ajax submits, without having to do submit on a form.
	 * Returns a javascript block of code that can be eval'd.
	 *
	 * @param boolean $isFlashTransparent - true if you want to set the wmode of the Flash swf to transparent
	 * @param boolean $setFocusToAnswerBox - true if you want the text entry box on the NuCaptcha player to be focused once it's loaded
	 * @param string $position - One of Leap::POSITION_LEFT, Leap::POSITION_RIGHT or Leap::POSITION_CENTER. Default is LEFT.
	 * @return string
	 */
	public function GetJavascriptToReinitialize($isFlashTransparent = false,
												$setFocusToAnswerBox = false,
												$position = Leap::POSITION_LEFT)
	{
		$parameters = $this->GetJavascriptParametersInternal($isFlashTransparent, $setFocusToAnswerBox, $position);

		return $this->GetJavascriptToReinitializeInternal($parameters);
	}

	/**
	 * Derived classes should override this function instead of GetJavascriptToReinitialize.
	 *
	 * Gets the javascript code to reinitialize the players with a new token.
	 * Used to do ajax submits, without having to do submit on a form.
	 * Returns a javascript block of code that can be eval'd.
	 *
	 * @param array $parameters - see GetJavascriptToReinitializeInternal for a description of all of the name/value pairs that go into this array
	 * @return string
	 */
	public function GetJavascriptToReinitializeInternal($parameters)
	{
		return $this->GenerateJavascriptReinitializeCall($parameters);
	}

	/**
	 * Returns of the javascript function to call to reinitialize the leap player, using the json object returned from GetJSONToReinitialize().
	 *
	 * @return string
	 */
	public function GetJavascriptReinitializeFunctionName()
	{
		return "lmReinitializePlayer";
	}

	/**
	 * Gets the json object (as a string) to send (in javascript) to call the reinitialize function.
	 *
	 * To get the reinitialize function name, call GetJavascriptReinitializeFunctionName.
	 * Used to do ajax submits, without having to do submit on a form.
	 * Returns a javascript (json) encoded object.
	 *
	 * You can send in extra items to embed in the JSON object returned. The key's of the $extraParameters
	 * variable will be used as the names of the items in the JSON object, and the values will be the values.
	 * Note that you have to properly escape strings. So you'd have to do the following:
	 *
	 * $t->GetJSONToReinitialize(array("aString"=>"\"some string data\""));
	 *
	 * If you don't properly escape the string, then when the JSON object is used (or eval'd) in Javascript, it won't parse properly.
	 *
	 * @param array $extraParameters - hash table of extra parameters to put in the JSON
	 * @param boolean $isFlashTransparent - true if you want to set the wmode of the Flash swf to transparent
	 * @param boolean $setFocusToAnswerBox - true if you want the text entry box on the NuCaptcha player to be focused once it's loaded
	 * @param string $position - One of Leap::POSITION_LEFT, Leap::POSITION_RIGHT or Leap::POSITION_CENTER. Default is LEFT.
	 * @return string
	 */
	public function GetJSONToReinitialize($extraParameters = null,
										  $isFlashTransparent = false,
										  $setFocusToAnswerBox = false,
										  $position = Leap::POSITION_LEFT)
	{
		$parameters = $this->GetJavascriptParametersInternal($isFlashTransparent, $setFocusToAnswerBox, $position);

		if (($extraParameters != null) && (is_array($extraParameters)))
		{
			foreach ($extraParameters as $key => $value)
			{
				$parameters[$key] = $value;
			}
		}

		return $this->GenerateJSON($parameters);
	}

	/**
	 * Gets everything and can be embedded into the html page.
	 *
	 * Derived classes should override GetWidgetInternal, not this function. That way when new parameters are added, the derived classes won't have to be changed
	 *
	 * @param boolean $isFlashTransparent - true if you want to set the wmode of the Flash swf to transparent
	 * @param boolean $setFocusToAnswerBox - true if you want the text entry box on the NuCaptcha player to be focused once it's loaded
	 * @param string $position - One of Leap::POSITION_LEFT, Leap::POSITION_RIGHT or Leap::POSITION_CENTER. Default is LEFT.
	 * @param string $lang - deprecated, set language in InitializeTransaction instead.
	 * @param string $skin - CSS skin to use. Enterprise customers only.
	 * @return string
	 */
	public function GetWidget($isFlashTransparent = false,
							  $setFocusToAnswerBox = false,
							  $position = Leap::POSITION_LEFT,
							  $lang = Leap::LANGUAGE_ENGLISH,
							  $skin='default',
							  $tabIndex=null)
	{
		$this->mSkin = $skin;
		
		$parameters = $this->GetJavascriptParametersInternal(
				$isFlashTransparent,
				$setFocusToAnswerBox,
				$position,
				$tabIndex
		);

		$this->CheckSocketRead();

		$begin = '<!-- NuCaptcha Start - PHP ' . Leap::GetVersion() . ' -->';
		$end = '<!-- NuCaptcha End -->';
		if ($this->getStatusCode() !== false)
		{
			if( is_string($this->mHTMLOnFailure) )
			{
				return $begin . $this->mHTMLOnFailure . $end;
			}
			return $begin . $this->GetErrorHTML($this->getStatusCode()) . $end;
		}

		return $begin . $this->GetWidgetInternal($parameters, $skin) . $end;
	}

	/**
	 * GetWidgetInternal:
	 * Derived classes should override this if need be, as opposed to GetWidget.
	 *
	 * @param array $parameters - see GetWidget for a description of all of the name/value pairs that go into this array
	 * @return string
	 */
	protected function GetWidgetInternal($parameters)
	{	
		$widget = "";
		$widget .= $this->GetLinks() . "\n";
		$widget .= $this->GetHTML($this->UnescapeString($parameters["position"])) . "\n";
		$widget .= '<script type="text/javascript">' . "\n";
		$widget .= $this->GetJavascriptInternal($parameters) . "\n";
		$widget .= '</script>'  . "\n";

		return $widget;
	}

	/**
	 * GetErrorHTML:
	 * Given a status code, returns a string of HTML to display to the user
	 *
	 * @param int $statusCode - the error code to put in the email that the user will send to support
	 * @return string
	 */
	protected function GetErrorHTML($errorCode)
	{
		$linkText = 'An error occurred. Click here to report the problem. Please tell us what happened so we can improve our system.';
		$emailAddr = 'support@nucaptcha.com';
		$emailSubject = 'NuCaptcha Error: '. $errorCode;
		$emailBody = "Please provide brief description of the error that occured here.\n\n====== DETAILS ======\n";
		$emailDetails = Array();
		$emailDetails['IP Address'] = $this->GetIpAddress();
		$emailDetails['PublisherID'] = "Unknown";
		$key = Leap::GetClientKey();
		if(null != $key)
		{
			try
			{
				$emailDetails['PublisherID'] = $key->GetChunk('CID');
			}
			catch(Exception $e)
			{
			}
		}
		$emailDetails['ClientLib'] = 'PHP ' . Leap::GetVersion();
		$emailDetails['Timestamp'] = date('D, d M y H:i:s O'); // DATE_RFC822

		// Append details to body
		foreach( $emailDetails as $k => $v )
		{
			$emailBody .= $k . ':' . $v . "\n";
		}

		$html = '<div class="error" style="width:300px">';

		$html .= '<a href="mailto:'.$emailAddr.'?subject='.rawurlencode($emailSubject).'&body='.rawurlencode($emailBody).'">';
		$html .= $linkText;
		$html .= '</a>';
		$html .= '</div>';
		return $html;
	}

	/**
	 * GetImagePath:
	 * Gets the path to the image resources, on a resource server
	 *
	 * @param binary $resourcesServer - The url to retrieve from
	 * @return string - the full path to the image resources for our skin
	 */
	protected function GetImagePath($resourceServer)
	{
		return $resourceServer . "images/player/leap/";
	}

	/**
	 * GenerateJavascriptCall:
	 * Returns the javascript call, based on the parameters
	 *
	 * @param array $parameters		- the list of variables to pass as the data object in the javascript
	 * @return string				- the code to call the javascript
	 */
	protected function GenerateJavascriptCall($parameters)
	{
		return $this->GenerateJavascriptFunctionCall($parameters, "lmLoadPlayer");
	}

	/**
	 * GenerateJavascriptReinitializeCall:
	 * Returns the javascript call to reinitialize, based on the parameters
	 *
	 * @param array $parameters		- the list of variables to pass as the data object in the javascript
	 * @return string				- the code to call the javascript
	 */
	protected function GenerateJavascriptReinitializeCall($parameters)
	{
		return $this->GenerateJavascriptFunctionCall($parameters, $this->GetJavascriptReinitializeFunctionName());
	}

	/**
	 * GenerateJSON:
	 * Generates a JSON object, with each member mapping to the parameter array passed in
	 *
	 * @param array $parameters		- the list of variables to pass as the data object in the javascript
	 * @return string				- the JSON object
	 */
	protected function GenerateJSON($parameters)
	{
		$ret = "";
		$ret .= "{";

		$index = 0;
		foreach ($parameters as $name => $value)
		{
			if ($index > 0)
			{
				$ret .= ", ";
			}

			$ret .= "\"$name\":";
			if (is_int($value) || is_float($value))
			{
				$ret .= "$value";
			}
			else if (is_bool($value))
			{
				$ret .= ($value === true) ? "true" : "false";
			}
			else
			{
				$ret .= $value;
			}
			$index++;
		}

		$ret .= "}\n";

		return $ret;
	}

	/**
	 * GenerateJavascriptFunctionCall:
	 * Helper function to take a list of name/value pairs and create a javascript object (JSON) to
	 * send to the function call javascript method
	 *
	 * @param array $parameters - name/value pairs to insert in the JSON object
	 * @param string $functionCall - the name of the function to call
	 * @return string - the javascript to call from the inputs
	 */
	private function GenerateJavascriptFunctionCall($parameters, $functionCall)
	{
		$ret = "";
		$ret .= "\tvar data = ";
		$ret .= $this->GenerateJSON($parameters);
		$ret .= ";\n";
		$ret .= "\t$functionCall(data);\n";

		return $ret;
	}


	public function GetPersistentData()
	{
		$this->CheckSocketRead();

		if ( $this->transactionFailed() )
		{
			return lmcTransactionError::getTransactionErrorPersistentData($this->getStatusCode());
		}

		return parent::GetPersistentData();
	}

	/**
	 * AppendJavascriptVariables:
	 * Default null implementation of this method, which derived classes call
	 *
	 * @param array $values - name/value pairs to append to
	 * @return array - the values array
	 */
	protected function AppendJavascriptVariables($values)
	{
		return $values;
	}
}
