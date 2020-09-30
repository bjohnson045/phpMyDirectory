<?php
/**
 * Categories Class
 * Handles category specific functions
 */
class Classifieds_Categories extends Categories {
    /**
    * Categories table
    * @var string
    */
    var $table = T_CLASSIFIEDS_CATEGORIES;
    var $table_related = T_CLASSIFIEDS_CATEGORIES_RELATED;
    var $table_fields = T_CLASSIFIEDS_CATEGORIES_FIELDS;
    var $table_lookup = T_CLASSIFIEDS_CATEGORIES_LOOKUP;
    var $table_type = T_CLASSIFIEDS;
    var $image_path = CLASSIFIEDS_CATEGORY_IMAGE_PATH;
    var $file_name = 'admin_classifieds_categories.php';
    var $category_lookup_field = 'category_id';

    /**
    * Key to identify the data
    * @var string
    */
    var $type = 'classifieds_category';

    /**
    * Get category results for the tree select field
    * @param string $where
    * @return array
    */
    function getTreeSelect($where) {
        $fields = array('id', 'left_', 'right_', 'parent_id', 'level', 'title');
        if(!empty($where)) {
            $where = 'WHERE '.$where;
        }
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." $where ORDER BY left_");
    }

    function getRoots(){
        return $this->db->GetAssoc("SELECT id, friendly_url FROM ".$this->table." WHERE level = 1");
    }

    /**
    * Get raw count from database
    * @param int $id Category ID
    * @return int
    * NOTE: Remove once we rename cat_id to category_id for listings category lookup table
    */
    function getRawCount($id) {
        $category = $this->db->getRow("SELECT left_, right_ FROM ".$this->table." WHERE id=?",array($id));
        return $this->db->GetOne("SELECT COUNT(*) FROM ".$this->table." c INNER JOIN ".$this->table_lookup." lc ON c.id=lc.category_id WHERE c.left_ BETWEEN ? AND ?",array($category['left_'],$category['right_']));
    }
}
?>