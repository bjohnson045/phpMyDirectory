<?php
class reCaptcha extends Captcha {
    function initialize() {
        require_once(PMDROOT.'/modules/captcha/reCaptcha/library/autoload.php');
    }

    function loadJavascript() {
        $lang = substr($this->PMDR->getLanguage('languagecode'),0,2);
        $javascript = '<script type="text/javascript" src="https://www.google.com/recaptcha/api.js?hl='.$lang.'"></script>';
        $this->PMDR->loadJavascript($javascript);
    }

    function getHTML($field_attributes = array()) {
        return '<div class="g-recaptcha" data-type="'.$this->settings['type'].'" data-size="'.$this->settings['size'].'" data-theme="'.$this->settings['theme'].'" data-sitekey="'.$this->settings['public_key'].'"></div>';
    }

    function validate($value) {
        $recaptcha = new \ReCaptcha\ReCaptcha($this->settings['private_key']);
        $resp = $recaptcha->verify($_POST['g-recaptcha-response'],get_ip_address());
        if(!$resp->isSuccess()) {
            $this->error = $this->PMDR->getLanguage('messages_captcha_error');
            return false;
        } else {
            return true;
        }
    }
}
?>