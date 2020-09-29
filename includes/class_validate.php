<?php
/**
* Validate  a password string to be between 4 and 16 characters
*/
class Validate_Password {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance and set the required variable
    * @param boolean $required
    * @return Validate_Password
    */
    function __construct($required = true) {
        $this->PMDR = Registry::getInstance();
        $this->required = $required;
    }

    /**
    * Validate a string to be within 4 to 16 characters
    * @param string $value
    * @return boolean
    */
    function validate($value) {
         if(!(preg_match("/^.{4,16}$/", $value)) AND ($value != '' OR $this->required)) {
            $this->error = $this->PMDR->getLanguage('messages_password_validation_error');
            return false;
         }
         return true;
    }
}

/**
* Validate a value against banned IP addresses
*/
class Validate_Banned_IP {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Database
    * @var object
    */
    var $db;

    /**
    * Get the registry instance and the DB object
    * @return Validate_Banned_IP
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Validate the value against the database of banned IP addresses
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $ips = explode("\r\n",$this->db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname = 'banned_ips'"));
        if(in_array($value,$ips) AND trim($value) != '') {
            $this->error = $this->PMDR->getLanguage('messages_banned');
            return false;
        } else {
            return true;
        }
    }
}

/**
* Validate a value against a list of banned words
*/
class Validate_Banned_Words {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Database
    * @var object
    */
    var $db;

    /**
    * Get the registry instance and database object
    * @return Validate_Banned_Words
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Validate a string against a list of banned words
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $words = array_filter(explode("\n",$this->PMDR->getConfig('banned_words')));
        if(count($words)) {
            $words = implode('|',array_map('preg_quote',$words));
            $value = strip_tags($value);
            $matches = array();
            if(preg_match('/(\W|^)('.$words.')(\W|$)/im', $value, $matches)) {
                $this->error = sprintf($this->PMDR->getLanguage('messages_banned_word'),$matches[0],'%s');
                return false;
            }
        }
        return true;
    }
}

/**
* Validate a string against a list of banned URLs
*/
class Validate_Banned_URL {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Database
    * @var object
    */
    var $db;

    /**
    * Get the registry instance and database object
    * @return Validate_Banned_URL
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Validate a string against a list of banned URLs
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $urls = preg_split('/[\r\n]+/',$this->PMDR->getConfig('banned_urls'),null,PREG_SPLIT_NO_EMPTY);
        $value = trim($value);
        if(count($urls) AND $value != '') {
            foreach($urls as $url) {
                if(strstr($value,$url)) {
                    $this->error = $this->PMDR->getLanguage('messages_banned_url');
                    return false;
                }
            }
        }
        return true;
    }
}

/**
* Validate if a variable is empty
*/
class Validate_NonEmpty {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Allow zero to be a non-empty value
    * @var boolean
    */
    var $accept_zero = true;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance
    * @param mixed $accept_zero
    * @return Validate_NonEmpty
    */
    function __construct($accept_zero = true) {
        $this->PMDR = Registry::getInstance();
        $this->accept_zero = $accept_zero;
    }

    /**
    * Validate a variable to determine if it is empty.
    * @param mixed $value Accepts any variable type
    * @return boolean
    */
    function validate($value) {
        if(is_array($value)) {
            if(count($value) < 1) {
                $this->error = $this->PMDR->getLanguage('messages_required');
                return false;
            } else {
                return true;
            }
        } else {
            if((trim($value) == '' OR $value == false) AND (!$this->accept_zero OR $value != '0')) {
                $this->error = $this->PMDR->getLanguage('messages_required');
                return false;
            } else {
                return true;
            }
        }
    }
}

/**
* Validate if a file is larger than size zero
*/
class Validate_NonEmpty_File {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance
    * @return Validate_NonEmpty_File
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Check whether a file is empty by checking it's size is greater than zero
    * @param array $value
    * @return boolean
    */
    function validate($value) {
        if(!empty($value) AND (!isset($value['size']) OR $value['size'] > 0)) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_required');
            return false;
        }
    }
}

/**
* Validate string as a valid username
*/
class Validate_Username {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance
    * @return Validate_Username
    */
    function __construct($required = true) {
        $this->PMDR = Registry::getInstance();
        $this->required = $required;
    }

