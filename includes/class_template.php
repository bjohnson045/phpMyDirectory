<?php
/**
* Class Template
* Simple templating engine allowing setting of variables and rendering of HTML template files
*/
class PMDTemplate {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var array Variables passed to the template
    */
    var $vars = array();
    /**
    * @var string Template File
    */
    var $template = null;
    /**
    * @var string Cache ID to determine if template is cached
    */
    var $cache_id = null;
    /**
    * @var integer Expiration in seconds of cached template file
    */
    var $expire = 900;
    /**
    * @var boolean If a template is cached or not
    */
    var $cached_contents = null;

    /**
    * Template Constructor
    * @param object Registry
    * @param string $template Template File
    * @return void
    */
    function __construct($PMDR, $template = null) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->template = $template;
        $this->cache = $this->PMDR->get('Cache');
    }

    /**
    * Get the URL of a file based on the child/parent template
    * @param string $file
    * @param string $url
    * @return string
    */
    function url($file,$url = BASE_URL) {
        return $this->PMDR->get('Templates')->url($file,$url);
    }

    /**
    * Get the CDN URL of the file based on child/template
    * @param string $file
    * @return string
    */
    function urlCDN($file) {
        return $this->PMDR->get('Templates')->urlCDN($file);
    }

    /**
    * Set a variable to be available in the template
    * @param string $name Variable name
    * @param mixed $value Value of the variable
    * @return void
    */
    function set($name, $value) {
        // We check for method exists so we can pass in other objects for the templates to use if needed (form class)
        $this->vars[$name] = (is_object($value) AND get_class($value) == 'PMDTemplate') ? $value->render() : $value;
    }

    /**
    * Set template variables from an array
    *
    * @param array $data
    */
    function setArray($data) {
        if(!is_array($data)) {
            return false;
        }
        foreach($data AS $key=>$value) {
            $this->set($key, $value);
        }
    }

    /**
    * Render the template parsing all HTML/PHP and returning the result
    * @param mixed $template Template File or Template Object
    * @return string Template html that has been parsed
    */
    function render($template = null) {
        if($this->isCached()) {
            return $this->cached_contents;
        } else {
            if(!$template) $template = $this->template;
            $contents = '';
            if(!is_null($template)) {
                if(defined('TEMPLATE_PATH_PARENT') AND !file_exists($template)) {
                    $template = str_replace(TEMPLATE_PATH,TEMPLATE_PATH_PARENT,$template);
                }
                if(file_exists($template)) {
                    $lang = &$this->PMDR->language;
                    $config = &$this->PMDR->config;
                    $PMDR = &$this->PMDR;
                    extract($this->vars);
                    ob_start();
                    // We do not put begin/end comments here because it breaks IE doctype issues
                    include($template);
                    $contents = ob_get_contents();
                    ob_end_clean();
                } else {
                    trigger_error('Template file not found: '.$template);
                    $contents = '';
                }
                if($this->cache_id) {
                    $this->cache->write(TEMPLATE_PATH.$this->cache_id,$contents);
                }
            }
            return $contents;
        }
    }

    /**
    * Escape javascript
    * @param mixed $value
    * @return mixed
    */
    function escape_js($value) {
        return $this->PMDR->get('Cleaner')->output_js($value);
    }

    /**
    * Escape javascript string
    * @param mixed $value
    * @return mixed
    */
    function escape_js_string($value) {
        return $this->PMDR->get('Cleaner')->output_js_string($value);
    }

    /**
    * Escape a value removing HTML
    * @param mixed $value
    * @return string
    */
    function escape($mixed) {
        if(is_array($mixed)) {
            return array_map(array($this, __FUNCTION__),$mixed);
        } else {
            return nl2br($this->PMDR->get('Cleaner')->clean_output($mixed));
        }
    }

    /**
    * Escape HTML
    * @param mixed $value
    * @return mixed
    */
    function escape_html($value) {
        return $this->PMDR->get('Cleaner')->clean_output_html($value);
    }

    /**
    * Check a permission
    *
    * @param string $permission
    * @return boolean
    */
    function checkPermission($permission) {
        return $this->PMDR->get('Authentication')->checkPermission($permission);
    }

    /**
    * Determine if a template is already cached
    * @return boolean True if cached False if not
    */
    function isCached() {
        if($this->cache_id) {
            return $this->cached_contents = $this->cache->get(TEMPLATE_PATH.$this->cache_id,$this->expire);
        } else {
            return false;
        }
    }

    /**
    * Get a language variable allowing variables
    * @param string $name
    * @param array $variables
    */
    function getLanguage($name, $variables=array()) {
        return $this->PMDR->getLanguage($name,$variables);
    }

    /**
    * Get content for a zone
    * @return string Content
    */
    function zone() {
        $args = func_get_args();
        $content = $this->PMDR->get('Zones')->getContent($args[0]);
        return $content;
    }

    /**
    * Process and display a template block
    * @return string Content
    */
    function block() {
        $args = func_get_args();
        $block_class_name = ucfirst($args[0]).'_Block';
        if(!class_exists($block_class_name) AND file_exists(PMDROOT.'/includes/blocks/'.$args[0].'.php')) {
            require_once(PMDROOT.'/includes/blocks/'.$args[0].'.php');
        }
        if(!class_exists($block_class_name)) {
            return false;
        }
        $block = new $block_class_name($this->PMDR);
        $content = call_user_func_array(array($block, "content"),array_slice($args,1));
        return (is_object($content) AND get_class($content) == 'PMDTemplate') ? $content->render() : $content;
    }
}

/**
* Template Block
*/
abstract class Template_Block {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * Template Block Constructor
    * @param object Registry
    * @return Template_Block
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
    }

    abstract public function content();
}
?>