<?php
include(PMDROOT.'/modules/login/Ning/auth.php');

class Authentication_Ning extends Authentication_Module {
    function loadModuleUser() {
        $ning = new NingIdApi();
        if(!$user = $ning->authorize($this->PMDR->getConfig('login_module_db_host'),$this->username, $this->password)) {
            return false;
        }
        return array(
            'login'=>$this->username,
            'pass'=>md5($this->password),
            'user_email'=>$ning_user['email']
        );
    }
}
?>