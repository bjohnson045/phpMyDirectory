<?php
/**
* Class SMS
* Base class for SMS handling
*/
abstract class SMS {
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
    * SMS Constructor
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
    abstract public function sendMessage($numbers, $message, $from = null);

    /**
    * Load SMS settings from database
    * @return void
    */
    protected function loadSettings() {
        $this->settings = $this->db->GetRow("SELECT id, settings FROM ".T_SMS_GATEWAYS." WHERE id=?",array(get_class($this)));
        if(!empty($this->settings['settings'])) {
            if($settings = unserialize($this->settings['settings'])) {
                $this->settings = array_merge($this->settings,$settings);
            } else {
                trigger_error('Unable to unserialize SMS settings.',E_USER_WARNING);
            }
        }
    }

    /**
     * This function will normalize a phone number to E164 optionally converting letters to numbers.
     * False is returned if no recognized number formats exist.
     */
    protected function normalize_E164($phone, $convert_letters = true) {
        if ($convert_letters) {
            $phone = strtr(strtolower($phone), 'abcdefghijklmnopqrstuvwxyz', '22233344455566677778889999');
        }

        // get rid of any non (digit, + character)
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // validate intl 10
        if(preg_match('/^\+([2-9][0-9]{9})$/', $phone, $matches)){
            return "+{$matches[1]}";
        }

        // validate US DID
        if(preg_match('/^\+?1?([2-9][0-9]{9})$/', $phone, $matches)){
            return "+1{$matches[1]}";
        }

        // validate INTL DID
        if(preg_match('/^\+?([2-9][0-9]{8,14})$/', $phone, $matches)){
            return "+{$matches[1]}";
        }

        // premium US DID
        if(preg_match('/^\+?1?([2-9]11)$/', $phone, $matches)){
            return "+1{$matches[1]}";
        }

        return false;
    }

    /**
     * This function will take an E.164 formatted number and put spaces
     * in between each digit so that Twilio's <Say> reads it like a human would.
     */
    protected function spaceNumber($number) {
        $number = trim($number);
        $number = preg_replace("/[^A-Za-z0-9]/", '', $number);
        $number = str_split($number);
        $number = implode(' ', $number);
        return $number;
    }

}
?>