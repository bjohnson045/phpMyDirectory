<?php
/**
* Cleaner
* Used to sanitize input/output
*/
class Cleaner {
    /**
    * Registry object
    * @var object
    */
    var $PMDR;

    /**
    * Cleaner constructor
    *
    * @param mixed $PMDR
    * @return Cleaner
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
    }

    /**
    * Clean output generally used in HTML
    * @param mixed $mixed
    * @param boolean $strip
    * @return mixed
    */
    function clean_output($mixed,$strip = false) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            // Prevent double escaping
            $mixed = $this->unclean_html($mixed);
            // Supress errors since some charsets not supported
            if($strip) {
                $mixed = $this->PMDR->get('HTML_Filter')->process($mixed, array());
            }
            return @htmlspecialchars($mixed,ENT_QUOTES);
        }
    }

    /**
    * Clean output that may contain allowable HTML
    * @param mixed $mixed
    * @return mixed
    */
    function clean_output_html($mixed) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            return $this->PMDR->get('HTML_Filter')->process($mixed, null);
        }
    }

    /**
    * Clean javascript output
    * @param mixed $mixed
    * @return mixed
    */
    function output_js($mixed) {
        return json_encode($mixed);
    }

    /**
    * Clean javascript output that is ONLY a string
    * @param string $string
    * @return string
    */
    function output_js_string($string) {
        return $this->clean_output(addslashes($string));
    }

    /**
    * Clean a URL
    * @param string $string
    * @return string
    */
    function clean_output_url($string) {
        return urlencode(@htmlspecialchars($string,ENT_QUOTES));
    }

    /**
    * Clean output used in form code that could possibly be a value of a field
    * @param mixed $mixed
    * @return mized
    */
    function clean_form_output($mixed) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            // Supress errors since some charsets not supported
            return @htmlspecialchars($mixed,ENT_QUOTES);
        }
    }

    /**
    * Trim a value and consolidate new lines
    * @param mixed $value
    * @return mixed
    */
    function clean_trim($value) {
        if(is_array($value)) {
            return array_map(array($this, __FUNCTION__),$value);
        } else {
            return preg_replace("/(\r\n|\n|\r)/", "\n",trim($value));
        }
    }

    /**
    * Cleanr form input
    * @param mixed $value
    * @param array $tags
    * @param boolean $no_trim
    * @return mixed
    */
    function clean_input($value, $tags = array(), $no_trim = false) {
        if(is_array($value)) {
            foreach($value AS $key=>$subvalue) {
                $value[$key] = $this->clean_input($subvalue, $tags, $no_trim);
            }
        } else {
            if(!$no_trim) {
                $value = $this->clean_trim($value);
            }
            if(!is_null($tags)) {
                $value = $this->unclean_html($this->PMDR->get('HTML_Filter')->process($value, $tags),ENT_QUOTES);
            }
        }
        return $value;
    }

    /**
    * Clean database input
    * @param string $string
    * @return string
    */
    function clean_db($string, $quotes = true) {
        if(is_array($string)) {
            return array_map(array($this, __FUNCTION__),$string,array_fill(0,count($string),$quotes));
        } else {
            return $this->PMDR->get('DB')->Clean($string, $quotes);
        }
    }

    /**
    * Clean URLs used in header redirects
    * @param string $url
    * @return string
    */
    function clean_header_url($url) {
        return str_replace('&amp;','&',Strings::strip_new_lines(urldecode($url)));
    }

    /**
    * Remove any sanitization from HTML
    * @param mixed $value
    * @return mixed
    */
    function unclean_html($mixed) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            return @htmlspecialchars_decode($mixed, ENT_QUOTES);
        }
    }

    /**
    * Strip tags and allow array input
    * @param mixed $value
    * @return mixed
    */
    function strip_tags($mixed) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            return strip_tags($mixed);
        }
    }
}
?>