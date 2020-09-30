<?php
class Users_API {
    function __construct($PMDR, $parameters = array()) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
    }

    function get($parameters) {
        if(!count($parameters) OR !is_numeric($parameters[0])) {
            return "No ID provided.";
        } else {
            return $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE id=?",array($parameters[0]));
        }
    }

    function getByEmail($parameters) {
        return $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE user_email=?",array($parameters[0]));
    }
}
?>