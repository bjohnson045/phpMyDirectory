<?php
class Authentication_PunBB extends Authentication_Module {
    function loadModuleUser() {
        if(!$user = $this->db->GetRow("SELECT username AS login, password AS pass, salt, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE username=?",array($this->username))) {
            return false;
        }
        if(strlen($user['pass']) == 40) {
            if($user['pass'] != sha1($user['salt'].sha1($this->password))) {
                return false;
            }
        } elseif($user['pass'] != sha1($this->password) AND $user['pass'] != md5($this->password)) {
            return false;
        }
        return $user;
    }
}
?>