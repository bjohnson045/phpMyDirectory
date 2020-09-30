<?php
/**
* Authentication module class
* Extends the basic user authentication class allowing modules to be written
*/
class Authentication_Module extends AuthenticationUser {
    /**
    * User information gathered from module
    * @var mixed
    */
    var $module_user;

    /**
    * Reset the database connection to default connection
    * @return boolean
    */
    function databaseReset() {
        return $this->db->Connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    }

    /**
    * Connect to the login modules database
    * @return boolean
    */
    function databaseConnect() {
        if(!$this->db->Connect($this->PMDR->getConfig('login_module_db_host'), $this->PMDR->getConfig('login_module_db_user'), $this->PMDR->getConfig('login_module_db_password'), $this->PMDR->getConfig('login_module_db_name'))) {
            $this->databaseReset();
            return false;
        }
        return true;
    }

    /**
    * Perform default login verification
    * @return boolean
    */
    function defaultVerifyLogin() {
        return parent::verifyLogin();
    }

    /**
    * Verify the login
    * @return boolean
    */
    function verifyLogin() {
        if(!$this->databaseConnect()) {
            return false;
        }

        $this->module_user = $this->loadModuleUser();

        $this->databaseReset();

        if(!$this->module_user) {
            return $this->defaultVerifyLogin();
        }

        $this->processUser();

        return true;
    }

    /**
    * Process the user on successful login.
    */
    function processUser() {
        $class_name = substr(strrchr(get_class($this),'_'),1);
        if(!$this->loadUser($this->username)) {
            $this->user['cookie_salt'] = $this->generateSalt();
            $this->user['id'] = $this->PMDR->get('Users')->insert(array_merge(array('user_groups'=>array(4),'salt'=>$this->user['salt']),$this->module_user));
            $this->db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id, login_provider, login_id) VALUES (?,?,?)",array($this->user['id'],$class_name,$this->module_user['login']));
            $this->loadUser($this->module_user['login']);
        } elseif(empty($this->user['login_provider'])) {
            $this->db->Execute("INSERT INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider) VALUES (?,?,?) ON DUPLICATE KEY UPDATE login_provider=?, login_id=?",array($this->user['id'],$this->module_user['login'],$class_name,$class_name,$this->module_user['login']));
        }
    }
}
?>