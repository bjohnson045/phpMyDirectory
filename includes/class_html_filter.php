<?php
/**
* HTML Filter Class
*/
class HTML_Filter {
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;
    var $filter;
    var $configurations;

    /**
    * HTML Filter constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->filter = new HTMLPurifier();
    }

    /**
    * Process Filtering
    * @param mixed $value Value(s) to perform filtering on
    * @param array $tags Tags/attributes to allow
    * @return mixed Filtered values
    */
    function process($value, $tags = array()) {
        $config_key = md5(serialize($tags));
        if(!isset($this->configurations[$config_key])) {
            $config = HTMLPurifier_Config::createDefault();
            if(defined('CHARSET') AND CHARSET != '') {
                $config->set('Core.Encoding', CHARSET);
            }
            $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
            $config->set('Cache.DefinitionImpl', 'Serializer');
            $config->set('Cache.SerializerPath', CACHE_PATH);
            $config->set('HTML.TidyLevel', 'none');
            $config->set('Core.RemoveProcessingInstructions',true);

            // Everything is allowed
            if(is_null($tags)) {
                $config->set('HTML.Allowed', null);
                $config->set('Attr.EnableID',true);
            // Only certain tags/attributes are allowed
            } elseif(is_array($tags) AND count($tags)) {
                $config->set('HTML.Allowed', $tags);
            // Nothing is allowed
            } else {
                $config->set('HTML.Allowed', '');
            }
            $config->set('Output.FlashCompat',true);
            $config->set('HTML.SafeObject', true);
            $config->set('HTML.SafeIframe', true);
            $config->set('URI.SafeIframeRegexp','/(https?:)?\/\/(www\.youtube\.com|www\.vimeo\.com|player\.vimeo\.com).*/');
            $config->set('Attr.AllowedFrameTargets',array('_blank','_self','_parent','_top'));
            $this->configurations[$config_key] = $config;
            unset($config);
        }

        return $this->run_filter($value,$this->configurations[$config_key]);
    }

    /**
    * Run the filter on a set of values
    * @param mixed $value
    * @param array $config
    * @return HTMLPurifier
    */
    function run_filter($value,$config) {
        if(is_array($value)) {
            return array_map(array($this, __FUNCTION__),$value);
        } else {
            return $this->filter->purify($value,$config);
        }
    }
}
?>