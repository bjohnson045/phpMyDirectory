<?php
/**
* Enchant Spell Class
* Checks spelling using PHP Enchant (requires PHP 5.3)
*/
class Spell_Enchant {

    function __construct() {}

    /**
    * Gets suggestions for a string of words
    * @param string $query
    * @param string $lang
    * @return array
    */
    function getSuggestions($query, $lang='en_US') {
        if(!function_exists('enchant_broker_init')) {
            trigger_error('Enchant spelling library not available.',E_USER_WARNING);
            return array();
        }

        $suggestions = array();
        if(!$enchant = enchant_broker_init()) {
            trigger_error('Unable to initialize Enchannt.'.$lang,E_USER_WARNING);
        }
        if(enchant_broker_dict_exists($enchant,$lang)) {
            $dictionary = enchant_broker_request_dict($enchant, $lang);
            enchant_dict_quick_check($dictionary, $query, $suggestions);
        } else {
            trigger_error('Enchant spelling library not available for language '.$lang,E_USER_WARNING);
        }
        return $suggestions;
    }

    /**
    * Gets replacement suggestion for entire string
    * @param string $query
    * @param string $lang
    * @return string
    */
    function getSuggested($query, $lang='en_US') {
        $words = preg_split('/[\W]+/',$query);
        $new_suggestion = $query;
        foreach($words AS $word) {
            $suggestions = $this->getSuggestions($word, $lang);
            $new_suggestion = str_replace($word,$suggestions[0],$new_suggestion);
        }

        if($new_suggestion == $query) {
            return false;
        }

        return $new_suggestion;
    }
}
?>