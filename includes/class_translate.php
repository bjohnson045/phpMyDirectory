<?php
/**
* Translate class
*/
class Translate {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Translate constructor
    * @param object $PMDR
    * @return Translate
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }
}

/**
* Google Translate
*/
class Google_Translate extends Translate {
    /**
    * Language code lookup array
    * @var array
    */
    var $language_reference = array(
        'ar'=>'ARABIC',
        'bg'=>'BULGARIAN',
        'ca'=>'CATALAN',
        'zh'=>'CHINESE',
        'zh-CN'=>'CHINESE_SIMPLIFIED',
        'zh-TW'=>'CHINESE_TRADITIONAL',
        'hr'=>'CROATIAN',
        'cs'=>'CZECH',
        'da'=>'DANISH',
        'nl'=>'DUTCH',
        'en'=>'ENGLISH',
        'et'=>'ESTONIAN',
        'tl'=>'FILIPINO',
        'fi'=>'FINNISH',
        'fr'=>'FRENCH',
        'de'=>'GERMAN',
        'el'=>'GREEK',
        'iw'=>'HEBREW',
        'hi'=>'HINDI',
        'hu'=>'HUNGARIAN',
        'id'=>'INDONESIAN',
        'it'=>'ITALIAN',
        'ja'=>'JAPANESE',
        'ko'=>'KOREAN',
        'lv'=>'LATVIAN',
        'lt'=>'LITHUANIAN',
        'no'=>'NORWEGIAN',
        'fa'=>'PERSIAN',
        'pl'=>'POLISH',
        'pt-PT'=>'PORTUGUESE',
        'ro'=>'ROMANIAN',
        'ru'=>'RUSSIAN',
        'sr'=>'SERBIAN',
        'sk'=>'SLOVAK',
        'sl'=>'SLOVENIAN',
        'es'=>'SPANISH',
        'sv'=>'SWEDISH',
        'th'=>'THAI',
        'tr'=>'TURKISH',
        'uk'=>'UKRAINIAN',
        'vi'=>'VIETNAMESE'
    );

    /**
    * Translate text from one language to another
    * @param string $text Text to translate
    * @param string $from Language to translate from
    * @param string $to Language to translate to
    * @return string Translated text
    */
    function translate($text, $from, $to) {
        // If the language $to or $from is not supported by the translating service we quit
        if(!array_key_exists($to,$this->language_reference) OR !array_key_exists($from, $this->language_reference)) {
            return false;
        }
        $url = 'https://www.googleapis.com/language/translate/v2?q='.urlencode($text).'&target='.$to.'&source='.$from.'&key='.$this->PMDR->getConfig('google_server_apikey');
        $http = $this->PMDR->get('HTTP_Request');
        $http->settings[CURLOPT_RETURNTRANSFER] = true;
        $http->settings[CURLOPT_REFERER] = BASE_URL;
        $result = $http->get('curl',$url);
        $result = json_decode($result,true);
        if(!isset($result['data']['translations'])) {
            return '';
        } else {
            return $result['data']['translations'][0]['translatedText'];
        }
    }

    /**
    * Check the google API key
    * @return int API key status code
    */
    function checkApiKey() {
        // Curl google for a list of languages. (Free request.)
        $url = 'https://www.googleapis.com/language/translate/v2/languages?key='.$this->PMDR->getConfig('google_server_apikey');
        $http = $this->PMDR->get('HTTP_Request');
        $http->settings[CURLOPT_RETURNTRANSFER] = true;
        $http->settings[CURLOPT_REFERER] = BASE_URL;
        $result = $http->get('curl',$url);
        $data = json_decode($result,true);

        // Check what the result is, we should be able to determine
        // if the key is valid or not before running through all of
        // the translations with a bad key.

        // Null, couldn't connect.
        if($data === null) {
            return self::KEY_TIMEOUT;
        }

        // No error code, probably good...
        if(empty($data['error']['code'])) {
            if (!empty($data['data']))  {
                // No error code, and it looks like we have data.
                // it should be working.
                return self::KEY_OKAY;
            } else {
                // No error, but no data?
                return self::KEY_UNKNOWN_1;
            }
        }

        if($data['error']['code'] == 400) {
            // This occurs when the provided key is invalid.
            // Often a result of using an old google maps api key.
            return self::KEY_INVALID;
        }

        if($data['error']['code'] == 403) {
            // Key is a valid console key, but language is not enabled.
            return self::KEY_DISABLED;
        }

        // Unknown result...
        return self::KEY_UNKNOWN_2;
    }

    // Key Check Results
    const KEY_OKAY = 0;
    const KEY_DISABLED = 10;
    const KEY_INVALID = 20;
    const KEY_UNKNOWN_1 = 40;
    const KEY_UNKNOWN_2 = 41;
    const KEY_TIMEOUT = 60;
}
?>