    /**
    * Validate a string to be alphanumeric and allow @ and . characters larger than 3 characters.
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(!empty($value)) {
            if(!(preg_match("/^[A-Za-z0-9_@\.]+$/", $value) AND strlen($value) > 3)) {
                $this->error = $this->PMDR->getLanguage('messages_username_validation_error');
                return false;
            }
        } elseif($this->required) {
            $this->error = $this->PMDR->getLanguage('messages_username_validation_error');
            return false;
        }
        return true;
    }
}

/**
* Validate a string as a valid email address
*/
class Validate_Email {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance
    * @param boolean $required
    * @return Validate_Email
    */
    function __construct($required = true) {
        $this->required = $required;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the value against an email regex
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        // We don't use gethostbynamel() here because a domain cannot resolve to an IP address but still have an MX record
        // OR (function_exists('checkdnsrr') AND !@checkdnsrr(substr(strrchr($value,'@'),1),'MX')
        if($this->required AND $value == '') {
            $this->error = $this->PMDR->getLanguage('messages_email_format');
            return false;
        }
        if(preg_match('/[,\s]+/',$value)) {
            $values = preg_split('/[,\s]+/',$value);
        } else {
            $values = array($value);
        }
        unset($value);
        foreach($values AS $value) {
            if($value != '' AND !preg_match("/^[A-Za-z0-9._%\-+]+@[A-Za-z0-9.\-]+\.[A-Za-z]{2,11}$/", $value)) {
                $this->error = $this->PMDR->getLanguage('messages_email_format');
                return false;
            }
        }
        return true;
    }
}

/**
* Validate a string contains less than a word limit
*/
class Validate_Word_Count {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Word limit
    * @var int
    */
    var $limit;

    /**
    * Get the registry instance and set the limit
    * @param int $limit
    * @return Validate_Word_Count
    */
    function __construct($limit) {
        $this->limit = $limit;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Count the words and compare against the limit
    * @param string $value
    * @return boolean
    */
    function validate($value) {
         if(Strings::count_words($value) > $this->limit AND !empty($value)) {
            $this->error = $this->PMDR->getLanguage('messages_word_count_limit');
            return false;
         }
         return true;
    }
}

/**
* Validate an image against multiple criteria
*/
class Validate_Image {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Maximum width
    * @var int
    */
    var $width;
    /**
    * Maximum height
    * @var int
    */
    var $height;
    /**
    * Maximum filesize
    * @var int
    */
    var $filesize;
    /**
    * Formats accepted
    * @var array|string
    */
    var $formats;
    /**
    * Allow animated GIF files
    * @var boolean
    */
    var $allow_animated;
    /**
    * Force exact dimensions
    * @var boolean
    */
    var $force_dimensions;

    /**
    * Get the registry instance and set the default parameters.
    * If the format is not an array, convert it into an array.
    * @param int $width
    * @param int $height
    * @param int $filesize
    * @param array|string $formats
    * @param boolean $allow_animated
    * @param boolean $force_dimensions
    * @return Validate_Image
    */
    function __construct($width, $height, $filesize, $formats, $allow_animated = false, $force_dimensions = false, $required = false) {
        $this->PMDR = Registry::getInstance();
        $this->width = $width;
        $this->height = $height;
        $this->filesize = $filesize;
        $this->required = $required;
        if(!is_array($formats)) {
            $formats = array_filter(explode(',',$formats));
        }
        $this->formats = array_map('strtolower',$formats);
        $this->force_dimensions = $force_dimensions;
        $this->allow_animated = $allow_animated;
    }

