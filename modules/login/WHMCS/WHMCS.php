<?php
class Authentication_WHMCS extends Authentication_Module {
    function databaseConnect() { return true; }
    function databaseReset() { return true; }

    function loadModuleUser() {
        $url = $this->PMDR->getConfig('login_module_db_host'); # URL to WHMCS API file
        $postfields["username"] = $this->PMDR->getConfig('login_module_db_user');
        $postfields["password"] = md5($this->PMDR->getConfig('login_module_db_password'));

        $postfields["action"] = "getclientsdatabyemail";
        $postfields["email"] = $this->username;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 100);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        $data = curl_exec($ch);
        curl_close($ch);

        $data = explode(";",$data);
        foreach($data AS $temp) {
            $temp = explode("=",$temp);
            $results[$temp[0]] = $temp[1];
        }

        if($results["result"]=="success") {
            $password_parts = explode(':',$results['password']);
            if($password_parts[0] != md5($password_parts[1].$this->password)) {
                return false;
            }
            return array(
                'login'=>$results['email'],
                'pass'=>$results['password'],
                'user_email'=>$results['email']
            );
        } else {
            return false;
        }
    }
}
?>