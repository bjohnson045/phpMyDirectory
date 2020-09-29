<?php
/**
* Search Log
*/
class Search_Log extends TableGateway {
    /**
    * Registry object
    * @var Registry
    */
    var $PMDR;
    /**
    * Database object
    * @var Database
    */
    var $db;

    /**
    * Search log constructor
    * @param object $PMDR
    * @return Search_Log
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_SEARCH_LOG;
    }

    /**
    * Clear the search log
    * @return void
    */
    function clear() {
        $this->db->Execute("TRUNCATE ".T_SEARCH_LOG);
    }
}
?>