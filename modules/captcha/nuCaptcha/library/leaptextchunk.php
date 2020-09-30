<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(!class_exists("lmcTextChunk", false))
{
	/**
	 * Callback function to save out a string.  Doesn't do anything other
	 * than return the passed in string.
	 *
	 * @param string $string
	 * @return string
	 */
	function lmccallbackSaveString(&$string)
	{
		return $string;
	}

	function lmccallbackLoadString(&$string)
	{
		return $string;
	}

	/**
	 * Callback to convert an int to a string
	 *
	 * @param integer $int
	 * @return string
	 */
	function lmccallbackSaveInt($int)
	{
		return "".$int;
	}

	function lmccallbackLoadInt($int)
	{
		return $int;
	}

	/**
	 * Function to convert binary to a string.  It base64 encodes it.
	 *
	 * @param array/binary $bin
	 * @return base64 string
	 */
	function lmccallbackSaveBinary($bin)
	{
		return lmcBase64::EncodeBinary($bin);
	}

	function lmccallbackLoadBinary($bin)
	{
		return lmcBase64::DecodeBinary($bin);
	}

	function array_compare($needle, $haystack)
	{
		if (!is_array($needle) || !is_array($haystack) || (count($haystack) > count($needle)))
		{
			return false;
		}

		$count = 0;
		$result = false;
		foreach ($needle as $k => $v)
		{
			if (!isset($haystack[$k]))
			{
				return false;
			}
			if ($haystack[$k] != $v)
			{
				return false;
			}
		}
		return true;
	}

	/**
	 * Function to convert array to a string.  It will do so by creating a lmcTextChunk
	 * and adding all the elements as chunks.
	 *
	 * @param array $array		- The data to add
	 * @param array $encoding	- Array of types to add for each data element
	 * @return string
	 */
	function lmccallbackSaveArray($array, $encoding)
	{
		if (count($array) != count($encoding))
		{
			throw new LeapException("Must have equal sizes for the data and the encoding format list", LMSC_INVALIDDATA);
		}

		// Create a new sub-chunk
		$chunk = new lmcTextChunk("_");
		$chunk->AddChunk("_L", count($array));

		// Get the list of indexes to store
		$indices = array_keys($array);

		// See if we should skip the indices because they're indexed from 0..n
		$skip = true;
		$index = 0;
		foreach ($array as $key=>$value)
		{
			// If the index doesn't match the expected index of the array position
			if ($index++ != $key)
			{
				$skip = false;
				break;
			}
		}

		// See if all the keys match
		$skip |= array_compare(array_keys($encoding), $indices);

		// Add each data element
		$index = 0;
		foreach($encoding as $value)
		{
			// Generate the key
			$key = $index++;

			// Register and store the index name
			if (!$skip)
			{
				$indexkey = $key."K";
				$chunk->RegisterChunk($indexkey, "TEXT");
				$chunk->AddChunk($indexkey, array_shift($indices));
			}

			// Register and store the value name
			if ($skip)
			{
				$valuekey = $key;
			}
			else
			{
				$valuekey = $key."V";
			}

			$chunk->RegisterChunk($valuekey, $value);
			$chunk->AddChunk($valuekey, array_shift($array));
		}

		// Return the chunk
		return $chunk->Export();
	}

	function lmccallbackLoadArray($array, $encoding)
	{
		// Store the chunk types as we're creating temporary types and protects recursion
		$chunktypes = lmcTextChunk::GetChunkTypes();

		// Predefine the chunk types
		$count = 0;
		foreach ($encoding as $type)
		{
			// Create the chunk names in case we stored the index names
			lmcTextChunk::RegisterChunk($count."K", "TEXT");
			lmcTextChunk::RegisterChunk($count."V", $type);
			// Create the chunk names based on index
			lmcTextChunk::RegisterChunk($count, $type);
			$count++;
		}

		// Decode the chunk
		$chunk = lmcTextChunk::Decode($array, "_");
		$len = $chunk->GetChunk("_L");
		$out = array();

		if ($len != count($encoding))
		{
			throw new LeapException("Must have equal sizes for the data and the encoding format list", LMSC_INVALIDDATA);
		}

		// See if we've skipped the indexes.  If we have the "V" on the first value then we know we have indexes
		$skip = !$chunk->ChunkExists("0V");
		if ($skip)
		{
			// Get the list of indexes to store
			$indices = array_keys($encoding);
		}

		for ($i = 0; $i < $len; $i++)
		{
			if ($skip)
			{
				$out[$indices[$i]] = $chunk->GetChunk($i);
			}
			else
			{
				$out[$chunk->GetChunk($i."K")] = $chunk->GetChunk($i."V");
			}
		}

		// restore the chunk types
		lmcTextChunk::SetChunkTypes($chunktypes);

		// return the array
		return $out;
	}
	
	function lmccallbackLoadDData($data)
	{
		return lmcUrlCoding::decodeStructure($data);
	}
	
	function lmccallbackSaveDData($data)
	{
		return lmcUrlCoding::encodeStructure($data);
	}

	/**
	 * Class:
	 * lmcTextChunk
	 *
	 * Purpose:
	 * This class is inteded to read and write the basic text chunk file
	 * format.  It is based off the IFF chunk format.
	 *
	 * Format:
	 * Header - A header of the count of chunks, followed by a pipe.
	 * Chunks - A string chunk name, followed by the char size, followed by the data
	 *          all separated by pipes.
	 */
	class lmcTextChunk
	{
		const LM_TEXTCHUNK_MAGIC_STRING = "LEAP";
		const LM_TEXTCHUNK_VERSION = 0;

		/**
		 * An array of the known chunk types.  Initialized to zero
		 *
		 * @var array
		 */
		private static	$m_ChunkTypes		= array();
		/**
		 * An array of the available encoding types.  Each type must have
		 * accompanying save/load callbacks
		 *
		 * @var array(string)
		 */
		private static	$m_EncodingTypes	= array("TEXT", "INT", "BIN", "ARRAY", "DDATA");
		/**
		 * An array of callbacks for saving various encoding types
		 *
		 * @var callback
		 */
		private static	$m_SaveCallbacks	= array(
			"TEXT"  => "lmccallbackSaveString",
			"INT"   => "lmccallbackSaveInt",
			"BIN"   => "lmccallbackSaveBinary",
			"ARRAY" => "lmccallbackSaveArray",
			"DDATA" => "lmccallbackSaveDData",
		);
		/**
		 * An array of callbacks for loading various encoding types
		 *
		 * @var callback
		 */
		private static	$m_LoadCallbacks	= array(
			"TEXT"  => "lmccallbackLoadString",
			"INT"   => "lmccallbackLoadInt",
			"BIN"   => "lmccallbackLoadBinary",
			"ARRAY" => "lmccallbackLoadArray",
			"DDATA" => "lmccallbackLoadDData",
		);
		
		/**
		 * The seperator character.
		 *
		 * @var unknown_type
		 */
		private static  $m_PIPE				= '|';
		/**
		 * The list of chunks.  Format limitation is that there can only be one
		 * chunk of each type.  This is the list of the chunks in the 'file' and
		 * the data that they contain.
		 *
		 * @var array
		 */
		private 		$m_Chunks			= array();

		/**
		 * GetChunkTypes:
		 * This will return the chunk types array
		 *
		 * @return array
		 */
		static public function GetChunkTypes()
		{
			return lmcTextChunk::$m_ChunkTypes;
		}

		/**
		 * SetChunkTypes:
		 * This will set the chunk types array
		 *
		 * @param array $types
		 */
		static public function SetChunkTypes($types)
		{
			lmcTextChunk::$m_ChunkTypes = $types;
		}

		/**
		 * ValidateChunkEncoding:
		 * This will ensure that it is valid to encode in this format
		 *
		 * @param string $encoding - format you wish to encode in
		 * @return bool - true if OK
		 */
		static private function ValidateChunkEncoding($encoding)
		{
			// If it's not an array, just return whether or not it exists
			if (!is_array($encoding))
			{
				return (false !== array_search($encoding, lmcTextChunk::$m_EncodingTypes));
			}

			// For each chunk in the array, validate the type
			$error = false;
			foreach ($encoding as $e)
			{
				$error |= !lmcTextChunk::ValidateChunkEncoding($e);
			}
			return !$error;
		}

		/**
		 * RegisterChunk:
		 * This will add a new chunk type to the list of available chunks.
		 *
		 * @param string $chunkname - the name of the chunk (how it is referenced)
		 * @param string $encoding - the method of encoding.  Text?  Binary?  etc.
		 * @return binary - true if success, false otherwise
		 */
		static public function RegisterChunk($chunkname, $encoding)
		{
			$chunkname = strtoupper($chunkname);

			// Check to see if it's a valid encoding type
			if (lmcTextChunk::ValidateChunkEncoding($encoding))
			{
				// Add the chunk type
				lmcTextChunk::$m_ChunkTypes[$chunkname] = $encoding;
				return true;
			}
			throw new LeapException("$encoding is not a valid encoding type for chunk (Adding $chunkname).", LMSC_INVALIDTYPE);
			return false;
		}

		/**
		 * RegisterStandardChunkTypes:
		 * This will register all of the standard chunk types.  The class initializes
		 * lazily.
		 *
		 * TODO: These should be pre-initialized in $m_ChunkTypes.
		 *
		 */
		static private function RegisterStandardChunkTypes()
		{
			// Ensure we only register once
			static $registered = false;
			if ($registered) return;
			$registered = true;

			// Standard Chunk Chunks
			lmcTextChunk::RegisterChunk("ERROR", "TEXT");		// An error is being passed
			lmcTextChunk::RegisterChunk("CERROR", "BIN");		// An encrypted error
			lmcTextChunk::RegisterChunk("ECODE", "INT");			// Error Code
			lmcTextChunk::RegisterChunk("ELINE", "INT");			// Error Line number
			lmcTextChunk::RegisterChunk("EFILE", "INT");			// Error Filename
			lmcTextChunk::RegisterChunk("EHFILE", "INT");		// Error Filename - Hashed
			lmcTextChunk::RegisterChunk("TRACE", "TEXT");		// Stack Trace
			lmcTextChunk::RegisterChunk("EMSG", "TEXT");			// Decoded Error Message

			lmcTextChunk::RegisterChunk("TYPE", "TEXT");			// Package Type
			lmcTextChunk::RegisterChunk("CID", "INT");			// Client ID
			lmcTextChunk::RegisterChunk("KID", "INT");			// Key ID
			lmcTextChunk::RegisterChunk("TIME", "INT");			// TimeStamp (unix32bit)
			lmcTextChunk::RegisterChunk("IV", "BIN");			// Initialization Vector
			lmcTextChunk::RegisterChunk("SKEY", "BIN");			// Symmetric Key
			lmcTextChunk::RegisterChunk("SESID", "TEXT");		// Session ID
			lmcTextChunk::RegisterChunk("_L", "INT");			// The length of the array

			lmcTextChunk::RegisterChunk("PUB_VER", "INT");		// The Publisher Library Version

			// Token Request Chunks
			lmcTextChunk::RegisterChunk("IP", "TEXT");			// Users IP Address
			lmcTextChunk::RegisterChunk("XF", "TEXT");			// Users XForward from header
			lmcTextChunk::RegisterChunk("USERDATA", "BIN");		// Custom User Information
			lmcTextChunk::RegisterChunk("UA", "TEXT");			// User agent
			lmcTextChunk::RegisterChunk("RU", "TEXT");			// request URI
			lmcTextChunk::RegisterChunk("RF", "TEXT");			// referrer
			lmcTextChunk::RegisterChunk('USESSL', 'INT');
			lmcTextChunk::RegisterChunk("CAID", "TEXT");			// Campaign ID
			lmcTextChunk::RegisterChunk("TEMPLATE", "INT");		// Which template to use
			lmcTextChunk::RegisterChunk("PURPOSE", "TEXT");		// Captcha purpose string
			lmcTextChunk::RegisterChunk("PLATFORM", "TEXT");		// The platform we're running in
			lmcTextChunk::RegisterChunk('HINTS', 'TEXT');        // Hints about the request
			lmcTextChunk::RegisterChunk('DEFAULTLANG', 'TEXT');  // Widget language
			lmcTextChunk::RegisterChunk('VERSION', 'INT');		
			
			// Token Response Chunks
			lmcTextChunk::RegisterChunk("TOKEN", "TEXT");		// The token
			lmcTextChunk::RegisterChunk("VSERV", "TEXT");		// The Validation Server
			lmcTextChunk::RegisterChunk("DSERV", "TEXT");		// The Data Server
			lmcTextChunk::RegisterChunk("RSERV", "TEXT");		// The Resource Server
			lmcTextChunk::RegisterChunk("HTML", "TEXT");			// The HTML to output
			lmcTextChunk::RegisterChunk("LINKS", "TEXT");		// The links (css, javascript) to output
			// TRES v0
			lmcTextChunk::RegisterChunk("FIELDS", "TEXT");		// The string array of POST fields to be queried for the response
			lmcTextChunk::RegisterChunk("JSVALUES", "TEXT");		// The string array of value to be sent to the javascript
	
			// TRES v1
			lmcTextChunk::RegisterChunk("FIELDS2", "DDATA");		// The string array of POST fields to be queried for the response
			lmcTextChunk::RegisterChunk("JSVALUES2", "DDATA");		// The string array of value to be sent to the javascript
			lmcTextChunk::RegisterChunk("ANSW", array("TEXT", "TEXT", "TEXT", "TEXT", "TEXT")); // The answer list

			// TRES v5
			lmcTextChunk::RegisterChunk("EREPORT", "TEXT");		// CSV string of preferred error reporters
			lmcTextChunk::RegisterChunk("DISABLED_REASON", "TEXT"); // Is this publisher disabled.  If so, why?

			// Validate Request Chunks
			lmcTextChunk::RegisterChunk("HASH", "TEXT");			// The hash of the token request, used to look up the response in memcache d

			// Validate Response Chunks
			lmcTextChunk::RegisterChunk("VALID", "INT");			// Was the response valid?  0 = no, 1 = yes, 2 = error

			// LEAP-950 TREQ v1
			lmcTextChunk::RegisterChunk('CAMPAIGNID', 'TEXT');

			// PHP API CHUNKS
			lmcTextChunk::RegisterChunk("PSDATA", "TEXT");		// The Persistent Storage Data
			lmcTextChunk::RegisterChunk("PUID", "TEXT");			// Public Unique ID
		}

		/**
		 * Constructor.  This will register the various chunk types.
		 *
		 * @param string $type - What type of request is this?
		 */
		public function __construct($type)
		{
			lmcTextChunk::RegisterStandardChunkTypes();
			$this->AddChunk("TYPE", $type);
		}

		/**
		 * AddChunk:
		 * This will store the contents of $data into the chunk of type $name.
		 * The $data should be in the format specified byt the chunk $name.
		 *
		 * @param string $name - the chunk type
		 * @param variable $data - the data to store in the chunk
		 */
		public function AddChunk($name, $data)
		{
			$name = strtoupper($name);

			// Find the chunk type to get its encoding
			if (array_key_exists($name, lmcTextChunk::$m_ChunkTypes))
			{
				// Get the encoding format
				$encoding = lmcTextChunk::$m_ChunkTypes[$name];

				// If we're trying to store an array
				if (is_array($data) && is_array($encoding))
				{
					// Call the save function callback
					$callback = lmcTextChunk::$m_SaveCallbacks["ARRAY"];
					$this->m_Chunks[$name] = $callback($data, $encoding);
					return;
				}
				// Confused encoding, one isn't an array
				else if (
					// it's okay for one to be an array if it's a list
					(is_array($data) && !is_array($encoding)
						&& 'DDATA' != $encoding)
				)
				{
					throw new LeapException("Must be an array when encoding for an array", LMSC_INVALIDTYPE);
				}
				// Simple data type (not array)
				else
				{
					// Check for case of boolean - set to empty string
					if (is_bool($data)) $data = "";		// LEAP-106

					// Call the save function callback
					$callback = lmcTextChunk::$m_SaveCallbacks[$encoding];
					$this->m_Chunks[$name] = $callback($data);
					return;
				}
			}
			else
			{
				throw new LeapException("Could not find chunk type: $name.", LMSC_UNDEFINEDCHUNK);
			}
		}

		/**
		 * HasChunk:
		 * Returns true if a TextChunk has a particular chunk as part of it's data
		 *
		 * @param string $name - the chunk type
		 * @return boolean
		 */
		public function HasChunk($name)
		{
			if (array_key_exists($name, $this->m_Chunks) && isset($this->m_Chunks[$name]))
			{
				return true;
			}

			return false;
		}

		/**
		 * Export:
		 * This will convert all of the chunks into a single text stream.
		 *
		 * @return string
		 */
		public function Export()
		{
			$msg = "";

			// insert the magic text
			$msg = self::LM_TEXTCHUNK_MAGIC_STRING;

			// insert the version number
			$msg .= lmcTextChunk::$m_PIPE . self::LM_TEXTCHUNK_VERSION;

			// Output the count
			$msg .= lmcTextChunk::$m_PIPE;
			$msg .= count($this->m_Chunks);

			// Go through each element
			foreach ($this->m_Chunks as $key => $value)
			{
				$msg .= lmcTextChunk::$m_PIPE . $key . lmcTextChunk::$m_PIPE . strlen($value) . lmcTextChunk::$m_PIPE . $value;
			}

			return $msg;
		}

		/**
		 * Decode:
		 * This will decode an encoded lmcTextChunk export string
		 *
		 * @param string $tchunk - result of lmcTextChunk->Export()
		 * @param string $expectedtype - string of the type expected
		 * @return lmcTextChunkData
		 */
		static public function Decode(&$tchunk, $expectedtype)
		{
			lmcTextChunk::RegisterStandardChunkTypes();
			//echo "Decoding:<BR>$tchunk<BR><BR>";
			$ret = new lmcTextChunkData();

			// check for some magic
			$test = self::LM_TEXTCHUNK_MAGIC_STRING;
			$magic = substr($tchunk, 0, strlen(self::LM_TEXTCHUNK_MAGIC_STRING));
			if ( $magic != self::LM_TEXTCHUNK_MAGIC_STRING )
			{
				error_log(var_export($tchunk,true));
				throw new LeapException("No magic code at the beginning of the text string! $magic. Magic define itself is: " . $test, LMSC_INVALIDDATA);
			}

			// skip past the magic text and the first pipe now
			$pos = strlen(self::LM_TEXTCHUNK_MAGIC_STRING) + 1;

			// get the version number
			$end		= strpos($tchunk, lmcTextChunk::$m_PIPE, $pos);
			$version	= substr($tchunk, $pos, $end-$pos);
			$pos = $end+1;

			if ( $version < self::LM_TEXTCHUNK_VERSION )
			{
				//throw new LeapException("Invalid version number for the text data!", LMSC_INVALIDDATA);
			}

			// Get the number of chunks
			$end		= strpos($tchunk, lmcTextChunk::$m_PIPE, $pos);
			$numchunks	= substr($tchunk, $pos, $end-$pos);
			if ( $numchunks <= 0 )
			{
				throw new LeapException("Only '$numchunks' found.  Must be >=1", LMSC_INVALIDDATA);
			}
			//echo "Found $numchunks chunks<BR>";

			// Initialize our start position to just past the first pipe
			$pos = $end+1;

			// Go through all the chunks
			for ($i = 0; $i < $numchunks; $i++)
			{
				// Decode the name
				$end 	= strpos($tchunk, lmcTextChunk::$m_PIPE, $pos);
				$name 	= substr($tchunk, $pos, $end-$pos);
				$pos	= ($end + 1);

				// always make the name of the chunk upper case
				$name = strtoupper($name);

				// Decode the data size
				$end 	= strpos($tchunk, lmcTextChunk::$m_PIPE, $pos);
				$datasize= substr($tchunk, $pos, $end-$pos);
				$pos 	= ($end + 1);

				// Decode the data
				$data	= substr($tchunk, $pos, $datasize);
				$pos	= ($pos + $datasize + 1);

				// check that it's in the encoding array
				// if it isn't, skip it
				if (array_key_exists($name, lmcTextChunk::$m_ChunkTypes))
				{
					$encoding = lmcTextChunk::$m_ChunkTypes[$name];
					if (false !== lmcTextChunk::ValidateChunkEncoding($encoding))
					{
						// If dealing with an array
						if (is_array($encoding))
						{
							$callback = lmcTextChunk::$m_LoadCallbacks["ARRAY"];
							$ret->AddChunk($name, $callback($data, $encoding));
						}
						// Simple data type
						else
						{
							$callback = lmcTextChunk::$m_LoadCallbacks[$encoding];
							$ret->AddChunk($name, $callback($data));
						}
					}
				}

				//echo "Read '$name' of $datasize bytes as '$data'<BR>";
			}

			// Grab the first chunk type
			$type = false;
			try
			{
				$type = $ret->GetChunk("TYPE");
			}
			catch (LeapException $e)
			{			
				throw new LeapException("Chunk TYPE not known. - ".$e->getTrace(), LMSC_UNDEFINEDCHUNK);
			}

			if (($type != $expectedtype) && (0 != strcmp("*", $expectedtype)))
			{
				throw new LeapException("Incorrect Chunk Type.  Expecting: '$expectedtype' - Found: '$type'", LMSC_INVALIDTYPE);
			}

			// Return the created chunkdata object
			return $ret;
		}
	}

	/**
	 * lmcTextChunkData class
	 *
	 * This object is used to store the decoded TextChunk string from lmcTextChunk.
	 *
	 */
	class lmcTextChunkData
	{
		/**
		 * Private array holding the chunk=>data key pair
		 *
		 * @var unknown_type
		 */
		private $m_data	= array();

		/**
		 * This will add a chunk to the data
		 *
		 * @param string $name
		 * @param object $value
		 */
		public function AddChunk($name, $value)
		{
			$name = strtoupper($name);
			$this->m_data[$name] = $value;
		}

		/**
		 * GetChunk:
		 * This will return the chunk associated with the passed in name
		 * Need the debug throw mechanism because LeapException calls GetChunk()
		 *
		 * @param string $name
		 * @return object
		 */
		public function GetChunk($name, $debugthrow=true)
		{
			$name = strtoupper($name);
			if (isset($this->m_data[$name]))
			{
				return $this->m_data[$name];
			}
			// If in debug throw an exception, otherwise try to recover
			if ($debugthrow)
			{

				$chunks = "(Available: ";
				$keys = array_keys($this->m_data);
				foreach ($keys as $key)
				{
					$chunks .= "$key, ";
				}
				// Check to see if any chunks were added
				if (count($keys) >= 1)
				{
					// If so, remove the final ", " and append the ")"
					$chunks = substr($chunks, 0, strlen($chunks)-2).")";
				}
				else
				{
					// No chunks, so just say so
					$chunks .= "No available chunks)";
				}

				throw new LeapException("Trying to access chunk: $name that doesn't exist. $chunks", LMSC_UNDEFINEDCHUNK);
			}
			return "";
		}

		/**
		 * ChunExists:
		 * This will return true if it exists, false otherwise
		 *
		 * @param string $name
		 * @return bool
		 */
		public function ChunkExists($name)
		{
			$name = strtoupper($name);
			return (isset($this->m_data[$name]));
		}

		/**
		 * This will print out all the chunks in the ChunkData
		 *
		 * @return string
		 */
		public function PrintChunks()
		{
			$out = "";
			foreach ($this->m_data as $name => $data)
			{
				if (is_array($data))
				{
					$data = print_r($data, true);
				}
				$out .= "$name => $data<BR>";
			}
			return $out;
		}
	}

}
