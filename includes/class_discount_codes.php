<?php
/**
* Discount Codes class
*/
class Discount_Codes extends TableGateway{
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Discount Codes constructor
    * @param object Registry
    */
    function __construct($PMDR) {
        $this->table = T_DISCOUNT_CODES;
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    /**
    * Get associative array of formatted discount codes
    * Used for select fields
    * @return array
    */
    function getFormattedCodes() {
        return $this->db->GetAssoc("SELECT code, CONCAT(title,' (',code,')') AS value FROM ".T_DISCOUNT_CODES." ORDER BY title ASC");
    }

    /**
    * Expire an existing discount code
    * @param int $id Discount code ID
    * @return resource
    */
    function expire($id) {
        return $this->db->Execute("UPDATE ".T_DISCOUNT_CODES." SET date_expire=DATE_SUB(NOW(),INTERVAL 1 MINUTE) WHERE id=?",array($id));
    }

    /**
    * Check if a code exists
    * @param string $code
    * @param int $id
    * @return resource
    */
    function codeExists($code, $id = null) {
        if(!is_null($id)) {
            return $this->db->GetOne("SELECT COUNT(*) FROM ".T_DISCOUNT_CODES." WHERE code=? AND id!=?",array($code,$id));
        } else {
            return $this->db->GetOne("SELECT COUNT(*) FROM ".T_DISCOUNT_CODES." WHERE code=?",array($code));
        }
    }

    /**
    * Store a discount code in a session variable from a URL parameter
    * @return  boolean True is value stored
    */
    function setURLCode() {
        if(isset($_GET['discount_code'])) {
            $valid = $this->db->GetOne("SELECT COUNT(*) FROM ".T_DISCOUNT_CODES." WHERE code=?",array($_GET['discount_code']));
            if($valid) {
                $this->PMDR->get('Session')->set('discount_code',$_GET['discount_code']);
                return true;
            }
        }
        return false;
    }

    /**
    * Get a session stored discount code
    * @return string Discount code
    */
    function getURLCode() {
        return $this->PMDR->get('Session')->get('discount_code');
    }

    function getDisplay() {
        $codes = $this->db->GetAll("SELECT title, code, type, discount_type, date_expire, value FROM ".T_DISCOUNT_CODES." WHERE display=1 ORDER BY title");

        foreach($codes AS &$code) {
            if($code['type'] == 'fixed') {
                $code['formatted_value'] = format_number_currency($code['value']);
            } else {
                $code['formatted_value'] = $code['value'].'%';
            }
            $code['date_expire'] = $this->PMDR->get('Dates_Local')->formatDate($code['date_expire']);
            $code['description'] = $this->PMDR->getLanguage('public_compare_available_discounts_description',array($code['code'],$code['formatted_value'],$code['type'],$code['date_expire']));
        }
        return $codes;
    }
}
?>