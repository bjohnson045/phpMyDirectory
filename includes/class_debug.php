<?php
/**
* Debugging class
*/
class Debug {
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
    * Debug Enabled
    * @var boolean
    */
    var $debug = false;

    /**
    * Debug Constructor
    * @param object $PMDR
    * @return Debug
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        if(DEBUG_MODE OR @$_SESSION['debug_mode']) {
            $this->enable();
        }
    }

    /**
    * Enable debugging
    */
    function enable() {
        $this->debug = true;
        if(!$this->PMDR->get('Session')->get('debug_mode')) {
            $this->PMDR->get('Session')->set('debug_mode',true);
        }
        $this->PMDR->set('load_time_start',microtime(1));
        $this->PMDR->set('memory_start',memory_get_usage());
        $this->db->LogSQL(T_LOG_SQL);
    }

    /**
    * Disable debugging
    */
    function disable() {
        $this->debug = false;
        $this->PMDR->get('Session')->delete('debug_mode');
        $this->db->LogSQL(null);
    }

    /**
    * Get debugging output
    * @return string
    */
    function getOutput() {
        $debug = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_debug.tpl');
        $debug->set('query_list', $this->db->query_list);
        $debug->set('query_count', $this->db->query_count - 3); // Subtract 3 for the queries we must run before logging begins
        $debug->set('email_errors',$this->PMDR->get('Email_Handler')->getLog());
        $debug->set('page_load_time',round(microtime(1)-$this->PMDR->get('load_time_start'),5));
        $debug->set('memory_start',$this->PMDR->get('memory_start'));
        $debug->set('memory_end',memory_get_usage());
        $debug->set('memory_peak',memory_get_peak_usage());
        $debug->set('current_template',$this->PMDR->getConfig('template'));
        $debug->set('current_language',$this->PMDR->getLanguage('title'));
        return $debug->render();
    }
}
?>