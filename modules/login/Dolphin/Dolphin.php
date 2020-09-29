<?php
class Authentication_Dolphin extends Authentication_Module {
    function loadModuleUser() {
        return $this->db->GetRow("SELECT NickName AS login, Password AS pass, Email AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."Profiles WHERE NickName=? AND Password=?",array($this->username,md5($this->password)));
    }
}
?>