<?php
class Authentication_vBulletin extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT username AS login, password AS pass, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."user WHERE username=? AND password=MD5(CONCAT(?,salt))",array($this->username,md5($this->password)));
    }
}
?>