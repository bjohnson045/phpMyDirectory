<?php

// In Moodle/config.php there is a password salt value that needs to be copied to this file.
// This salt will be auto-generated during installation.
// $CFG->passwordsaltmain should be copied to $_MoodleSaltMain below.

class Authentication_Moodle extends Authentication_Module {
    function loadModuleUser() {
        $_MoodleSaltMain = '';

        if(!$user = $this->db->GetRow("SELECT username AS login, password AS pass, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."user WHERE username=? OR email=?",array($this->username,$this->username))) {
            return false;
        }

        $salt = substr($user['pass'],0,29);
        //exit($salt);
        if(crypt($this->password,$salt) != $user['pass']) {
            return false;
        }
        //echo crypt($this->password,$salt).' = '.$user['pass'];

        //if(md5($this->password.$_MoodleSaltMain) != $user['pass']) {
        //    return false;
        //}
        return $user;
    }
}
?>