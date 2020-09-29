<?php
/**
 * @package   NuCaptcha PHP clientlib
 * @author    <support@nucaptcha.com> Leap Marketing Technologies Inc
 * @license   LGPL License 2.1 (see included license.txt)
 * @link      http://www.nucaptcha.com/api/php
 */

// we only want to redefine the client version of this class if we haven't already defined the non-client version
if(class_exists("lmcConfigFile", false))
{
	return;
}

/**
 * Parse INI style leap configs and provide and object interface.
 * 
 * Typical usage:
 * 
 * $c = new lmcConfigFile('/path/to/config/file.config');
 * 
 * $value = $c->GetGlobal("hostname");
 * 
 * A config file looks like:
 * 
 * # this is a comment
 * # note that if no section is defined, GLOBAL is assumed
 * hostname = localhost
 * ip = 127.0.0.1
 * 
 * [MYSECTION]
 * mykey = value
 * 
 */ 
class lmcConfigFile
{
	/**
	 * Stores the parsed config file
	 *
	 * @var Array
	 */
	private $mVariables = array();
	
	/**
	 * Stores the filename of the current config file
	 *
	 * @var string
	 */
	private $mFilename = "";
	
	/**
	 * Create a new config object
	 * 
	 * @param string $filename name of file to open
	 * @return lmcConfigFile
	 */
	public function __construct( $filename )
	{
		$this->mFilename = $filename;
		$this->mVariables = $this->ParseConfigFile( $filename );
	}
	
	/**
	 * Return a setting from the global section
	 * 
	 * @param string $name name of the variable
	 * @return string
	 */
	public function GetGlobal( $name )
	{		
		return $this->Get( 'GLOBAL', $name );
	}
	
	/**
	 * Return a setting from a section
	 * 
	 * @param string $section name of the section
	 * @param string $name name of the variable
	 * @return string
	 */
	public function Get( $section, $name )
	{		
		$section = strtoupper( $section );
		$name    = strtoupper( $name );

		// check to make sure that we can access this variable even
		if ( false === array_key_exists( $section, $this->mVariables ) )
		{
			$filename = $this->mFilename;
			throw new Exception( "The variable $name in section $section could not be found in $filename, because the section does not exist." );
		}
		
		if( true === array_key_exists( $name, $this->mVariables[$section] ) )
		{
			return $this->mVariables[$section][$name];
		}
		else
		{
			throw new Exception( "The variable $name in section $section could not be found in " . $this->mFilename );
		}
	}
	
	/**
	 * Parse a config file
	 * @param string $file name of the file to parse
	 * @return array
	 */
	private function ParseConfigFile( $file )
	{
		$ini = file( $file );
		
		if ( ($ini === FALSE) || (count( $ini ) == 0) )
		{
			//echo "config file $file not found!<br>";
			return array();
		}
		
		// set up the default array
		$result = Array( 'GLOBAL' => Array() );
		$currentsection = 'GLOBAL';
		
		foreach ( $ini as $line )
		{
			$line = trim( $line );
			$line = str_replace( "\t", " ", $line );
		
			// Comments
			if ( !preg_match( '/^[a-zA-Z0-9[]/', $line ) )
			{
				continue;
			}
	
			// Sections
			if ( $line[0] == '[' )
			{
				// find the name of the section
				$tmp = explode( ']', $line );
				$currentsection = strtoupper( trim( substr( $tmp[0], 1 ) ) );
				
				// create the entry if it doesn't exist
				if ( false === array_key_exists( $currentsection, $result ) )
				{
					$result[$currentsection] = Array();
				}
				
				continue;
			}
	
			// Key-value pair
			list( $key, $value ) = explode( '=', $line, 2 );
			$key = strtoupper( trim( $key ) );
			$value = trim( $value );
			
			$isQuoted = false;
			if ( ($value{0} === '"') || ($value{0} === '\'') )
			{
				$isQuoted = true;
			}
			
			if ( strstr( $value, ";" ) )
			{
				$tmp = explode( ';', $value );
				if ( count( $tmp ) == 2 )
				{
					if ( (($value{0} != '"') && ($value{0} != "'")) ||
						 preg_match( '/^".*"\s*;/', $value ) || preg_match( '/^".*;[^"]*$/', $value ) ||
						 preg_match( "/^'.*'\s*;/", $value ) || preg_match( "/^'.*;[^']*$/", $value ) )
					{
						$value = $tmp[0];
					}
				}
				else
				{
					if ( $value{0} == '"' )
					{
						$value = preg_replace( '/^"(.*)".*/', '$1', $value );
					}
					else if ( $value{0} == "'" )
					{
						$value = preg_replace( "/^'(.*)'.*/", '$1', $value );
					}
					else
					{
						$value = $tmp[0];
					}
				}
			}
			
			$value = trim( $value );
			$value = trim( $value, "'\"" );
			
			if ( $isQuoted === false )
			{
				if ( $this->isFloatNumber( $value ) )
				{
					$value = (float) $value;
				}
				else if ( $this->isIntegerNumber( $value ) )
				{
					$value = (int) $value;
				}
				else if ( $this->isBoolValue( $value ) )
				{
					$value = $this->convertToBool( $value );
				}
			}
	
			$result[$currentsection][$key] = $value;
		}
	
		return $result;
	}
	
	/**
	 * determine if a string is a floating point number
	 *
	 * @param string $n
	 * @return bool
	 */
	function isFloatNumber($n)
	{
		return ((string)(float)$n) === (string)$n;
	}
	
	/**
	 * determine if a string is an integer
	 *
	 * @param string $n
	 * @return bool
	 */
	function isIntegerNumber($n)
	{
		return ((string)(int)$n) === (string)$n;
	}
	
	/**
	 * Determine if a string is a boolean. If the string is 'true' or
	 * 'false', boolean true is returned. Otherwise, boolean false is
	 * returned.
	 *
	 * @param string $n
	 * @return bool
	 */
	function isBoolValue($n)
	{
		switch (strtolower($n))
		{
			case ("true"): return true;
			case ("false"): return true;
			default: return false;
		}
    }
    
    	/**
	 * Convert a boolean string to a boolean value. The string true is
	 * evaluated to boolean true and string false is evaluated to string
	 * false.
	 * 
	 * @param string $n
	 * @return bool
	 */
    function convertToBool($n)
	{
		switch (strtolower($n))
		{
			case ("true"): return true;
			case ("false"): return false;
			default: return false;
		}
    }
}

