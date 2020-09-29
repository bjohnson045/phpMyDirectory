<?php
/**
* Sitemap XML class
*/
class Sitemap_XML extends TableGateway {
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
    * Sitemap XML constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_SITEMAP_XML;
    }

    function getURLs() {
        return $this->db->GetAll("SELECT * FROM ".T_SITEMAP_XML." WHERE active=1 ORDER BY ordering");
    }
}
?>