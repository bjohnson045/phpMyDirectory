<?php
/**
* Tax
*/
class Tax extends TableGateway {
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
    * Tax constructor
    * @param object $PMDR
    * @return Tax
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_TAX;
    }
}
?>