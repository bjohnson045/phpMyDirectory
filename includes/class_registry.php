<?php
/**
* Registry class
* Stores and processes classes
*/
class Registry {
    /**
    * Factory
    * @var object
    */
    var $factory;
    /**
    * Registry data
    * @var array
    */
    var $data = array();
    /**
    * Database
    * @var object
    */
    var $db;
    /**
    * Configuration values
    * @var array
    */
    var $config;
    /**
    * Language values
    * @var array
    */
    var $language;

    /**
    * Registry instance
    * @var Registry
    */
    static private $thisInstance = null;

    /**
    * Load the factory
    * @param object $factory
    * @return Registry
    */
    function __construct($factory) {
        spl_autoload_register(array($this, 'load'));
        $this->factory = $factory;
    }

    /**
    * Get the registry instance
    * @return Registry
    */
    static function getInstance() {
        if(self::$thisInstance == null) {
            self::$thisInstance = new Registry(new Factory());
        }
        return self::$thisInstance;
    }

    /**
    * Load files
    * @param string $className
    */
    function load($className) {
        if(file_exists(PMDROOT.'/includes/class_'.strtolower($className).'.php')) {
            include(PMDROOT.'/includes/class_'.strtolower($className).'.php');
        }
    }

    /**
    * Get a value from the registry
    * @param string $className
    * @param mixed $constructor_input
    * @param boolean $getnew
    */
    function get($className, $constructor_input=null, $getnew = false) {
        $className = strtolower($className);
        $constructor = 'make' . $className;
        // Check if the data already exists
        if((!array_key_exists($className,$this->data) OR $getnew)) {
            // Check if the constructor exists in the factory
            if(!method_exists($this->factory,$constructor)) {
                if(class_exists($className)) {
                    // Load the object into the registry
                    if(is_null($constructor_input)) {
                        $this->data[$className] = new $className($this);
                    } else {
                        $this->data[$className] = new $className($this,$constructor_input);
                    }
                // No data was found
                } else {
                    return false;
                }
            } else {
                // Load the data into the registry from the Factory
                if(!is_null($constructor_input)) {
                    if($getnew) {
                        return $this->factory->$constructor($this, $constructor_input);
                    } else {
                        $this->data[$className] = $this->factory->$constructor($this, $constructor_input);
                    }
                } else {
                    if($getnew) {
                        return $this->factory->$constructor($this);
                    } else {
                        $this->data[$className] = $this->factory->$constructor($this);
                    }
                }
            }
        }
        return $this->data[$className];
    }

    /**
    * Returns a new copy of an object
    * @param mixed $className
    * @param mixed $constructor_input
    * @return mixed
    */
    function getNew($className, $constructor_input=null) {
        return $this->get($className, $constructor_input, true);
    }

    /**
    * Set a value in the registry or session
    * @param string $variable_name
    * @param mixed $data
    * @param boolean $in_session
    */
    function set($variable_name, $data, $in_session = false) {
        if($in_session) {
            $_SESSION[$variable_name] = $data;
        } else {
            $this->data[$variable_name] = $data;
        }
    }

    /**
    * Add multiple data into one variable
    * @param string $variable_name
    * @param mixed $data
    */
    function setAdd($variable_name, $data, $flush = false) {
        if(!isset($this->data[$variable_name]) OR $flush) {
            $this->data[$variable_name] = array();
        }
        if(!is_array($this->data[$variable_name])) {
            $this->data[$variable_name] = array($this->data[$variable_name]);
        }
        $this->data[$variable_name] = array_merge($this->data[$variable_name],is_array($data) ? $data : array($data));
    }

    /**
    * Add data onto a multidimensional array
    */
    function setAddArray() {
        $variable_name = func_get_arg(0);
        if(func_num_args() == 2) {
            $data = func_get_arg(1);
            $this->data[$variable_name][] = is_array($data) ? $data : array($data);
        } else {
            $key = $data = func_get_arg(1);
            $data = func_get_arg(2);
            $this->data[$variable_name][$key] = is_array($data) ? $data : array($data);
        }
    }

    /**
    * Load configuration values from the database
    * @param mixed $sections
    */
    function loadConfig($sections = array()) {
        $this->config = $this->get('DB')->GetAssoc("SELECT varname, value FROM ".T_SETTINGS);
    }

