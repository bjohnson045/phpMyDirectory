<?php
class Authentication_phpMyDirectory extends Authentication_Module {
    function loadModuleUser() {
        $user = $this->db->GetRow("SELECT id, login, password_salt, pass, user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE login=?",array($this->username));
        if($this->encryptPassword($this->password,$user['password_salt']) == $user['pass']) {
            return $user;
        } else {
            return false;
        }
    }
}
?>