<?php
/**
* Banners Class
*/
class Banners extends TableGateway {
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
    * Banners constructor
    * @param object $PMDR
    * @return Banners
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_BANNERS;
    }

    /**
    * Get a row (banner) from the database
    * @param int $id Banner ID to get
    * @return array Banner result
    */
    function getRow($id) {
        $banner = $this->db->GetRow("SELECT b.*, bt.width, bt.height, bt.type FROM ".T_BANNERS." b, ".T_BANNER_TYPES." bt WHERE b.type_id=bt.id AND b.id=?",array($id));
        $banner['categories'] = $this->db->GetCol("SELECT category_id FROM ".T_BANNERS_CATEGORIES." WHERE banner_id=?",array($id));
        $banner['locations'] = $this->db->GetCol("SELECT location_id FROM ".T_BANNERS_LOCATIONS." WHERE banner_id=?",array($id));
        return $banner;
    }

    /**
    * Insert a banner into the database
    * @param array $data Banner data
    * @return int Banner ID
    */
    function insert($data) {
        $id = parent::insert($data);
        $this->updateCategories($id,$data['categories']);
        $this->updateLocations($id,$data['locations']);
        if(!empty($data['image'])) {
            $this->processImage($data,$id);
        }
        return $id;
    }

    /**
    * Update an existing banner
    * @param array $data Banner data
    * @param int $id Banner ID
    * @return void
    */
    function update($data,$id) {
        parent::update($data,$id);
        $this->updateCategories($id,$data['categories']);
        $this->updateLocations($id,$data['locations']);
        if(!empty($data['image'])) {
            $this->processImage($data,$id);
        }
    }

    /**
    * Process a banner image
    * @param mixed $data
    * @param mixed $id
    * @return void
    */
    function processImage($data,$id) {
        @unlink(find_file(BANNERS_PATH.$id.'.*'));
        $type_info = $this->db->GetRow("SELECT * FROM ".T_BANNER_TYPES." WHERE id=?",array($data['type_id']));
        $options = array(
            'width'=>$type_info['width'],
            'height'=>$type_info['height'],
            'enlarge'=>true,
            'crop'=>true
        );
        if($extension = $this->PMDR->get('Image_Handler')->process($data['image'],BANNERS_PATH.$id.'.*',$options)) {
            $this->update(array('extension'=>$extension),$id);
        }
    }

    /**
    * Delete a banner
    * @param int $id Banner ID
    * @return void
    */
    function delete($id) {
        @unlink(find_file(BANNERS_PATH.$id.'.*'));
        $this->db->Execute("DELETE FROM ".T_BANNERS_CATEGORIES." WHERE banner_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_BANNERS_LOCATIONS." WHERE banner_id=?",array($id));
        parent::delete($id);
    }

    /**
    * Delete banners by listing ID
    * @param int $id
    * @param string|null $type String if deleting by a specific type, otherwise null
    * @return void
    */
    function deleteByListID($id, $type = NULL) {
        if(!is_null($type)) {
            $banner = $this->db->Execute("SELECT id FROM ".T_BANNERS." WHERE listing_id=? AND type_id=?",array($id,$type));
        } else {
            $banner = $this->db->Execute("SELECT id FROM ".T_BANNERS." WHERE listing_id=?",array($id));
        }
        while($b = $banner->FetchRow()) {
            $this->delete($b['id']);
        }
    }

    /**
    * Get banner types
    * @return array Banner types
    */
    function getTypes() {
        return $this->db->GetAll("SELECT * FROM ".T_BANNER_TYPES);
    }

    /**
    * Update banner categories
    * @param int $id Banner ID
    * @param array $categories Banner categories
    * @return void
    */
    function updateCategories($id, $categories = array()) {
        $this->db->Execute("DELETE FROM ".T_BANNERS_CATEGORIES." WHERE banner_id=?",array($id));

        if(!empty($categories) AND $banner = $this->db->GetRow("SELECT b.id, b.listing_id FROM ".T_BANNERS." b INNER JOIN ".T_BANNER_TYPES." bt ON b.type_id=bt.id WHERE b.id=?",array($id))) {
            if(!is_null($banner['listing_id'])) {
                $listing_categories = $this->db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=?",array($banner['listing_id']));
                $listing_categories_all = array();
                foreach($listing_categories AS $listing_category) {
                    $listing_categories_all[] = $listing_category;
                    $parent_categories = $this->PMDR->get('Categories')->getParentIDArray($listing_category);
                    $listing_categories_all = array_merge($listing_categories_all,$parent_categories);
                }
                $listing_categories_all = array_unique($listing_categories_all);
                foreach($listing_categories_all AS $listing_category) {
                    $this->db->Execute("INSERT INTO ".T_BANNERS_CATEGORIES." (banner_id,category_id) VALUES (?,?)",array($id,$listing_category));
                }
            } else {
                if($categories == '') $categories = array();

                if(!is_array($categories)) {
                    $categories = array($categories);
                }
                if(!empty($categories)) {
                    foreach($categories as $category) {
                        $value_string .= '('.$id.','.$category.'),';
                    }
                    $this->db->Execute("INSERT INTO ".T_BANNERS_CATEGORIES." (banner_id,category_id) VALUES ".trim($value_string,','));
                }
            }
        }
    }

    /**
    * Update banner locations
    * @param int $id Banner ID
    * @param array $locations Banner locations
    * @return void
    */
    function updateLocations($id, $locations = array()) {
        $this->db->Execute("DELETE FROM ".T_BANNERS_LOCATIONS." WHERE banner_id=?",array($id));
        if(!empty($locations) AND $banner = $this->db->GetRow("SELECT b.id, b.listing_id FROM ".T_BANNERS." b INNER JOIN ".T_BANNER_TYPES." bt ON b.type_id=bt.id WHERE b.id=?",array($id))) {
            if(!is_null($banner['listing_id'])) {
                $this->db->Execute("INSERT INTO ".T_BANNERS_LOCATIONS." (banner_id,location_id) SELECT ".$id.", location_id FROM ".T_LISTINGS." WHERE id=?",array($banner['listing_id']));
            } else {
                if($locations == '') $locations = array();

                if(!is_array($locations)) {
                    $locations = array($locations);
                }
                if(!empty($locations)) {
                    foreach($locations as $location) {
                        $value_string .= '('.$id.','.$location.'),';
                    }
                    $this->db->Execute("INSERT INTO ".T_BANNERS_LOCATIONS." (banner_id,location_id) VALUES ".trim($value_string,','));
                }
            }
        }
    }
}
?>