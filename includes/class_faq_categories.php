<?php
/**
* FAQ Categories class
*/
class FAQ_Categories extends TableGateway {
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
    * FAQ Categories constructor
    * @param object $PMDR
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_FAQ_CATEGORIES;
    }

    /**
    * Delete FAQ category
    * @param int $id FAQ category ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_FAQ_CATEGORIES." WHERE id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_FAQ_QUESTIONS." WHERE category_id=?",array($id));
    }
}
?>