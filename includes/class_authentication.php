<?php
/**
* Authentication Class
* Automatically authenticates users on construction
* Includes ability to remember logins via cookies, and be extended
*/
class Authentication {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database Database object
    */
    var $db;
    /**
    * @var Session Instance of Session class
    */
    var $session;
    /**
    * Url to re-direct to in not authenticated
    * @var string
    */
    var $redirect;
    /**
    * String to use when making hash of username and password
    * @var string
    */
    var $hashKey;
    /**
    * User ID
    * @var int
    */
    var $userid;
    /**
    * User login name
    * @var string
    */
    var $username;
    /**
    * User password
    * @var string
    */
    var $password;
    /**
    * Parameters for specific actions
    * @var array
    */
    var $parameters;
    /**
    * User information array
    * @var array
    */
    var $user;
    /**
    * Add debug information to the debug_messages array
    * @var boolean
    */
    var $debug = false;
    /**
    * Debug messages
    * @var array
    */
    var $debug_messages = array();
    /**
    * Default encryption hash
    * @var string
    */
    var $encryption = 'sha256';

    /**
    * Auth constructor
    * Checks for valid user automatically
    * @param object Registry
    * @param string URL to redirect to on failed login
    * @access public
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->hashKey = SECURITY_KEY;
        $this->session = $this->PMDR->get('Session');
        $this->installation_folder = COOKIE_PATH;
        $this->cookie_domain = COOKIE_DOMAIN;
    }

    /**
    * Checks username and password against database
    * @return void
    */
    function authenticate($parameters = array()) {
        if(isset($parameters['redirect'])) {
            if($this->debug) {
                $this->debug_messages[] = 'Settings redirect to '.$parameters['redirect'];
            }
            $this->redirect = $parameters['redirect'];
        }

        if($this->session->get($this->session_prefix.'hash')) {
            if(!$this->confirmAuth()) {
                if($this->debug) {
                    $this->debug_messages[] = 'Authentication confirmation failed (hash incorrect)';
                }
                $this->logout();
                if(!$this->redirect) {
                    if($this->debug) {
                        $this->debug_messages[] = 'Redirect not set, returning false';
                    }
                    return false;
                }
                if($this->debug) {
                    $this->debug_messages[] = 'Redirecting';
                }
                $this->redirect();
            }
            if($this->debug) {
                $this->debug_messages[] = 'Hash valid, returning true';
            }
            return true;
        }

        // Load login credentials (i.e. from $_POST)
        if(!$this->loadInput()) {
            if($this->debug) {
                $this->debug_messages[] = 'Input could not be loaded';
            }
            if(!$this->redirect) {
                if($this->debug) {
                    $this->debug_messages[] = 'Redirect not set, returning false';
                }
                return false;
            }
            if($this->debug) {
                $this->debug_messages[] = 'Redirecting with error message';
            }
            if(isset($parameters['error_message'])) {
                $this->redirect($parameters['error_message']);
            } else {
                $this->redirect();
            }
        }

        // Check if user has tried to login too many times
        if(($failed_count = $this->getFailedCount()) >= $this->PMDR->getConfig('failed_login_limit')) {
            if($this->debug) {
                $this->debug_messages[] = 'Failed count limit reached';
            }
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('login_failed_locked',array($failed_count,$this->PMDR->getConfig('failed_login_lock_time'))));
            if(!$this->redirect) {
                if($this->debug) {
                    $this->debug_messages[] = 'Redirect not set, returning false';
                }
                return false;
            }
            if($this->debug) {
                $this->debug_messages[] = 'Redirecting';
            }
            $this->redirect();
        }

