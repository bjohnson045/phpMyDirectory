<?php
/**
* Banner Types
*/
class Banners_Types extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * Banner Types Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->table = T_BANNER_TYPES;
    }

    /**
    * Insert a banner type
    * @param array $data Banner type data
    * @return int Banner type ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->db->Execute("ALTER TABLE ".T_MEMBERSHIPS." ADD banner_limit_$id SMALLINT(6) NOT NULL");
        $this->db->Execute("ALTER TABLE ".T_LISTINGS." ADD banner_limit_$id SMALLINT(6) NOT NULL");
        return $id;
    }

    /**
    * Update a banner type
    * @param array $data Banner type data
    * @param int $id Banner ID
    * @return void
    */
    function update($data, $id) {
        parent::update($data, $id);
        $banners = $this->db->GetAll("SELECT id FROM ".T_BANNERS." WHERE listing_id IS NULL AND type_id=?",array($id));
    }

    /**
    * Get banner types in an associative array
    * @param string|null $type If a string the type to get
    * @return array Associative array of banner types with the ID as keys
    */
    function getTypesAssoc($type = null) {
        if(!is_null($type)) {
            $where_sql = " WHERE type=".$this->PMDR->get('Cleaner')->clean_db($type);
        }
        return $this->db->GetAssoc("SELECT id, name FROM ".T_BANNER_TYPES."$where_sql");
    }

    /**
    * Delete a banner type
    * @param int $id Banner type ID
    * @return void
    */
    function delete($id) {
        $this->db->Execute("ALTER TABLE ".T_MEMBERSHIPS." DROP banner_limit_".$id);
        $this->db->Execute("ALTER TABLE ".T_LISTINGS." DROP banner_limit_".$id);
        $banners = $this->db->GetCol("SELECT id FROM ".T_BANNERS." WHERE type_id=?",array($id));
        foreach($banners AS $banner_id) {
            $this->PMDR->get('Banners')->delete($banner_id);
        }
        parent::delete($id);
    }
}
?>