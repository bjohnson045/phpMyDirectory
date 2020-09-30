<?php
/**
* Email log class
*/
class Email_Log extends TableGateway {
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
    * Email log constructor
    * @param object $PMDR
    * @return Email_Log
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_EMAIL_LOG;
    }
}
?>