<?php
class Image extends Captcha {
    function initialize() {}
    function loadJavascript() {}

    function getHTML($field_attributes = array()) {
        if(!$this->PMDR->get('Session')->get('security_code')) {
            if($this->settings['type'] = 'textnumbers') {
                $string = Strings::random(4,false,true,true,str_split('I10O'));
            } else {
                $string = Strings::random(4,false,false,true,str_split('I10O'));
            }
            $this->PMDR->get('Session')->set('security_code',$string);
        }
        $template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/form_captcha_image.tpl');
        $template->set('attributes',HTML::attributesString('input',$field_attributes,array('class')));
        $template->set('class',implode(' ',$field_attributes['class']));
        $template->set('random',Strings::random(8));
        return $template->render();
    }

    function validate($value) {
        $code = $this->PMDR->get('Session')->get('security_code');
        $this->PMDR->get('Session')->delete('security_code');
        if(strtolower($code) != strtolower($value)) {
            $this->error = $this->PMDR->getLanguage('messages_captcha_error');
            return false;
        } else {
            return true;
        }
    }
}

?>