<?php
/**
* Class Captcha
* Base class for Captcha implementations
*/
abstract class Captcha {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * Settings for the specific gateway
    * @var mixed
    */
    var $settings;

    /**
    * Captcha Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->loadSettings();
        $this->initialize();
    }

    abstract protected function initialize();
    abstract public function loadJavascript();
    abstract public function getHTML($field_attributes = '');
    abstract public function validate($value);

    /**
    * Load Captcha settings from database
    * @return void
    */
    protected function loadSettings() {
        $this->settings = $this->db->GetRow("SELECT id, settings FROM ".T_CAPTCHAS." WHERE id=?",array(get_class($this)));
        if(!empty($this->settings['settings'])) {
            if($settings = unserialize($this->settings['settings'])) {
                $this->settings = array_merge($this->settings,$settings);
            } else {
                trigger_error('Unable to unserialize captcha settings.',E_USER_WARNING);
            }
        }
    }
}
?>