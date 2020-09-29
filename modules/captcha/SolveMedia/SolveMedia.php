<?php
class SolveMedia extends Captcha {
    function initialize() {
        require_once(PMDROOT.'/modules/captcha/SolveMedia/library/solvemedialib.php');
    }

    function loadJavascript() {
        if(empty($this->settings['theme'])) {
            $this->settings['theme'] = 'white';
        }
        $javascript = '<script type="text/javascript">$(document).ready(function(){$("#adcopy-outer").css("display","inline-block");});var ACPuzzleOptions = {';
        $supported_languages = array('en','de','fr','es','it','yi','ja','ca','pl','hu','sv','no','pt','nl','tr');
        $current_language_code = substr($this->PMDR->getLanguage('languagecode'),0,2);
        if(in_array($current_language_code,$supported_languages)) {
            $javascript .= 'lang: \''.$current_language_code.'\',';
        }
        $javascript .= 'theme: \''.$this->settings['theme'].'\'';
        $javascript .= '};</script>';
        $this->PMDR->loadJavascript($javascript,100);
    }

    function getHTML($field_attributes = array()) {
        if(!empty($this->settings['challenge_key'])) {
            return solvemedia_get_html($this->settings['challenge_key'],null,SSL_ON);
        } else {
            return false;
        }
    }

    function validate($value) {
        $solvemedia_response = solvemedia_check_answer($this->settings['verification_key'],get_ip_address(),$_POST["adcopy_challenge"],$_POST["adcopy_response"],$this->settings['hash_key']);
        return $solvemedia_response->is_valid;
    }
}
?>