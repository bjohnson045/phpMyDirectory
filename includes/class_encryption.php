<?php
/**
* Encryption class
* Calls encryption methods of desired algorithm
*/
class Encryption {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
     * Encryption Key
     * @var string
     */
    var $key;

    /**
    * Encryption class constructur
    * @param object $PMDR Registry
    * @return Encryption
    */
    function __construct($PMDR,$key = null) {
        $this->PMDR = $PMDR;
        if(!is_null($key)) {
            $this->key = $key;
        }
    }

    /**
    * Encryption wrapper
    * @param string $input String to encrypt
    * @param string $key Key to use for encryption
    * @return decrypted
    */
    function encrypt($string, $key = null) {
        if(is_null($key)) {
            $key = $this->key;
        }
        return AesCtr::encrypt($string, $key, 256);
    }

    /**
    * Decryption wrapper
    * @param string $input String to dencrypt
    * @param string $key Key to use for dencryption
    * @return decrypted
    */
    function decrypt($string, $key = null) {
        if(is_null($key)) {
            $key = $this->key;
        }
        return AesCtr::decrypt($string, $key, 256);
    }
}
// Remove MCrypt
// Use OpenSSL encryption instead
/**
* Encryption class implementing PHP's myscrypt (AES 256)
*/
class Encryption_MCrypt {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Encryption Key
    * @var string
    */
    var $key;
    /**
    * IV size
    * @var int
    */
    var $iv_size;

    /**
    * Encryption class constructur
    * @param object $PMDR Registry
    * @return Encryption
    */
    function __construct($PMDR, $key = null) {
        $this->PMDR = $PMDR;
        $this->iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        if(!is_null($key)) {
            $this->key = $key;
        }
    }

    /**
    * Encryption wrapper
    * @param string $input String to encrypt
    * @param string $key Key to use for encryption
    * @return decrypted
    */
    function encrypt($string, $key = null) {
        if(is_null($key)) {
            $key = $this->key;
        }
        $string = trim($string);
        $iv = mcrypt_create_iv($this->iv_size, MCRYPT_RAND);
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256,$key,$iv.$string,MCRYPT_MODE_CBC,$iv));
    }

    /**
    * Decryption wrapper
    * @param string $input String to dencrypt
    * @param string $key Key to use for dencryption
    * @return decrypted
    */
    function decrypt($string, $key = null) {
        if(is_null($key)) {
            $key = $this->key;
        }
        $string = base64_decode($string);
        $iv = substr($string,0,$this->iv_size);
        $string = substr($string, $this->iv_size);
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256,$key,$string,MCRYPT_MODE_CBC,$iv));
    }
}
?>