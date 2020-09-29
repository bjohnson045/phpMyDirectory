<?php
class Authentication_phpBB3 extends Authentication_Module {
    function loadModuleUser() {
        $user = $this->db->GetRow("SELECT username AS login, user_password AS pass, user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE username=?",array($this->username));
        if(strlen($user['pass']) <= 32 ) {
            if(md5($this->password) != $user['pass']) {
                return false;
            }
        } else {
            require_once(PMDROOT.'/modules/login/phpBB3/PasswordHash.php');
            $phpbb_hasher = new PasswordHash(6, TRUE);
            if(!$phpbb_hasher->CheckPassword($this->password, $user['pass'])) {
                return false;
            }
        }
        return $user;
    }
}
?>