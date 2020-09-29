<?php
class Authentication_SMF extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT member_name AS login, passwd AS pass, email_address AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."members WHERE member_name=? AND passwd=?",array($this->username,sha1(strtolower($this->username).$this->password)));
    }
}
?>