<?php
class Authentication_UBBThreads extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT USER_LOGIN_NAME AS login, USER_PASSWORD AS pass, USER_REGISTRATION_EMAIL AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."USERS WHERE USER_LOGIN_NAME=? AND USER_PASSWORD=?",array($this->username,md5($this->password)));
    }
}
?>