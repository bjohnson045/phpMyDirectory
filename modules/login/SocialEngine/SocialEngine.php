<?php
class Authentication_SocialEngine extends Authentication_Module {
    function loadModuleUser() {
        $secret = $this->db->GetOne("SELECT value FROM ".$this->PMDR->getConfig('login_module_db_prefix')."core_settings WHERE name='core.secret'");
        if(!$user = $this->db->GetRow("SELECT username AS login, password AS pass, email AS user_email, salt FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE username=? OR email=?",array($this->username,$this->username))) {
            return false;
        }
        if(md5($secret.$this->password.$user['salt']) != $user['pass']) {
            return false;
        }
        return $user;
    }
}
?>