        if($this->verifyLogin()) {
            if($this->debug) {
                $this->debug_messages[] = 'Login verified, storing authentication values';
            }
            $this->storeAuth();
            if($this->debug) {
                $this->debug_messages[] = 'Updating user details';
            }
            $this->PMDR->get('Users')->update(array('logged_last'=>$this->PMDR->get('Dates')->dateTimeNow(),'logged_ip'=>get_ip_address(),'logged_host'=>gethostbyaddr(get_ip_address())),$this->user['id']);
            $this->resetFailedCount();
            $this->PMDR->log('login',sprintf($this->PMDR->getLanguage('messages_login'), $this->session->get($this->session_prefix.'login')));
            return true;
        } else {
            if($this->debug) {
                $this->debug_messages[] = 'Login not verified';
            }
            $this->processFailedLogin();
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('login_failed',array($failed_count+1,$this->PMDR->getConfig('failed_login_limit'),$this->PMDR->getConfig('failed_login_lock_time'))));
            return false;
        }
    }

    /**
    * Process failed login attempt
    * Stores the failed login details in the database by IP
    * @return void
    */
    function processFailedLogin() {
        if($this->debug) {
            $this->debug_messages[] = 'Processing failed login';
        }
        $this->db->Execute("INSERT INTO ".T_USERS_LOGIN_FAILS." (date,ip_address) VALUES (NOW(),?)",array(get_ip_address()));
    }

    /**
    * Get failed login count from database
    * @return int
    */
    function getFailedCount() {
        if($this->debug) {
            $this->debug_messages[] = 'Getting failed count';
        }
        return $this->db->GetOne("SELECT COUNT(*) as count FROM ".T_USERS_LOGIN_FAILS." WHERE ip_address=? AND date > DATE_SUB(NOW(),INTERVAL ".$this->PMDR->getConfig('failed_login_lock_time')." MINUTE)",array(get_ip_address()));
    }

    /**
    * Reset the failed login count
    * @return void
    */
    function resetFailedCount() {
        if($this->debug) {
            $this->debug_messages[] = 'Resetting failed count';
        }
        $this->db->Execute("DELETE FROM ".T_USERS_LOGIN_FAILS." WHERE ip_address=?",array(get_ip_address()));
    }

    /**
    * Load the user by username and optionally the password
    * @param string $username
    * @param string $password
    * @return array|boolean User array or false on failure
    */
    function loadUser($username, $password = NULL) {
        if($this->debug) {
            $this->debug_messages[] = 'Loading user by username';
        }
        $this->user = $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE (login=? OR user_email=?)",array($username,$username));
        if(!$this->user OR is_null($password)) {
            $this->debug_messages[] = 'Password is null or user not found, return the user';
            return $this->user;
        }
        if($this->debug) {
            $this->debug_messages[] = 'Verifying password';
        }
        if($this->verifyPassword($password,$this->user['pass'],$this->user['password_salt'],$this->user['password_hash'])) {
            if($this->debug) {
                $this->debug_messages[] = 'Password correct';
            }
            unset($this->user['password_salt'],$this->user['password_hash']);
            return $this->user;
        } else {
            if($this->debug) {
                $this->debug_messages[] = 'Password incorrect';
            }
            return false;
        }
    }

    /**
    * Force a user login
    * @return boolean
    */
    function forceLogin($username,$field = null) {
        // Allow a user ID to be used for forced log ins
        if(is_numeric($username) AND (is_null($field) OR $field == 'id')) {
            if(!$username = $this->db->GetOne("SELECT login FROM ".T_USERS." WHERE id=?",array($username))) {
                return false;
            }
        }

        if($this->loadUser($username)) {
            $this->storeAuth();
            return true;
        } else {
            return false;
        }
    }

    /**
    * Verify a login
    * Loads the user from the stored details
    * @return boolean
    */
    function verifyLogin() {
        $user = $this->loadUser($this->username,$this->password);
        if($this->debug) {
            if($user) {
                $this->debug_messages[] = 'User loaded';
            } else {
                $this->debug_messages[] = 'User failed to load';
            }
        }
        return (($user) ? true : false);
    }

    /**
    * Set up the session from a cookie
    * Used for the remember me feature
    * @return boolean
    */
    function setSessionFromCookies() {
        if($this->debug) {
            $this->debug_messages[] = 'Setting session from cookies';
        }
        if(isset($_COOKIE[COOKIE_PREFIX.md5($this->installation_folder).'_'.$this->session_prefix.'login']) AND $_COOKIE[COOKIE_PREFIX.md5($this->installation_folder).'_'.$this->session_prefix.'login'] != '') {
            if($this->debug) {
                $this->debug_messages[] = 'Found cookie';
            }
            $cookie_parts = explode(':',$_COOKIE[COOKIE_PREFIX.md5($this->installation_folder).'_'.$this->session_prefix.'login']);
            if($user = $this->db->GetRow("SELECT * FROM ".T_USERS." WHERE id=? AND MD5(CONCAT(pass,cookie_salt)) = ?",array($cookie_parts[0],$cookie_parts[1]))) {
                if($this->debug) {
                    $this->debug_messages[] = 'Found valid user from cookie, setting session, returning true';
                }
                $this->session->set($this->session_prefix.'login',$user['login']);
                $this->session->set($this->session_prefix.'first_name',$user['user_first_name']);
                $this->session->set($this->session_prefix.'last_name',$user['user_last_name']);
                $this->session->set($this->session_prefix.'pass',$user['pass']);
                $this->session->set($this->session_prefix.'id',$user['id']);
                $this->session->set($this->session_prefix.'permissions',$this->PMDR->get('Users')->getPermissions($user['id']));
                $this->session->set($this->session_prefix.'hash',md5($this->hashKey.$user['login'].$user['pass']));
                $this->session->set($this->session_prefix.'timezone',$user['timezone']);
                $this->resetCookieHash($user['id'],$user['pass']);
                return true;
            } else {
                if($this->debug) {
                    $this->debug_messages[] = 'No user found, clearing cookie, returning false';
                }
                setcookie(COOKIE_PREFIX.md5($this->installation_folder).'_'.$this->session_prefix.'login','',time()-3600,$this->installation_folder,$this->cookie_domain);
                return false;
            }
        } else  {
            if($this->debug) {
                $this->debug_messages[] = 'Cookie not found, returning false';
            }
            return false;
        }
    }

    /**
    * Reset cookie hash
    * Used for security purposes and refreshed regularly
    * @param int $userid
    * @param string $password
    * @return void
    */
    function resetCookieHash($userid, $password) {
        if($this->debug) {
            $this->debug_messages[] = 'Resetting cookie hash';
        }
        $new_salt = $this->changeCookieSalt($userid);
        setcookie(COOKIE_PREFIX.md5($this->installation_folder)."_".$this->session_prefix.'login', $userid.':'.md5($password.$new_salt), time()+60*$this->PMDR->getConfig('login_cookie_timeout'),$this->installation_folder,$this->cookie_domain);
    }

    /**
    * Generate a 32 character salt
    * @return string
    */
    function generateSalt() {
        return md5(uniqid(rand(), true));
    }

    /**
    * Change the stored cookie salt
    * @param int $userid
    * @return string
    */
    function changeCookieSalt($userid) {
        $new_salt = $this->generateSalt();
        if($this->debug) {
            $this->debug_messages[] = 'Changing user salt to '.$new_salt;
        }
        $this->db->Execute("UPDATE ".T_USERS." SET cookie_salt=? WHERE id=?",array($new_salt,$userid));
        return $new_salt;
    }

    /**
    * Encrypt a password
    * @param string $password
    * @param string $salt
    * @param string $hash
    * @return string Encrypted password
    */
    function encryptPassword($password, $salt='', $hash = null) {
        if(is_null($hash)) {
            $hash = $this->encryption;
        }
        if(empty($hash) OR !in_array($hash,array('md5','sha1','sha2','sha256'))) {
            $hash = 'md5';
        }
        return hash($hash,$password.$salt);
    }

    /**
    * Verify a password
    * Checks a password against the encrypted version
    * @param string $password
    * @param string $stored_password
    * @param string $salt
    * @param string $hash
    * @return boolean
    */
    function verifyPassword($password,$stored_password,$salt,$hash) {
        return ($stored_password == $this->encryptPassword($password,$salt,$hash));
    }

    /**
    * Sets the session variables after a successful login
    * @return void
    */
    function storeAuth() {
        // Regenerate ID for security
        $this->session->regenerate_id();
        // Store useful information about the user in session variables
        $this->session->set($this->session_prefix.'login',$this->user['login']);
        $this->session->set($this->session_prefix.'first_name',$this->user['user_first_name']);
        $this->session->set($this->session_prefix.'last_name',$this->user['user_last_name']);
        $this->session->set($this->session_prefix.'pass',$this->user['pass']);
        $this->session->set($this->session_prefix.'id',$this->user['id']);
        $this->session->set($this->session_prefix.'permissions',$this->PMDR->get('Users')->getPermissions($this->user['id']));
        // Create a session variable to use to confirm sessions
        $this->session->set($this->session_prefix.'hash', md5($this->hashKey.$this->user['login'].$this->user['pass']));
        $this->session->set($this->session_prefix.'timezone',$this->user['timezone']);
        if($_POST['remember']) {
            setcookie(COOKIE_PREFIX.md5($this->installation_folder)."_".$this->session_prefix."login", $this->user['id'].':'.md5($this->user['pass'].$this->user['cookie_salt']), time()+60*$this->PMDR->getConfig('login_cookie_timeout'),$this->installation_folder,$this->cookie_domain);
        }
    }

    /**
    * Check a permission against stored permissions
    * @param string $permission
    * @return boolean
    */
    function checkPermission($permission) {
        if($this->debug) {
            $this->debug_messages[] = 'Checking permission '.$permission;
        }
        // The class prefix is not used here so we can check admin permissions on the public/members area as well
        $prefix = substr($permission,0,strpos($permission,'_')).'_';
        if(!$this->session->get($prefix.'login') OR !$this->session->get($prefix.'pass')) {
            return false;
        }
        if(!in_array($permission,(array) $this->session->get($prefix.'permissions'))) {
            if($this->debug) {
                $this->debug_messages[] = 'Returning false';
            }
            return false;
        }
        if($this->debug) {
            $this->debug_messages[] = 'Returning true';
        }
        return true;
    }

    /**
    * Confirms that an existing login is still valid
    * @return boolean
    */
    function confirmAuth() {
        $login=$this->session->get($this->session_prefix.'login');
        $password=$this->session->get($this->session_prefix.'pass');
        $hashKey=$this->session->get($this->session_prefix.'hash');

        if(md5($this->hashKey.$login.$password) != $hashKey) {
            return false;
        }
        return true;
    }

    /**
    * Load login data
    * @return boolean
    */
    function loadInput() {
        if(!empty($_POST[$this->session_prefix.'login']) AND !empty($_POST[$this->session_prefix.'pass'])) {
            $this->username = $_POST[$this->session_prefix.'login'];
            $this->password = $_POST[$this->session_prefix.'pass'];
            return true;
        } else {
            return false;
        }
    }

    /**
    * Logs the user out
    * @param boolean Parameter to pass on to Auth::redirect() (optional)
    * @return void
    */
    function logout() {
        $this->PMDR->log('logout',sprintf($this->PMDR->language['messages_logout'], $this->session->get($this->session_prefix.'login')));
        // Clear the cookies if they are set
        if(isset($_COOKIE[COOKIE_PREFIX.md5($this->installation_folder).'_'.$this->session_prefix.'login'])) {
            setcookie(COOKIE_PREFIX.md5($this->installation_folder)."_".$this->session_prefix."login", "", time()-60*60*24*100, $this->installation_folder,$this->cookie_domain);
            $this->changeCookieSalt($this->session->get($this->session_prefix.'id'));
        }
        $this->session->delete($this->session_prefix.'login');
        $this->session->delete($this->session_prefix.'pass');
        $this->session->delete($this->session_prefix.'first_name');
        $this->session->delete($this->session_prefix.'last_name');
        $this->session->delete($this->session_prefix.'id');
        $this->session->delete($this->session_prefix.'hash');
        $this->session->delete($this->session_prefix.'permissions');
        $this->session->delete($this->session_prefix.'timezone');
    }

    /**
    * Redirects browser and terminates script execution
    * @param boolean adverstise URL where this user came from (optional)
    * @return void
    */
    function redirect($error_message = null) {
        if(!is_null($error_message)) {
            $this->PMDR->addMessage('error',$error_message);
        }
        redirect($this->redirect,array('from'=>URL));
    }

    /**
    * Authenticate the current user against banned IP addresses
    */
    function authenticateIP() {
        $ips = explode("\r\n",$this->PMDR->getConfig('banned_ips'));
        $ip = get_ip_address();
        if(trim($ip) != '' AND in_array(get_ip_address(),$ips)) {
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('messages_banned'));
            redirect(BASE_URL.'/contact.php');
        }
    }
}

