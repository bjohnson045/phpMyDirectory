<?php
class Authentication_Wordpress extends Authentication_Module {
    function loadModuleUser() {
        if(!$wordpress_user = $this->db->GetRow("SELECT user_login AS login, user_pass AS pass, user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."_users WHERE user_login=?",array($this->username))) {
            return false;
        }
        if(strlen($wordpress_user['pass']) <= 32 ) {
            if(md5($this->password) != $wordpress_user['pass']) {
                return false;
            }
        } else {
            require_once(PMDROOT.'/modules/login/Wordpress/class-phpass.php');
            $wp_hasher = new PasswordHash(8, TRUE);
            if(!$wp_hasher->CheckPassword($this->password, $wordpress_user['pass'])) {
                return false;
            }
        }
        return $wordpress_user;
    }
}
?>