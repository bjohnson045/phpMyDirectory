<?php
/***
 * Remote URL Login Module
 *  - Since it is undetermined how the remote user will store passwords, the
 *    passwords will be transmitted in plaintext and thus require SSL.
 *
 * Example URL: https://www.yoursite.com/modules/login/Remote/Remote_example.php
 */
class Authentication_Remote extends Authentication_Module {
    function databaseConnect() { return true; }
    function databaseReset() { return true; }

    function loadModuleUser() {
	    $_remoteUrl = $this->PMDR->getConfig('login_module_db_host'); # URL to Remote Authentication

		$data = array(
            'username'=>$this->username,
            'password'=>$this->password
        );

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $_remoteUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $raw_json = curl_exec($curl);
        curl_close($curl);

        $auth_info = json_decode($raw_json, true);

		if ($auth_info['success']) {
			return array(
                'login'=>$auth_info['login'],
				'user_email'=>$auth_info['user_email'],

				// Dummy Pass for Local Database
				'pass'=>Strings::strtolower(Strings::random(32))
			);
		}

		return false;
    }
}
?>