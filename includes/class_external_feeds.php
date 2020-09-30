<?php
/**
* External Feeds
*/
class External_Feeds extends TableGateway {
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
    * External Feeds constructor
    * @param object $PMDR
    * @return External_Feeds
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_FEEDS_EXTERNAL;
    }
}
?>