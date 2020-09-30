<?php
class Authentication_Joomla extends Authentication_Module {
    function loadModuleUser() {
        $user = $this->db->GetRow("SELECT username AS login, user_password AS pass, user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE username=?",array($this->username));
        if($user) {
            $pass = explode(':',$user['pass']);
            if(count($pass) == 2 AND $pass[0] <= 32 ) {
                if($pass[0] == md5($this->password.$pass[1])) {
                    return $user;
                }
            } else {
                require_once(PMDROOT.'/modules/login/Joomla/PasswordHash.php');
                $joomla_hasher = new PasswordHash(10, TRUE);
                if($joomla_hasher->CheckPassword($this->password, $user['pass'])) {
                    return $user;
                }
            }
        }
        return false;
    }
}
?>