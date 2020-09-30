<?php

require('Authentication/Abstract.php');
require('Authentication/PasswordHash.php'); // Used with PhpBb3

require('Authentication/Core.php');
require('Authentication/Core12.php');
require('Authentication/Default.php');
require('Authentication/IPBoard.php');
require('Authentication/MyBb.php');
require('Authentication/NoPassword.php');
require('Authentication/PhpBb3.php');
require('Authentication/SMF.php');
require('Authentication/vBulletin.php');

class Authentication_Xenforo extends Authentication_Module {
    function loadModuleUser() {
        $row = $this->db->GetRow('SELECT user.user_id, user.username, user.email, auth.scheme_class, auth.data, auth.remember_key FROM '.$this->PMDR->getConfig('login_module_db_prefix').'user as `user` LEFT JOIN '.$this->PMDR->getConfig('login_module_db_prefix').'user_authenticate as `auth` ON user.user_id = auth.user_id WHERE user.username=? OR user.email=? LIMIT 1;', array($this->username, $this->username));

        if ($row === false) {
            // Username not found.
            return false;
        }

        // Check if the authentication scheme_class exists
        if (!class_exists($row['scheme_class'], false)) {
            // Xenforo may have added a new import option.
            trigger_error('Unrecognized Xenforo authentication scheme: ' . $row['scheme_class']);
            return false;
        }

        /* @var $auth XenForo_Authentication_Abstract */
        $auth = new $row['scheme_class']();
        $auth->setData($row['data']);

        if ($auth->authenticate($row['user_id'], $this->password)) {
            // Successfully authenticated.
            return array(
                'login' => $row['username'],
                'pass' => $this->password,
                'user_email' => $row['email']
            );
        }

        // Not authenticated
        return false;
    }
}
?>