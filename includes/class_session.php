<?php
/**
* Session handler
* Uses database to handle sessions
*/
class Session {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Database
    * @var object
    */
    var $db;
    /**
    * Session lifetime timeout value
    * @var int
    */
    var $sessionLifetime;
    /**
    * Session Name
    */
    var $name = 'session';

    /**
    * Initiate the session and settings
    * @param object $PMDR
    * @return Session
    */
    function __construct($PMDR) {
        $this->name = COOKIE_PREFIX.$this->name;
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');

        // Set the default session values
        @ini_set("session.gc_probability",1);
        @ini_set("session.gc_divisor",100);

        // Set the session timeout value from configuration, if false we get the default server value
        if(!$this->sessionLifetime = intval($this->PMDR->getConfig('session_timeout'))*60) {
            $this->sessionLifetime = get_cfg_var("session.gc_maxlifetime");
        }

        // Run the session write close function on shut down
        register_shutdown_function('session_write_close');

        // If we do not have a session
        if(session_id() == '') {
            // Start the session
            $this->start();

            if(isset($_SESSION['OBSOLETE']) AND $_SESSION['OBSOLETE'] AND (!isset($_SESSION['EXPIRES']) OR $_SESSION['EXPIRES'] < time())) {
                $_SESSION = array();
                session_destroy();
                $this->start();
            }

            // If we do not have an "initiated" session variable, regenerate the ID to prevent session fixation
            if(!$this->get('initiated') OR rand(1, 100) <= 5) {
                $this->regenerate_id();
                $this->set('initiated',true);
            }
        }
    }

    /**
    * Start the session
    */
    function start() {
        // Setup the save handler to use the methods in this class
        // Required before each session_start() call for PHP 5.2 due to a bug: https://bugs.php.net/bug.php?id=32330
        session_set_save_handler(
            array($this, 'open'),
            array($this, 'close'),
            array($this, 'read'),
            array($this, 'write'),
            array($this, 'destroy'),
            array($this, 'gc')
        );
        session_set_cookie_params(0, COOKIE_PATH, COOKIE_DOMAIN, false, false);
        session_name($this->name);
        session_start();
    }

    /**
    * Regenerate session ID
    */
    function regenerate_id() {
        // Set the current session to expire in 10 seconds
        $_SESSION['OBSOLETE'] = true;
        $_SESSION['EXPIRES'] = time() + 10;

        // Create new session without destroying the old one
        session_regenerate_id(false);

        // Get current session ID and close both sessions to allow other scripts to use them
        $session_id = session_id();
        session_write_close();

        // Set the session ID to the new one and restart the session
        session_id($session_id);
        $this->start();

        // We want to keep this session so remove the expiration
        unset($_SESSION['OBSOLETE']);
        unset($_SESSION['EXPIRES']);
    }

    /**
    * Open the session
    * @param string $save_path
    * @param string $session_name
    */
    function open($save_path, $session_name) {
        return true;
    }

    /**
    * Close the session
    */
    function close() {
        return true;
    }

    /**
    * Read the session
    * @param string $session_id
    * @return mixed
    */
    function read($session_id) {
        // Get the session from the database by checking user agent, ID, and last access time
        $session = $this->db->GetRow("SELECT session_data FROM ".T_SESSIONS." WHERE id=? AND http_user_agent=? AND last_access > ?",array($session_id,md5(SECURITY_KEY.$_SERVER['HTTP_USER_AGENT']),time()-$this->sessionLifetime));
        if($session) {
            return $session['session_data'];
        }
        return '';
    }