    /**
    * Validate the image against multiple criteria
    * @param mixed $value
    * @return boolean
    */
    function validate($value) {
        if(empty($value)) {
            if($this->required) {
                $this->error = $this->PMDR->getLanguage('messages_required');
                return false;
            } else {
                return true;
            }
        }

        $image_handler = $this->PMDR->get('Image_Handler');

        // Check if we have a valid image
        if($image_handler->verifyImage($value)) {
            // If animated is not allowed, but image is animated, return false
            if($this->allow_animated == false AND $image_handler->animated) {
                $this->error = $this->PMDR->getLanguage('messages_images_animation_error');
                return false;
            // If the image dimensions are not valid, return false
            } elseif(($image_handler->getCurrentWidth() > $this->width OR $image_handler->getCurrentHeight() > $this->height) AND ($this->force_dimensions OR ($this->allow_animated AND $image_handler->animated))) {
                $this->error = $this->PMDR->getLanguage('messages_images_dimensions_error',array($this->width,$this->height));
                return false;
            // If the filesize is too big, return false
            } elseif(empty($image_handler->file_size) OR $image_handler->file_size/1024 > $this->filesize) {
                $this->error = $this->PMDR->getLanguage('messages_image_size_error',array($this->filesize));
                return false;
            // Valid image
            } else {
                return true;
            }
        } else {
            $this->error = $this->PMDR->getLanguage('messages_images_type_error',implode(',',$this->formats));
            return false;
        }
    }
}

/**
* Validate a string matches a captcha value
*/
class Validate_Captcha {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance
    * @return Validate_Captcha
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a string depending on the captcha type
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(!$this->PMDR->get('Captcha')->validate($value)) {
            $this->error = $this->PMDR->getLanguage('messages_captcha_error');
            return false;
        }
        return true;
    }
}

/**
* Validate a string as a date
*/
class Validate_Date {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance and set the required flag
    * @param boolean $required
    * @return Validate_Date
    */
    function __construct($required = true) {
        $this->required = $required;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a string against a date regex
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^[0-9]{2,4}.{1}[0-9]{2,4}.{1}[0-9]{2,4}$/',$value) OR (!$this->required AND $value == '')) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_date_validation_error');
            return false;
        }
    }
}

/**
* Validate a string as date time format
*/
class Validate_DateTime {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance and set the required flag
    * @param boolean $required
    * @return Validate_DateTime
    */
    function __construct($required = true) {
        $this->required = $required;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a string as a date time value
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^[0-9]{4}-{1}[0-9]{2}-{1}[0-9]{2}( [0-9]{2}:[0-9]{2}:[0-9]{2})?$/',$value) OR (!$this->required AND $value == '')) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_date_validation_error');
            return false;
        }
    }
}

/**
* Validate a date range
*/
class Validate_Date_Range {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Minimum date in range
    * @var int
    */
    var $minimum;
    /**
    * Maximum date in range
    * @var int
    */
    var $maximum;

