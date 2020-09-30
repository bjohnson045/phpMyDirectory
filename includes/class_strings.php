<?php
/**
 * String manipulation class
 * Provides multi-byte replacement string functions.
 *
 * Includes functions ported from phputf8 (http://phputf8.sourceforge.net/).
 * @copyright  (c) 2005 Harry Fuecks
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt
 */
class Strings {
    /**
    * Sets if the server has mb_string enabled or not
    * @var boolean
    */
    public static $server_utf8 = false;
    /**
    * List of called methods where the individual file has been included.
    * @var array
    */
    public static $loaded = array();
    /**
    * Initialize and check for UTF8 support
    */
    public static function init() {
        // Check if PCRE is compiled with UTF8 support
        if(!preg_match('/^.$/u','ñ')) {
            trigger_error('PCRE has not been compiled with UTF8 support',E_USER_WARNING);
        }

        // Check if mbstring is loaded and set the encoding to UTF-8
        if(extension_loaded('mbstring')) {
            mb_internal_encoding('UTF-8');
            Strings::$server_utf8 = true;
        }
    }
    /**
    * Check if a string is ASCII
    * @param string $str
    * @return boolean
    */
    public static function is_ascii($str) {
        if(is_array($str)) {
            $str = implode($str);
        }
        return !preg_match('/[^\x00-\x7F]/S', $str);
    }

    /**
    * Strip ASCII control characters
    * @param string $str
    * @return string
    */
    public static function strip_ascii_control($str) {
        return preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/S', '', $str);
    }

    /**
    * Strip non-ASCII characters
    * @param string $str
    * @return string
    */
    public static function strip_non_ascii($str) {
        return preg_replace('/[^\x00-\x7F]+/S', '', $str);
    }

    /**
    * Transliterate characters to ASCII (remove accents, etc)
    * @param string $str
    * @param int $case 1, -1 or 0 to allow for case specific conversion
    * @return string
    */
    public static function transliterate_to_ascii($str, $case = 0, $ignores = array()) {
        if(!isset(self::$loaded[__FUNCTION__])) {
            require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
            self::$loaded[__FUNCTION__] = true;
        }
        return _transliterate_to_ascii($str, $case, $ignores);
    }

