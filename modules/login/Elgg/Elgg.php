<?php
class Authentication_Elgg extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT username AS login, password AS pass, salt, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users_entity WHERE username=? AND password=MD5(CONCAT(?,salt))",array($this->username,$this->password));
    }
}
?>