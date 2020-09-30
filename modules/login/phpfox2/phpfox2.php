<?php
class Authentication_phpfox2 extends Authentication_Module {
    function loadModuleUser() {
        if($user =  $this->db->GetRow("SELECT user_name AS login, password AS pass, email AS user_email, full_name FROM ".$this->PMDR->getConfig('login_module_db_prefix')."user WHERE (user_name=? OR email=?) AND password=MD5(CONCAT(?,MD5(password_salt)))",array($this->username,$this->username,md5($this->password)))) {
            if(!empty($user['full_name'])) {
                if(count($user_name_parts = explode(' ',$user['full_name'])) > 2) {
                    $user['user_first_name'] = $user_name_parts[1];
                    $user['user_last_name'] = $user_name_parts[2];
                }
            }
            return $user;
        }
        return false;
    }
}
?>