    /**
    * Write the session
    * @param string $session_id
    * @param mixed $session_data
    * @return boolean
    */
    function write($session_id, $session_data) {
        if(!$user_id = $this->get('admin_id')) {
            if(!$user_id = $this->get('user_id')) {
                $user_id = NULL;
            }
        }
        $this->db->Execute("REPLACE INTO ".T_SESSIONS." (id,user_id,session_data,last_access,http_user_agent) VALUES (?,?,?,?,?)",array($session_id,$user_id,$session_data,time(),md5(SECURITY_KEY.$_SERVER['HTTP_USER_AGENT'])));
        if($this->db->Affected_Rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Destroy the session
    * @param string $session_id
    * @return boolean
    */
    function destroy($session_id = null) {
        $this->db->Execute("DELETE FROM ".T_SESSIONS." WHERE id=?",array($session_id));
        if($this->db->Affected_Rows()) {
            return true;
        } else {
            return false;
        }
    }

    /**
    * Session garbage collection
    * Deletes timeed out sessions
    * @param mixed $maxlifetime
    * @return boolean
    */
    function gc($maxlifetime) {
        $this->db->Execute("DELETE FROM ".T_SESSIONS." WHERE last_access < ?",array(time()-$this->sessionLifetime));
        return true;
    }

    /**
    * Set a session value
    * @param string $variable
    * @param mixed $value
    */
    function set($variable,$value) {
        $_SESSION[$variable] = $value;
    }

    /**
    * Get a session value
    * @param string $variable
    * @return mixed
    */
    function get($variable) {
        return isset($_SESSION[$variable]) ? $_SESSION[$variable] : false;
    }

    /**
    * Delete a session value
    * @param string $variable
    * @return boolean
    */
    function delete($variable) {
        if(isset($_SESSION[$variable])) {
            unset($_SESSION[$variable]);
            return true;
        } else {
            return false;
        }
    }

    /**
    * Get the number of active sessions
    * @return int
    */
    function get_users_online() {
        return $this->db->GetOne("SELECT COUNT(id) as count FROM ".T_SESSIONS);
    }

    /**
    * Destructor
    */
    function __destruct () {
        @session_write_close();
    }
}

/**
* Session file handler
* Uses files to manage sessions
*/
class Session_Files extends Session {
    /**
    * Path where sessions are stored
    * @var string
    */
    var $savePath = '';
    /**
    * Session Name
    */
    var $name = 'PMDFilesSession';

    /**
    * Open the session
    * @param string $save_path
    * @param string $session_name
    * @return boolean
    */
    function open($save_path, $session_name) {
        if(empty($save_path) OR @!is_writable($save_path)) {
            $checks = array('save_path','upload_tmp_dir','tmp','temp','tmpdir','sys_get_temp_dir','file');

            $path = false;
            foreach($checks AS &$check) {
                switch($check) {
                    case 'save_path':
                        $path = session_save_path();
                        break;
                    case 'upload_tmp_dir':
                        $path = ini_get('upload_tmp_dir');
                        break;
                    case 'tmp':
                        $path = realpath(getenv('TMP'));
                        break;
                    case 'temp':
                        $path = realpath(getenv('TEMP'));
                        break;
                    case 'tmpdir':
                        $path = realpath(getenv('TMPDIR'));
                        break;
                    case 'sys_get_temp_dir':
                        $path = sys_get_temp_dir();
                        break;
                    case 'file':
                        $temp_file = tempnam(md5(uniqid(rand(),TRUE)),'');
                        if($temp_file) {
                            $path = realpath(dirname($temp_file));
                            unlink($temp_file);
                        }
                        break;
                }
                try {
                    if($path AND is_writable($path)) {
                        $this->savePath = $path;
                        unset($path);
                        return true;
                    }
                } catch (Exception $e) {
                    // Catch instance where is_writable can return an exception on some servers:
                    // Fatal error: Exception thrown without a stack frame in Unknown on line 0
                    // Usually caused by open_basedir restriction
                }
            }
        } else {
            $this->savePath = $save_path;
            return true;
        }
        return false;
    }

    /**
    * Close the session
    * @return boolean
    */
    function close() {
        return true;
    }

    /**
    * Read the session
    * @param string $session_id
    * @return mixed
    */
    function read($session_id) {
        return (string) @file_get_contents($this->savePath.'/sess_'.$session_id);
    }

    /**
    * Write the session
    * @param string $session_id
    * @param mixed $session_data
    * @return boolean
    */
    function write($session_id, $session_data) {
        if ($fp = @fopen($this->savePath.'/sess_'.$session_id, "w")) {
            $return = fwrite($fp, $session_data);
            fclose($fp);
            return $return;
        } else {
            return false;
        }
    }

    /**
    * Destroy the session
    * @param string $session_id
    * @return bool
    */
    function destroy($session_id = null) {
        return @unlink($this->savePath.'/sess_'.$session_id);
    }

    /**
    * Session garbage collection
    * @param int $maxlifetime
    * @return boolean
    */
    function gc($maxlifetime) {
        foreach (glob($this->savePath.'/sess_*') AS $filename) {
            if(filemtime($filename) + $this->sessionLifetime < time()) {
                @unlink($filename);
            }
        }
        return true;
    }

    /**
    * Get number of active sessions
    * @return int
    */
    function get_users_online() {
        if($dh = opendir($this->savePath.'/')) {
            $count = 0;
            while(false !== ($file = readdir($dh))) {
                if($file != '.' && $file != '..') {
                    if(time()- fileatime($this->savePath.'/'.$file) < 3 * 60) {
                        $count++;
                    }
                }
            }
            closedir($dh);
            return $count;
        } else {
            return false;
        }
    }
}
?>