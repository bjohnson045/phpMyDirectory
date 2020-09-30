<?php
class Authentication_phpfox extends Authentication_Module {
    function verifyLogin() {
        return $this->db->GetRow("SELECT user AS login, password AS pass, email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."user WHERE (user=? OR email=?) AND password=?",array($this->username,$this->username,md5($this->password)));
    }
}
?>