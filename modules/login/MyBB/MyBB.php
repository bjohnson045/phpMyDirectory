<?php
class Authentication_MyBB extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT username AS login, password AS pass, salt, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE (username=? OR email=?) AND password=MD5(CONCAT(MD5(salt),?))",array($this->username,$this->username,md5($this->password)));
    }
}
?>