/**
* Admin Authentication Subclass
*/
class AuthenticationAdmin extends Authentication {
    /**
    * The session prefix to use for admin authentication
    * @var string
    */
    var $session_prefix = 'admin_';

    /**
    * Admin Authentication constructor
    * @param object $PMDR
    * @return AuthenticationAdmin
    */
    function __construct($PMDR) {
        $this->redirect = BASE_URL_ADMIN.'/index.php';
        parent::__construct($PMDR);
    }

    /**
    * Authenticate a user
    * @param array $parameters
    * @return boolean True on success
    */
    function authenticate($parameters = array()) {
        if(!parent::authenticate($parameters)) {
            if($this->debug) {
                $this->debug_messages[] = 'Failed parent authentication, returning false';
            }
            if(!$this->redirect) {
                return false;
            }
            $this->redirect();
        }
        if(!$this->checkPermission('admin_login',false)) {
            if($this->debug) {
                $this->debug_messages[] = 'Failed login permission check';
            }
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('messages_permission_denied'));
            $this->logout();
            if(!$this->redirect) {
                return false;
            }
            $this->redirect();
        }
        return true;
    }

    /**
    * Check a permission
    * @param string $permission
    * @param boolean $redirect Whether to redirect or not.
    * @param mixed $redirect_url The URL to redirect to or NULL if no redirection.
    * @return boolean True if permission is valid
    */
    function checkPermission($permission, $redirect=true, $redirect_url=null) {
        if((!$valid = parent::checkPermission($permission)) AND $redirect) {
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('messages_permission_denied'));
            if(!is_null($redirect_url)) {
                redirect_url($redirect_url);
            } else {
                redirect(BASE_URL_ADMIN.'/admin_index.php');
            }
        }
        return $valid;
    }
}

/**
* User Authentication subclass
*/
class AuthenticationUser extends Authentication {
    /**
    * The session prefix to use for user authentication
    * @var string
    */
    var $session_prefix = 'user_';

    /**
    * User authentication constructor
    * @param object $PMDR
    * @return AuthenticationUser
    */
    function __construct($PMDR) {
        $this->redirect = BASE_URL.MEMBERS_FOLDER.'index.php';
        parent::__construct($PMDR);
    }

    /**
    * Authenticate a user
    * @param array $parameters
    * @return boolean True on success
    */
    function authenticate($parameters = array()) {
        if(!parent::authenticate($parameters)) {
            if($this->debug) {
                $this->debug_messages[] = 'Failed parent authentication, returning false';
            }
            if(!$this->redirect) {
                return false;
            }
            $this->redirect();
        }
        if(!$this->checkPermission('user_login')) {
            if($this->debug) {
                $this->debug_messages[] = 'Failed login permission check';
            }
            $this->PMDR->addMessage('error',$this->PMDR->getLanguage('messages_permission_denied'));
            $this->logout();
            if(!$this->redirect) {
                return false;
            }
            $this->redirect();
        }
        return true;
    }
}
?>