<?php
/**
* Favorites class
*/
class Favorites extends TableGateway {
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
    * Favorites constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_FAVORITES;
    }
}
?>