    /**
    * Get the registry instance and set the required flag
    * @param boolean $required
    * @return Validate_Date_Range
    */
    function __construct($minimum = null, $maximum = null) {
        $this->PMDR = Registry::getInstance();
        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    /**
    * Validate a date against a range
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if($this->minimum !== null AND $this->maximum !== null AND (strtotime($value) < strtotime($this->minimum) OR strtotime($value) > strtotime($this->maximum))) {
            $this->error = $this->PMDR->getLanguage('messages_date_range_validation_error',$this->PMDR->get('Dates_Local')->formatDate($this->minimum),$this->PMDR->get('Dates_Local')->formatDate($this->maximum));
            return false;
        }
        if($this->minimum !== null AND strtotime($value) < strtotime($this->minimum)) {
            $this->error = $this->PMDR->getLanguage('messages_date_range_minimum_validation_error',$this->PMDR->get('Dates_Local')->formatDate($this->minimum));
            return false;
        }
        if($this->maximum !== null AND strtotime($value) > strtotime($this->maximum)) {
            $this->error = $this->PMDR->getLanguage('messages_date_range_maximum_validation_error',$this->PMDR->get('Dates_Local')->formatDate($this->maximum));
            return false;
        }
        return true;
    }
}

/**
* Validate a string as a price
*/
class Validate_Price {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;

    /**
    * Get the registry instance
    * @return Validate_Price
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the stirng as a price by comparing against all valid price characters
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^[0-9'.$this->PMDR->getLanguage('thousandseperator').$this->PMDR->getLanguage('decimalseperator').$this->PMDR->getLanguage('currency_prefix').$this->PMDR->getLanguage('currency_suffix').'\.]+$/',$value)) {
            return true;
        } else {
            $this->error = 'The price entered is incorrectly formatted.';
            return false;
        }
    }
}

/**
* Validate a string as a friedly URL
*/
class Validate_Friendly_URL {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = true;

    /**
    * Get the registry instance and set the required flag
    * @param boolean $required
    * @return Validate_Friendly_URL
    */
    function __construct($required = true) {
        $this->PMDR = Registry::getInstance();
        $this->required = $required;
    }

    /**
    * Validate the string as a friendly URL based on a set of rewrite allowable characters
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $allowed_characters = $this->PMDR->getConfig('rewrite_characters');
        if(strstr($allowed_characters,'*')) {
            $regex_characters = '\p{L}';
        } else {
            $regex_characters = '0-9A-Za-z\-';
        }
        $regex_characters .= preg_quote(str_replace(',','',$allowed_characters),'/');
        if(preg_match('/^['.$regex_characters.']+$/iu',$value)) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_friendly_url_validation_error');
            return false;
        }
    }
}

/**
* Validate a value as being numeric
*/
class Validate_Numeric {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;

    /**
    * Get the registry instance
    * @return Validate_Numeric
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a value as being numeric
    * @param string|int $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^(\d+)(\.\d+)?$/',$value) OR $value == '') {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_numeric_validation_error');
            return false;
        }
    }
}

/**
* Validate a value as being currency
*/
class Validate_Currency {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;

    var $raw = true;

    /**
    * Get the registry instance
    * @return Validate_Currency
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a value as being a currency
    * @param string|int $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^[\d'.preg_quote($this->PMDR->getLanguage('thousandseperator')).']+'.preg_quote($this->PMDR->getLanguage('decimalseperator')).'\d{'.$this->PMDR->getLanguage('decimalplaces').'}$/',$value) OR $value == '') {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_currency_validation_error',array('x'.$this->PMDR->getLanguage('thousandseperator').'xxx'.$this->PMDR->getLanguage('decimalseperator').str_repeat('x',intval($this->PMDR->getLanguage('decimalplaces'))),'%s'));
            return false;
        }
    }
}

/**
* Validate a number is within a range
*/
class Validate_Numeric_Range {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Minimum number in range
    * @var int
    */
    var $minimum;
    /**
    * Maximum number in range
    * @var int
    */
    var $maximum;

    /**
    * Get the registry instance and set the min and max limits
    * @param int $minimum
    * @param int $maximum
    * @return Validate_Numeric_Range
    */
    function __construct($minimum,$maximum) {
        $this->PMDR = Registry::getInstance();
        $this->minimum = $minimum;
        $this->maximum = $maximum;
    }

    /**
    * Validate a number is within a range
    * @param int $value
    * @return boolean
    */
    function validate($value) {
        if(($value >= $this->minimum AND $value <= $this->maximum) OR $value == '') {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_numeric_range_error',array($this->minimum,$this->maximum,'%s'));
            return false;
        }
    }
}

/**
* Validate a string is in URL format
*/
class Validate_URL {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Require the value to be non-empty
    * @var boolean
    */
    var $required = false;

    /**
    * Get the registry instance and set the required flag
    * @param boolean $required
    * @return Validate_URL
    */
    function __construct($required = false) {
        $this->PMDR = Registry::getInstance();
        $this->required = $required;
    }

    /**
    * Check if a string is a valid URL
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(valid_url($value) OR ($value == '' AND !$this->required)) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_url_validation_error');
            return false;
        }
    }
}

/**
* Validate a string is in phone number format
*/
class Validate_Phone_Number {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;

    /**
    * Get the registry instance
    * @return Validate_Phone_Number
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate a string against a phone number regex
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match('/^((\+{0,1}\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/',$value) OR $value == '') {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_phone_validation_error');
            return false;
        }
    }
}

/**
* Validate a string against a maximum length
*/
class Validate_Length {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Maximum length
    * @var int
    */
    var $length;

    /**
    * Get the registry instance and set the maximum length
    * @param int $length
    * @return Validate_Length
    */
    function __construct($length) {
        $this->length = $length;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the value against a maximum length after cleaning and stripping any HTML
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(($current_length = Strings::strlen(preg_replace('/[\r\n]+/','',strip_tags($this->PMDR->get('Cleaner')->unclean_html($value))))) <= $this->length) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_length_validation_error',array($current_length,$this->length,'%s'));
            return false;
        }
    }
}

/**
* Validate a string against a maximum length
*/
class Validate_Length_Range {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Minimum length
    * @var int
    */
    var $min;
    /**
    * Maximum length
    * @var int
    */
    var $max;

    /**
    * Get the registry instance and set the maximum length
    * @param int $length
    * @return Validate_Length
    */
    function __construct($min, $max = null) {
        $this->min = $min;
        $this->max = $max;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the value against a maximum length after cleaning and stripping any HTML
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $current_length = Strings::strlen(preg_replace('/[\r\n]+/','',strip_tags($this->PMDR->get('Cleaner')->unclean_html($value))));
        if($current_length >= $min AND (is_null($max) OR $current_length <= $max)) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_length_range_validation_error',array($current_length,$this->min,$this->max,'%s'));
            return false;
        }
    }
}

/**
* Validate the value against a custom regex
*/
class Validate_Regex {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Regular expression
    *
    * @var mixed
    */
    var $regex;
    /**
    * Custom error message
    * @var string
    */
    var $error_message;

    /**
    * Get the registry instance and set the regex and error message
    * @param string $regex
    * @param string $error_message
    * @return Validate_Regex
    */
    function __construct($regex, $error_message) {
        $this->regex = $regex;
        $this->error_message = $error_message;
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the string against a custom regex value
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        if(preg_match($this->regex,$value) OR $value == '') {
            return true;
        } else {
            $this->error = $this->error_message;
            return false;
        }
    }
}

/**
* Validate a currency code
*/
class Validate_Currency_Code {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;

    /**
    * Get the registry instance
    * @return Validate_Currency_Code
    */
    function __construct() {
        $this->PMDR = Registry::getInstance();
    }

    /**
    * Validate the value against a known list of currency codes
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $currencies = include(PMDROOT.'/includes/currencies.php');
        $currencies = array_map('strtolower',$currencies);
        if($value != '' OR array_search(strtolower($value),$currencies)) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_currency_code_validation_error');
            return false;
        }
    }
}

/**
* Validate image tag limit
*/
class Validate_Image_Tag_Limit {
    /**
    * Registry
    * @var Registry
    */
    var $PMDR;
    /**
    * Error message
    * @var string
    */
    var $error;
    /**
    * Tag limit
    * @var int
    */
    var $limit;

    /**
    * Get the registry instance
    * @return Validate_Image_Tag_Limit
    */
    function __construct($limit) {
        $this->PMDR = Registry::getInstance();
        $this->limit = $limit;
    }

    /**
    * Check the value for a maximum number of image tags
    * @param string $value
    * @return boolean
    */
    function validate($value) {
        $matches = array();
        $image_tag_count = preg_match_all("/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/si",$value,$matches);
        if($image_tag_count <= $this->limit) {
            return true;
        } else {
            $this->error = $this->PMDR->getLanguage('messages_image_tag_validation_error',array($this->limit,'%s'));
            return false;
        }
    }
}
?>