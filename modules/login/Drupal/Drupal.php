<?php
class Authentication_Drupal extends Authentication_Module {
    // http://api.drupal.org/api/drupal/includes--password.inc/function/user_check_password/7
    function loadModuleUser() {
        if(!$user = $this->db->GetRow("SELECT name AS login, pass, mail AS user_email FROM ".$this->PMDR->getConfig('login_module_db_prefix')."users WHERE name=?",array($this->username))) {
            return false;
        }

        $password = $this->password;

        include(PMDROOT.'/modules/login/Drupal/functions.php');

        if(substr($user['pass'], 0, 2) == 'U$') {
            $stored_hash = substr($user['pass'], 1);
            $password = md5($password);
        } else {
            $stored_hash = $user['pass'];
        }

        $type = substr($stored_hash, 0, 3);
        switch ($type) {
            case '$S$':
                // A normal Drupal 7 password using sha512.
                $hash = _password_crypt('sha512', $password, $stored_hash);
            break;
            case '$H$':
                // phpBB3 uses "$H$" for the same thing as "$P$".
                case '$P$':
                // A phpass password generated using md5.  This is an
                // imported password or from an earlier Drupal version.
                $hash = _password_crypt('md5', $password, $stored_hash);
                break;
            default:
                return false;
        }
        if($hash AND $stored_hash == $hash) {
            return $user;
        }
        return false;
    }
}
?>