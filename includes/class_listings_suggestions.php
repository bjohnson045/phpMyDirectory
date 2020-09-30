<?php
/**
* Listing Suggestions
*/
class Listings_Suggestions extends TableGateway{
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
    * Listing suggestions constructor
    * @param object $PMDR
    * @return Listings_Suggestions
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_LISTINGS_SUGGESTIONS;
    }

    /**
    * Clear all listing suggestions
    * @return void
    */
    function clear() {
        $this->db->Execute("TRUNCATE ".T_LISTINGS_SUGGESTIONS);
    }

    /**
    * Insert listing suggestion
    * @param array $data Listing suggestion data
    * @return void
    */
    function insert($data) {
        $data['date'] = $this->PMDR->get('Dates')->dateTimeNow();
        parent::insert($data);
    }
}
?>