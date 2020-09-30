<?php
class nuCaptcha extends Captcha {
    function initialize() {
        require_once(PMDROOT.'/modules/captcha/nuCaptcha/library/leapmarketingclient.php');
        Leap::SetClientKey($this->settings['client_key']);
    }

    function loadJavascript() {
        $this->PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function(){$("#nucaptcha-widget").css("display","inline-block");});</script>',100);
    }

    function getHTML($field_attributes = array()) {
        $supported_languages = array(
            'en'=>'eng',
            'fr'=>'fre',
            'de'=>'deu',
            'sp'=>'spa',
            'it'=>'ita',
            'ru'=>'rus',
            'zh'=>'zho'
        );
        $current_language_code = substr($this->PMDR->getLanguage('languagecode'),0,2);
        $language_code = 'eng';
        if(isset($supported_languages[$current_language_code])) {
            $language_code = $supported_languages[$current_language_code];
        }
        $transaction = Leap::InitializeTransaction(null,SSL_ON,null,Leap::PURPOSE_UNKNOWN,$language_code);

        if(LMSC_OK !== Leap::GetStatusCode()) {
            trigger_error('nuCaptcha Error: '.Leap::GetStatusString());
        }
        $_SESSION['leap'] = $transaction->GetPersistentData();
        return $transaction->GetWidget();
    }

    function validate($value) {
        if(true === array_key_exists('leap', $_SESSION) AND true === Leap::WasSubmitted()) {
            $valid = Leap::ValidateTransaction($_SESSION['leap']);

            if(Leap::GetStatusCode() != LMSC_CORRECT && Leap::GetStatusCode() != LMSC_WRONG && Leap::GetStatusCode() != LMSC_EMPTY) {
                trigger_error('nuCaptcha Error ('.Leap::GetStatusCode().'): '.Leap::GetStatusString());
            }
            if($valid === true) {
                return true;
            }
        }
        return false;
    }
}
?>