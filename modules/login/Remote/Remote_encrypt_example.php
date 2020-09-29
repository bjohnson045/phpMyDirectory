<?php
// If SSL is not available, and mcrypt is enabled, an alternative method of sending passwords could be using mcrypt.

// Basic Encryption Key
$_remoteKey = 'Your Secret Key';

// Encrypt pass
$iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB), MCRYPT_RAND);
$enc_pass = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $_remoteKey, $this->password, MCRYPT_MODE_CFB, $iv);

// Be sure to send both $iv, as well as $enc_pass

// On the Remote.php page you would send:
$data = array(
	'username'=>$this->username,
	'password'=>$enc_pass,
	'iv'=>$iv
);

// On the remote page, decrypt with. ($enc_pass = $_POST['password'])
$plain_pass = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $_remoteKey, $enc_pass, MCRYPT_MODE_CFB, $iv);
?>