    /**
    * String Length
    * @param string $str
    * @return string
    */
    public static function strlen($str) {
        if(self::$server_utf8) {
            return mb_strlen($str);
        } elseif(self::is_ascii($str)) {
            return strlen($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strlen($str);
        }
    }

    /**
    * String position
    * @param string $str
    * @param string $search
    * @param int $offset
    * return int
    */
    public static function strpos($str, $search, $offset = 0) {
        if(self::$server_utf8) {
            return mb_strpos($str, $search, $offset = 0);
        } elseif(self::is_ascii($str) AND self::is_ascii($search)) {
            return strpos($str, $search, $offset);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strpos($str, $search, $offset);
        }
    }

    /**
    * String reverse
    * @param string $str
    * @return string
    */
    public static function strrev($str) {
        if(self::is_ascii($str)) {
            return strrev($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strrev($str);
        }
    }

    /**
    * Find last occurance in string
    * @param string $str
    * @param string $search
    * @param int $offset
    * @return int Position
    */
    public static function strrpos($str, $search, $offset = 0) {
        if(self::$server_utf8) {
            return mb_strrpos($str, $search, $offset);
        } elseif(self::is_ascii($str) AND self::is_ascii($search)) {
            return strrpos($str, $search, $offset);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strrpos($str, $search, $offset);
        }
    }

    /**
    * Find length of segment with a mask
    * @param string $str
    * @param string $mask
    * @param int $offset
    * @param int $length
    * @return int
    */
    public static function strspn($str, $mask, $offset = NULL, $length = NULL) {
        if(self::is_ascii($str)) {
            return strspn($str, $mask, $offset = NULL, $length = NULL);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strspn($str, $mask, $offset = NULL, $length = NULL);
        }
    }

    /**
    * Substring
    * @param string $str
    * @param int $start
    * @param int $length
    * @return string
    */
    public static function substr($str,$start,$length = false) {
        if(self::$server_utf8) {
            if($length) {
                return mb_substr($str,$start,$length);
            } else {
                return mb_substr($str,$start,mb_strlen($str));
            }
        } elseif(self::is_ascii($str)) {
            if($length) {
                return substr($str,$start,$length);
            } else {
                return substr($str,$start);
            }
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            if($length) {
                return _substr($str,$start,$length);
            } else {
                return _substr($str,$start);
            }
        }
    }

    /**
    * Substring replace
    * @param string $str
    * @param string $replacement
    * @param int $offset
    * @param int $length
    * @return string
    */
    public static function substr_replace($str, $replacement, $offset, $length = NULL) {
        if(self::is_ascii($str) AND self::is_ascii($replacement)) {
            if(is_null($length)) {
                return substr_replace($str, $replacement, $offset);
            } else {
                return substr_replace($str, $replacement, $offset, $length);
            }
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _substr_replace($str, $replacement, $offset, $length);
        }
    }

    /**
    * Substring case insensitive replace
    * @param string $search
    * @param string $replacement
    * @param string $string
    * @param int $count
    * @return string
    */
    public static function str_ireplace($search, $replacement, $string, $count = NULL) {
        if(self::is_ascii($search) AND self::is_ascii($replacement) AND self::is_ascii($string)) {
            if(is_null($length)) {
                return str_ireplace($search, $replacement, $string);
            } else {
                return str_ireplace($search, $replacement, $string, $count);
            }
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _str_ireplace($search, $replacement, $string, $count);
        }
    }

    /**
    * String to lower case
    * @param string $str
    * @return string
    */
    public static function strtolower($str) {
        if(self::$server_utf8) {
            return mb_strtolower($str);
        } elseif(self::is_ascii($str)) {
            return strtolower($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strtolower($str);
        }
    }

    /**
    * String to upper case
    * @param string $str
    * @return string
    */
    public static function strtoupper($str) {
        if(self::$server_utf8) {
            return mb_strtoupper($str);
        } elseif(self::is_ascii($str)) {
            return strtoupper($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strtoupper($str);
        }
    }

    /**
    * Upper case first letter
    * @param string $str
    * @return string
    */
    public static function ucfirst($str) {
        if(self::is_ascii($str)) {
            return ucfirst($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _ucfirst($str);
        }
    }

    /**
    * Upper case first letter of all words
    * @param string $str
    * @return string
    */
    public static function ucwords($str) {
        if(self::is_ascii($str)) {
            return ucwords($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _ucwords($str);
        }
    }

    /**
    * Compare strings according to case
    * @param string $str1
    * @param string $str2
    * @return int
    */
    public static function strcasecmp($str1, $str2) {
        if(self::is_ascii($str1) AND self::is_ascii($str2)) {
            return strcasecmp($str1, $str2);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _strcasecmp($str1, $str2);
        }
    }

    /**
    * Trim a string of white space or characters
    * @param string $str
    * @param string $charlist
    * @return string
    */
    public static function trim($str, $charlist = NULL) {
        if(is_null($charlist)) {
            return trim($str);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _trim($str, $charlist);
        }
    }

    /**
    * Trim a string to the left of white space or characters
    * @param string $str
    * @param string $charlist
    * @return string
    */
    public static function ltrim($str, $charlist = NULL) {
        if(is_null($charlist)) {
            return ltrim($str);
        } elseif(self::is_ascii($str)) {
            return ltrim($str, $charlist);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _ltrim($str, $charlist);
        }
    }

    /**
    * Trim a string to the right of white space or characters
    * @param string $str
    * @param string $charlist
    * @return string
    */
    public static function rtrim($str, $charlist = NULL) {
        if(is_null($charlist)) {
            return rtrim($str);
        } elseif(self::is_ascii($str)) {
            return rtrim($str, $charlist);
        } else {
            if(!isset(self::$loaded[__FUNCTION__])) {
                require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
                self::$loaded[__FUNCTION__] = true;
            }
            return _rtrim($str, $charlist);
        }
    }

    public static function str_pad($str, $length, $pad_str = ' ', $pad_type = STR_PAD_RIGHT) {
        if(!isset(self::$loaded[__FUNCTION__])) {
            require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
            self::$loaded[__FUNCTION__] = true;
        }
        return _str_pad($str, $length, $pad_str, $pad_type);
    }

    public static function str_split($str, $split_length = 1) {
        if(!isset(self::$loaded[__FUNCTION__])) {
            require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
            self::$loaded[__FUNCTION__] = true;
        }
        return _str_split($str, $split_length);
    }

    /**
    * Takes a UTF8 string and returns an array of ints corresponding to the unicode characters.
    * @param string $str
    * @return array
    * @return false
    */
    public static function to_unicode($str) {
        if(!isset(self::$loaded[__FUNCTION__])) {
            require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');
            self::$loaded[__FUNCTION__] = true;
        }
        return _to_unicode($str);
    }

    /**
    * Takes an array of ints corresponding to unicode characters and returns a utf8 string.
    * @param string $str
    * @return array
    * @return boolean
    */
    public static function from_unicode($arr){
        if(!isset(self::$loaded[__FUNCTION__])) {
            require(PMDROOT.'/includes/utf8/'.__FUNCTION__.'.php');;
            self::$loaded[__FUNCTION__] = true;
        }
        return _from_unicode($arr);
    }

    /**
    * Limit number of characters in a string
    * @param string $string String to limit
    * @param int $characters Character limit
    * @return string Limited string
    */
    public static function limit_characters($string, $characters) {
        if($characters AND self::strlen($string) > 0) {
            return self::substr($string,0,$characters);
        } else {
            return '';
        }
    }

    /**
    * Limit number of words in a string
    * @param string $text
    * @param int $characters
    * @return string
    */
    public static function limit_words($text, $characters, $suffix = '') {
        if(self::strlen($text) <= $characters) {
            return $text;
        } elseif($characters AND self::strlen($text) > 0) {
            $regex = '/^.{1,'.$characters.'}\b/s';
            $num_matches = preg_match($regex, $text, $matches);
            return $matches[0].$suffix;
        } else {
            return '';
        }
    }

    /**
    * Count words
    * @param string $string
    * @return int
    */
    public static function count_words($string) {
        return count(preg_split("/[\s,]+/",$string,-1,PREG_SPLIT_NO_EMPTY));
    }

    public static function comma_separated($value) {
        if(is_array($value)) {
            $value = implode(',',$value);
        }
        $value = preg_replace("/(\r\n|\n|\r|\t)/", ',',$value);
        $value = trim(preg_replace('/,+/',',',$value),', ');
        return $value;
    }

    /**
    * Normalize a URL by removing all special characters
    * Eventually use Normalizer class in PHP 5.3.0
    * @param string $str String to normalize
    * @return string Nromalized string
    */
    public static function rewrite($string) {
        global $PMDR;

        $allowed_characters = $PMDR->getConfig('rewrite_characters');
        // If we have a wildcard * we match any type of letter
        if(strstr($allowed_characters,'*')) {
            $regex_characters = '\p{L}';
        // Else we match alpha-numeric and any allowed characters
        } else {
            // Transliterate the string but ignore any characters we have defined
            $string = Strings::transliterate_to_ascii($string,0,explode(',',$allowed_characters));
            $regex_characters = '0-9A-Za-z\-';
            $regex_characters .= preg_quote(str_replace(',','',$allowed_characters),'/');
        }
        return Strings::strtolower(preg_replace(array('/&/','/\s+/','/\//','/[^'.$regex_characters.']/u'),array('and','-','-',''),$string));
    }

    /**
    * Generate a random string
    * @param int $length String length
    * @param array $filter Filter specific characters
    * @return string
    */
    public static function random($length, $special_characters = true, $numbers = true, $upper = false, $filter = array()) {
        $characters = array_merge(range('A','Z'),range('a','z'));
        if($special_characters) {
            $characters = array_merge($characters,array('~','!','@','#','$','%','^','&','*','(',')','_','-','+','=','{','}','[',']',':'));
        }
        if($numbers) {
            $characters = array_merge($characters,range(1,9));
        }
        $array = array_diff($characters,$filter);
        shuffle($array);
        $random = implode('',array_slice($array,0,$length));
        if($upper) {
            $random = self::strtoupper($random);
        }
        return $random;
    }

    /**
    * Convert break lines to new lines
    * @param string $string
    * @return string
    */
    public static function br2nl($string) {
        $find = array('<br>','<br />','&lt;br&gt;','&lt;br /&gt;');
        $replace = "\n";
        $string = str_replace($find,$replace,$string);
        return $string;
    }

    /**
    * Convert new lines to break lines by a full replacement
    * @param string $string
    * @return string
    */
    public static function nl2br_replace($string) {
        return str_replace(array("\r\n","\n","\r"),'<br />',$string);
    }

    /**
    * Strip new lines from a string
    * @param string $string
    * @return string
    */
    public static function strip_new_lines($string) {
        return preg_replace("/(%0A|%0D|\n+|\r+)/i", '', $string);
    }

    /**
    * Minimize spacing by trimming and condensing multiple spaces into single spaces
    * @param string $string
    * @return string
    */
    public static function minimize_spacing($string) {
        return trim(preg_replace('/[[:space:]]/',' ',$string));
    }

    /**
    * Finish a string with a character without duplicates
    * @param string $string String to finish with a character
    * @param string $character Character to finish with
    */
    public static function str_finish($string, $character) {
        return Strings::rtrim($string,$character).$character;
    }
}
Strings::init();
?>