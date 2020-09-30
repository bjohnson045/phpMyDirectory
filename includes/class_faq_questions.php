<?php
/**
* FAQ Questions Class
*/
class FAQ_Questions extends TableGateway{
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;
    /**
    * Database
    * @var object Database
    */
    var $db;

    /**
    * FAQ Questions constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_FAQ_QUESTIONS;
    }
}
?>