    /**
    * Load language values from the database
    * @param array $sections
    */
    function loadLanguage($sections = array()) {
        if(!is_array($sections)) {
            $sections = array_filter(array($sections));
        }
        $sections[] = 'messages';
        $sections[] = 'custom';
        if(defined('PMD_SECTION')) {
            switch(PMD_SECTION) {
                case 'members':
                    $set_language = $this->config['language'];
                    $sections[] = 'user_general';
                    break;
                case 'admin':
                    $set_language = $this->config['language_admin'];
                    $sections[] = 'admin_general';
                    break;
                case 'public':
                    $set_language = $this->config['language'];
                    $sections[] = 'public_general';
                    break;
                default:
                    $set_language = $this->config['language'];
                    break;
            }
        } else {
            $set_language = $this->config['language'];
            $sections[] = 'public_general';
        }

        if(!$language = $this->get('Cache')->get($set_language.implode('',$sections),0,'language_')) {
            $db = $this->get('DB');
            try {
                $language = @$db->GetRow("SELECT languageid, title, languagecode, charset, textdirection, decimalseperator, thousandseperator, decimalplaces, currency_prefix, currency_suffix, date_override, time_override, locale FROM ".T_LANGUAGES." WHERE languageid=?",array($set_language));
            } catch (Exception $e) {
                // Do nothing
                $langauge = array();
            }
            if(!$language) {
                try {
                    $language = $db->GetRow("SELECT languageid, title, languagecode, charset, textdirection, decimalseperator, thousandseperator, decimalplaces, currency_prefix, currency_suffix, date_override, time_override, locale FROM ".T_LANGUAGES." WHERE languageid=?",array(1));
                    $this->config['language'] = $language['languageid'];
                } catch (Exception $e) {
                    // Do nothing
                    $langauge = array();
                }
            }

            // Get the default and translated language for the sections we want to get
            $language_phrases = $db->GetAssoc("SELECT master.variablename, COALESCE(phrases.content, master.content)
            FROM ".T_LANGUAGE_PHRASES." as master LEFT JOIN ".T_LANGUAGE_PHRASES." as phrases ON master.variablename=phrases.variablename AND master.section=phrases.section AND phrases.languageid=?
            WHERE master.languageid=-1 AND master.section IN('".implode("','",$sections)."')",array($language['languageid']));

            $language = array_merge($language_phrases, (array) $language);

            $this->get('Cache')->write($language['languageid'].implode('',$sections),$language,'language_');
        } else {
            $language = $language;
        }

        // Combine above arrays in a fashion where translated variables always override
        if(is_array($this->language) AND sizeof($this->language) > 0) {
            $this->language = array_merge($this->language, (array) $language);
        } else {
            $this->language = $language;
        }
        unset($language);

        // Apply override values if they are non-empty
        if($this->language['date_override'] != '') $this->config['date_format'] = $this->language['date_override'];
        if($this->language['time_override'] != '') $this->config['time_format'] = $this->language['time_override'];

        if(!defined('CHARSET')) {
            define('CHARSET',$this->language['charset']);
        }

        if($this->language['locale'] != '') {
            $locale = str_replace('-','_',$this->language['locale']);
            if(!setlocale(LC_TIME,$locale.'.utf8', $locale.'.UTF-8', $locale, strstr($locale,'_',true))) {
                trigger_error('Unable to set locale '.str_replace('-','_',$this->language['locale']).'.UTF-8',E_USER_WARNING);
            }
            unset($locale);
        }
    }

    /**
    * Get a configuration value
    * @param string $key
    * @return mixed
    */
    function getConfig($key) {
        if(!is_array($this->config) OR sizeof($this->config) < 1) {
            $this->loadConfig();
        }
        return isset($this->config[$key]) ? $this->config[$key] : false;
    }

    /**
    * Get a language value
    * @param string $key
    * @param array $variables
    * @return string
    */
    function getLanguage($key, $variables = array()) {
        if(count($this->language) < 1) {
            $this->loadLanguage();
        }
        if(!isset($this->language[$key])) {
            return false;
        }
        if(is_array($variables) AND count($variables) > 0) {
            return vsprintf($this->language[$key],$variables);
        } else {
            return $this->language[$key];
        }
    }

    /**
    * Add a global message
    * @param string $type
    * @param mixed $messages
    * @param string $log_type
    */
    function addMessage($type, $messages, $log_type = null) {
        $session = $this->get('Session');
        if(!is_array($messages)) {
            $messages = array($messages);
        }
        if($current_message = $session->get('messages')) {
            foreach($messages AS $message) {
                $current_message[$type][] = $message;
            }
            $session->set('messages',$current_message);
        } else {
            $session->set('messages',array($type=>$messages));
        }
        if(!is_null($log_type)) {
            $this->log($log_type,$messages);
        }
    }

    /**
    * Get all messages
    * @return array
    */
    function getMessages() {
        $session = $this->get('Session');
        if($messages = $session->get('messages')) {
            $session->delete('messages');
            return $messages;
        } else {
            return array();
        }
    }

    /**
    * Log an action
    * @param string $action_type
    * @param string $action
    */
    function log($action_type, $action) {
        $logger = $this->get('Logger');
        $logger->log($action_type, $action);
    }

    /**
    * Load CSS
    * @param string $css_include
    * @param int $priority
    */
    function loadCSS($css_include, $priority = 0) {
        if(!@in_array($css_include,(array) $this->data['load_css'][$priority])) {
            $this->data['load_css'][$priority][] = $css_include;
        }
    }

    /**
    * Load Javascript
    * @param string $js_include
    * @param int $priority
    */
    function loadJavascript($js_include, $priority = 0) {
        if(!empty($js_include) AND (!isset($this->data['load_javascript'][$priority]) OR !@in_array($js_include, $this->data['load_javascript'][$priority]))) {
            $this->data['load_javascript'][$priority][] = $js_include;
        }
    }

    /**
    * Get all loaded CSS
    * @return string
    */
    function getCSS() {
        $data = '';
        if(is_array($this->data['load_css'])) {
            ksort($this->data['load_css']);
            foreach($this->data['load_css'] AS $priority) {
                foreach($priority AS $css) {
                    $data .= $css."\n";
                }
            }
        }
        return $data;
    }

    /**
    * GetAll loaded Javascript
    * @return string
    */
    function getJavascript() {
        $data = '';
        if(is_array($this->data['load_javascript'])) {
            ksort($this->data['load_javascript']);
            foreach($this->data['load_javascript'] AS $priority) {
                foreach($priority AS $javascript) {
                    $data .= $javascript."\n";
                }
            }
        }
        return $data;
    }
}
?>