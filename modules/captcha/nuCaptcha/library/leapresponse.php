<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */


// Constants for the fields
// TODO: Provide this in the TRES
define('LM_FIELD_SUBMITTED', 'lmsubmitted');

define('LM_CHUNK_TYPE_NULL', 'NULL');

/**
 * Intermediate storage for user response.
 */
class lmcResponse
{
	const PIPE = "&";
	const VARIABLE_END = "?";
	const VARIABLE_LENGTH_MARKER = '#';
	
    /**
     * Store the variables for the response
     *
     * @var array
     */
    private $mVariables = Array();
    
    /**
     * Store the types of each of the variables
     *
     * @var array
     */
    private $mTypes = Array();
    
    /**
     * Store the chunk names for each of the variables
     *
     * @var array
     */
    private $mChunkNames = Array();
    
    /**
     * Store the maximum string length of variables
     *
     * @var array
     */
    private $mStringMaximums = Array();
    
    /**
     * Store the variables names for the GetFields() call later on
     *
     * @var array
     */
    private $mVariableNames = Array();
    
    /**
     * The name of the answer field
     * @var string
     */
    private $mAnswerName = false;

    /**
     * Construct the response from a text string.
     *
     * @param string $input - string looking like this: varname0.BIN.RESP|varname1.int.FENTER|etc
     */
    public function __construct($input)
    {
		if( $input != null )
		{
			// decode the persistent data
			$chunkData = lmcTextChunk::Decode($input, "PDATA");

			if( $chunkData->ChunkExists("EREPORT") )
			{
				Leap::SetReportingMode($chunkData->GetChunk("EREPORT"));
			}

			// format is an associative array of:
			// varname -> the name of the POST variable to query
			// length -> optional; only for string type objects
			// type -> the type of the data
			// chunkname -> the name of the chunk to add for this value when submitting to the validation server

			$list = $chunkData->GetChunk("FIELDS2");

			if(true == is_array($list))
			{
				foreach ($list as $value)
				{
					$stringMax = false;
					if (array_key_exists("length", $value))
					{
						$stringMax = $value["length"];
					}

					$this->AddVariable( $value["varname"], $value["type"], $value["chunkname"], $stringMax );

					if( $value['chunkname'] == 'RESP' )
					{
						$this->mAnswerName = $value["varname"];
					}
				}
			}
			else
			{
				throw new LeapException("Invalid response data for FIELDS", LMSC_INVALIDDATA);
			}

			if( $this->mAnswerName == null || $this->mAnswerName === false )
			{
				throw new LeapException('Unable to find RESP chunk in FIELDS', LMSC_INVALIDDATA);
			}
		}
    }
    
	/**
	 * AddVariable:
	 * Adds a new variable to the list of variables that the response looks for
	 *
	 * @param string $variableName		- the name of the variable
	 * @param string $type				- the text chunk data type of the variable
	 * @param string $chunkname			- the text chunk name for the variable
	 * @param int $stringMax			- the maximum length of the variable, if it's a string. false can be used to ignore this value
	 * @return string
	 */
    private function AddVariable( $variableName, $type = LM_CHUNK_TYPE_NULL, $chunkname = LM_CHUNK_TYPE_NULL, $stringMax = false )
    {
		$this->mTypes[$variableName] = $type;
		$this->mChunkNames[$variableName] = $chunkname;
		$this->mVariableNames[] = $variableName;
		if ( $stringMax !== false )
		{
			$this->mStringMaximums[$variableName] = $stringMax;
		}
    }
    
    /**
     * Sets the validation fields in a text chunk meant to be sent to the validation server
     *
     * @param lmcTextChunk $chunk - the chunk to fill in
     */
    public function SetValidationFields(&$chunk)
    {
		// Store the chunk types as we're creating temporary types and protects recursion
		$chunktypes = lmcTextChunk::GetChunkTypes();
		
		// go through and register all of our chunks
		foreach ($this->mChunkNames as $key => $value)
		{
			if ( $this->mTypes[$key] !== LM_CHUNK_TYPE_NULL )
			{
				lmcTextChunk::RegisterChunk($value, $this->mTypes[$key]);
			}
		}
		
		// add all of the chunks now
		foreach ($this->mVariableNames as $varName)
		{
			$add = true;
			switch($this->mTypes[$varName])
			{
				case "BIN":
				case "TEXT":
					if(array_key_exists($varName, $this->mStringMaximums))
					{
						$value = substr($this->mVariables[$varName], 0, $this->mStringMaximums[$varName]);
					}
				break;

				case LM_CHUNK_TYPE_NULL:
					$add = false;
				break;
				
				default:
				break;
			}

			// make sure the variable is there, otherwise an exception
			// is thrown.
			if(false === array_key_exists($varName, $this->mVariables))
			{
				$add = false;
			}
			
			if ( $add )
			{
				$chunk->AddChunk($this->mChunkNames[$varName], $this->mVariables[$varName]);
			}
		}
				
		// restore the chunk types
		lmcTextChunk::SetChunkTypes($chunktypes);
    }
        
    /**
     * Get the answer stored in the object.
     *
     * @return string
     */
    public function GetAnswer()
    {
		return $this->mAnswerName;
    }
    
    /**
     * Returns a list of vars to look for in $_POST
     *
     * @return array
     */
    public function GetFields()
    {
		return $this->mVariableNames;
    }

    /**
     * Get a value from the object
     *
     * @param string $varname
     * @return mixed
     */
    public function GetVar($varname)
    {
		// $this->CheckVarName($varname);
		// do not check that the var name exists!
		// we allow the client of lmcResponse to check if a variable is present without validating
		// so that they can check if the answer has been set yet.

		if(false == array_key_exists($varname, $this->mVariables))
		{
			return false;
		}
		else
		{
			return $this->mVariables[$varname];
		}
    }
    
    /**
     * Set a value in the object
     *
     * @param string $varname
     * @param mixed $value
     * @return bool
     */
    public function SetVar($varname, $value)
    {
		$this->CheckVarName($varname);

		// if a var is '', don't set it
		if('' === $value)
		{
			$this->mVariables[$varname] = false;
		}
		else
		{
			// make sure the field being set is proper
			switch ($this->mTypes[$varname])
			{
				case "INT":
					if (false == is_numeric($value))
					{
						throw new LeapException("Invalid response data for $varname (expected an integer)", LMSC_INVALIDTYPE);
					}
				break;
				
				case "TEXT":
				case "BIN":
					if (false == is_string($value))
					{
						throw new LeapException("Invalid response data for $varname (expected " . $this->mTypes[$varname] . ")", LMSC_INVALIDTYPE);
					}
				break;
				
				default:
				break;
			}
			
			$this->mVariables[$varname] = $value;
		}	

		return true;
    }
    
    /**
     * Check to see if a varname is valid for the object. If it is not, an
     * Exception is thrown.
     * 
     * @param $varname
     */
    private function CheckVarName($varname)
    {
		if(false === array_search($varname, $this->mVariableNames))
		{
			throw new LeapException("'$varname' is not a valid parameter. Valid parameters are: " . join(", ", array_keys($this->mVariables)), LMSC_INVALIDTYPE);
		}
    }
}