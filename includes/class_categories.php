<?php
/**
 * Categories Class
 * Handles category specific functions
 */
class Categories extends Tree_Gateway {
    /**
    * Categories table
    * @var string
    */
    var $table = T_CATEGORIES;
    var $table_related = T_CATEGORIES_RELATED;
    var $table_fields = T_CATEGORIES_FIELDS;
    var $table_lookup = T_LISTINGS_CATEGORIES;
    var $table_type = T_LISTINGS;
    var $image_path = CATEGORY_IMAGE_PATH;
    var $linked_tables = array(T_BANNERS_CATEGORIES);
    var $file_name = 'admin_categories.php';
    var $category_lookup_field = 'cat_id';
    var $url_prefix;

    /**
    * Key to identify the data
    * @var string
    */
    var $type = 'category';

    function __construct($PMDR) {
        parent::__construct($PMDR);
        $this->url_prefix = $this->PMDR->getConfig('category_mod_rewrite');
    }

    /**
    * Insert a category
    * @param array $data Category data
    * @return mixed Boolean false if failure, otherwise ID
    */
    function insert($data) {
        $data['friendly_url'] = Strings::rewrite((trim($data['friendly_url']) != '') ? $data['friendly_url'] : $data['title']);
        if($data['placement_id'] == '') {
            $data['placement_id'] = '1';
            $data['placement'] = 'subcategory';
        }

        foreach($data AS $key=>$data_field) {
            if(strstr($key,'custom_') AND is_array($data_field)) {
                $data[$key] = implode("\n",$data_field);
            }
        }

        if($data['placement'] == 'before') {
            $node_data = $this->newPreviousSibling($data['placement_id']);
        } elseif($data['placement'] == 'after') {
            $node_data = $this->newNextSibling($data['placement_id']);
        } elseif($data['placement'] == 'subcategory') {
            $node_data = $this->newLastChild($data['placement_id']);
        }

        if(!$node_data) {
            trigger_error('Invalid category placement ID '.$data['placement_id'].' while inserting a category.',E_USER_WARNING);
            return false;
        }

        $data = array_merge($data,$node_data);

        if(!$id = $this->PMDR->getNew('TableGateway',$this->table)->insert($data)) {
            parent::delete($node_data);
        }

        $this->updateFriendlyPath($id);
        if(isset($data['related'])) {
            $this->updateRelatedCategories($id,$data['related']);
        }
        if(isset($data['fields'])) {
            $this->updateFields($id,$data['fields']);
        }

        if(!empty($data['small_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['small_image'],$this->image_path.$id.'-small.*');
        }
        if(!empty($data['large_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['large_image'],$this->image_path.$id.'.*');
        }
        if(!empty($data['map_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['map_image'],$this->image_path.$id.'-map.*');
        }

        $this->PMDR->get('Cache')->deletePrefix('categories');

        return $id;
    }

    /**
    * Update category
    * @param array $data
    * @param int $id Category ID
    */
    function update($data, $id) {
        $category = $this->db->GetRow("SELECT id, left_, right_, closed FROM ".$this->table." WHERE id=?",array($id));
        $data['friendly_url'] = Strings::rewrite((trim($data['friendly_url']) != '') ? $data['friendly_url'] : $data['title']);
        if(empty($data['display_columns'])) {
            $data['display_columns'] = null;
        }

        foreach($data AS $key=>$data_field) {
            if(strstr($key,'custom_') AND is_array($data_field)) {
                $data[$key] = implode("\n",$data_field);
            }
        }

        // If close is selected, close its sublocations as well.  Else check if it is already closed and if so, open its subcategories also.
        if($data['closed']) {
            $this->db->Execute("UPDATE ".$this->table." SET closed=1 WHERE left_ > ? AND right_ < ?",array($category['left_'],$category['right_']));
        } elseif($category['closed']) {
            $this->db->Execute("UPDATE ".$this->table." SET closed=0 WHERE left_ > ? AND right_ < ?",array($category['left_'],$category['right_']));
        }

        $this->PMDR->getNew('TableGateway',$this->table)->update($data,$id);

        if($data['placement'] != '' AND $data['placement_id'] != '' AND !$this->isAChildOf($data['placement_id'],$id) AND $id != $data['placement_id']) {
            if($data['placement'] == 'before') {
                $this->moveToPreviousSibling($id,$data['placement_id']);
            } elseif($data['placement'] == 'after') {
                $this->moveToNextSibling($id,$data['placement_id']);
            } elseif($data['placement'] == 'subcategory') {
                $this->moveToFirstChild($id,$data['placement_id']);
            }
        }

        $this->updateFriendlyPath($id);
        $this->updateRelatedCategories($id,$data['related']);
        $this->updateFields($id,$data['fields']);

        if($data['small_image_delete']) unlink(find_file($this->image_path.$id.'-small.*'));
        if($data['large_image_delete']) unlink(find_file($this->image_path.$id.'.*'));
        if($data['map_image_delete']) unlink(find_file($this->image_path.$id.'-map.*'));

        if(!empty($data['small_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['small_image'],$this->image_path.$id.'-small.*');
        }
        if(!empty($data['large_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['large_image'],$this->image_path.$id.'.*');
        }
        if(!empty($data['map_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['map_image'],$this->image_path.$id.'-map.*');
        }

        $this->PMDR->get('Cache')->deletePrefix('categories');
    }

    /**
    * Delete category
    * @param int $id Category ID
    * @return void
    */
    function delete($id) {
        @unlink(find_file($this->image_path.$id.'.*'));
        @unlink(find_file($this->image_path.$id.'.-small*'));
        $this->db->Execute("DELETE FROM ".$this->table_fields." WHERE category_id=?",array($id));
        $this->db->Execute("DELETE FROM ".$this->table_related." WHERE category_id=?",array($id));
        $this->db->Execute("DELETE FROM ".$this->table_lookup." WHERE ".$this->category_lookup_field."=?",array($id));
        if(isset($this->linked_tables)) {
            foreach($this->linked_tables AS $table) {
                $this->db->Execute("DELETE FROM ".$table." WHERE category_id=?",array($id));
            }
        }
        parent::delete($id);
    }

    /**
    * Update a category related categories
    * @param int $id
    * @param array $related_caregories
    */
    function updateRelatedCategories($id,$related_caregories) {
        if(!is_array($related_caregories)) {
            $related_caregories = array();
        }
        $this->db->Execute("DELETE FROM ".$this->table_related." WHERE category_id=? OR related_category_id=?",array($id,$id));
        foreach($related_caregories as $category_related_id) {
            if($id != $category_related_id) {
                $this->db->Execute("INSERT INTO ".$this->table_related." (category_id,related_category_id) VALUES (?,?), (?,?)",array($id,$category_related_id,$category_related_id,$id));
            }
        }
    }

    /**
    * Update a categories fields
    * @param int $id
    * @param array $fields
    */
    function updateFields($id,$fields) {
        if(!is_array($fields)) {
            $fields = array();
        }
        $this->db->Execute("DELETE FROM ".$this->table_fields." WHERE category_id=?",array($id));
        foreach($fields as $field_id) {
            $this->db->Execute("INSERT INTO ".$this->table_fields." (category_id,field_id) VALUES (?,?)",array($id,$field_id));
        }
    }

    /**
    * Update category fields by field ID
    *
    * @param array $categories
    * @param int $field_id
    */
    function updateFieldsByID($categories,$field_id) {
        $this->db->Execute("DELETE FROM ".$this->table_fields." WHERE field_id=?",array($field_id));
        if($category_count = count($categories)) {
            if($category_count == $this->getCount()) {
                $this->db->Execute("INSERT IGNORE INTO ".$this->table_fields." (category_id,field_id) SELECT id, ? FROM ".$this->table,array($field_id));
            } else {
                foreach($categories AS $category) {
                    $this->db->Execute("INSERT INTO ".$this->table_fields." (category_id,field_id) VALUES(?,?)",array($category,$field_id));
                }
            }
        }
    }

    /**
    * Get raw count from database
    * @param int $id Category ID
    * @return int
    */
    function getRawCount($id) {
        $category = $this->db->getRow("SELECT left_, right_ FROM ".$this->table." WHERE id=?",array($id));
        return $this->db->GetOne("SELECT COUNT(*) FROM ".$this->table." c INNER JOIN ".$this->table_lookup." lc ON c.id=lc.".$this->category_lookup_field." WHERE c.left_ BETWEEN ? AND ?",array($category['left_'],$category['right_']));
    }

    /**
    * Get category level labels from language variables
    * @return array Associative array containing language variables as keys and labels as values
    */
    function getLevelLabels() {
        return $this->db->GetAssoc("SELECT SUBSTR(variablename,-1), content FROM ".T_LANGUAGE_PHRASES." WHERE (languageid=-1 OR languageid=".$this->PMDR->getConfig('language_admin').") AND variablename LIKE 'general_categories_levels_%' ORDER BY variablename ASC");
    }

    /**
    * Update language variables
    * @return void
    */
    function updateLanguageVariables() {
        $current_level = $this->db->GetOne("SELECT COUNT(*) as count FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_categories_levels_%' AND languageid=-1");
        $max_level = $this->db->GetOne("SELECT MAX(level) as count FROM ".$this->table);

        if($max_level > $current_level) {
            for($x=$current_level; $x < $max_level; $x++) {
                $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." SET languageid=-1, section='general_categories', variablename='general_categories_levels_".($x+1)."', content='Category Level ".($x+1)."'");
            }
        } elseif($max_level < $current_level) {
            for($x=$max_level; $x < $current_level; $x++) {
                $this->db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_categories_levels_".($x+1)."'");
            }
        }
    }

    /**
    * Get Listings
    * @param int $category_id Category ID
    * @param bool $only_count Only get number of listings
    * @param int $limit_start Limit starting number
    * @param int $limit_number Number to retreive
    * @param string $orderby ORDER BY query section
    */
    function getListings($category_id, $only_count=false, $limit_start=false, $limit_number=false, $orderby = '') {
        if($limit_start) $limit_sql = 'LIMIT '.$limit_start.', '.$limit_number.' ';
        if($orderby != '') $orderby = 'ORDER BY '.$orderby;
        $sql = $only_count ? 'SELECT COUNT(*) AS count' : 'SELECT '.$this->table_type.'.*';

        $sql .="
                FROM ".$this->table_type." INNER JOIN ".$this->table_lookup." ON ".$this->table_type.".id=".$this->table_lookup.".list_id
                WHERE ".$this->lookup_Table.".".$this->category_lookup_field."='$category_id'
                    AND ".$this->table_type.".status = 'active'
                $orderby $limit_sql";

        return $this->db->GetAll($sql);
    }

    /**
    * Parse for browsing
    * Parses category results for proper display
    * @param array $results
    * @return array Parsed results
    */
    function parseForBrowsing($results, $columns_number = null) {
        if(count($results)) {
            if(is_null($columns_number) OR $columns_number == 0) {
                $columns_number = $this->PMDR->getConfig('category_num_columns');
            }
            // Initialize our first level count
            $first_level_count = 0;
            // Initialize our columns array
            $columns = array();
            // Set the root level we are currently on
            $first_level_count_reference = $results[0]['level'];
            // Loop through all categories checking their level and if we are displaying it.  This is used to correctly build our table.  We do it here with
            // PHP because a DB query is most likely slower.
            if(is_array($results)) {
                for ($i=0;$i<sizeof($results);$i++) {
                    if($first_level_count_reference == $results[$i]['level'] AND ($results[$i]['count_total'] > 0 OR !$this->PMDR->getConfig('cat_empty_hidden')) AND !$results[$i]['hidden']) {
                        $first_level_count++;
                    }
                }
            }

            if($this->PMDR->getConfig('category_vertical_sort')) {
                $records_per_column = floor($first_level_count/$columns_number);
                $extras_per_column = $first_level_count % $columns_number;
            }

            $j = 0;

            // Loop through our records
            if(is_array($results)) {
                for ($i=0;$i<sizeof($results);$i++) {
                    // We we want to hide empty and count is 0 or if its not a first level category or hidden, we skip it (subcategories come later)
                    if(($this->PMDR->getConfig('cat_empty_hidden') AND $results[$i]['count_total'] == 0) OR ($results[$i]['level'] != $first_level_count_reference) OR $results[$i]['hidden']) {
                        continue;
                    }

                    if($this->PMDR->getConfig('category_vertical_sort')) {
                        if(count($columns) <= $extras_per_column) {
                            $column_index = floor($j/($records_per_column+1));
                        } else {
                            $column_index = floor(($j-$extras_per_column)/$records_per_column);
                        }
                    } else {
                        $column_index = $j % $columns_number;
                    }
                    $columns[$column_index][$i] = $results[$i];

                    if(!empty($results[$i]['small_image_url'])) {
                        $columns[$column_index][$i]['image'] = $results[$i]['small_image_url'];
                    } else {
                        $columns[$column_index][$i]['image'] = get_file_url_cdn(CATEGORY_IMAGE_PATH.$results[$i]['id'].'-small.*');
                    }

                    $sub_counter = 0;
                    // If we have set to display more than 0 subcats
                    if ($this->PMDR->getConfig('show_subs_number') > 0) {
                        // Loop through remaining categories starting from where we left off ($i)
                        if(is_array($results)) {
                            for($y=$i+1; $y<sizeof($results); $y++) {
                                // Its a higher level, so we know its a subcat, continue
                                if($results[$y]['level'] > $results[$i]['level']) {
                                    // If we want to hide empty counts and count = 0 or its hidden, we skip it
                                    if(($this->PMDR->getConfig('cat_empty_hidden') AND $results[$y]['count_total'] == 0) OR $results[$y]['hidden']) {
                                        continue;
                                    }
                                    if($sub_counter < $this->PMDR->getConfig('show_subs_number')) {  // subtract one because we count before this comparison
                                        $columns[$column_index][$i]['more_children'] = false;
                                        $columns[$column_index][$i]['children'][] = $results[$y];
                                        $sub_counter++; // count how many subcats we add
                                    } else {
                                        $columns[$column_index][$i]['more_children'] = true;
                                    }
                                } else {  // break if we run out of categories, or next one is a main category
                                    break;
                                }
                            }
                        }
                        $i = $y-1;
                    }

                    $j++;
                }
            }
        } else {
            $columns = array();
        }

        return $columns;
    }

    /**
    * Get category URL
    * @param int $id Category ID
    * @param string $category_path Category friendly URL path
    * @param int $location_id Location ID
    * @param string $location_path Location friendly URL path
    */
    function getURL($id=null, $category_path, $location_id = null, $location_path = null) {
        if(MOD_REWRITE) {
            $url_string = rtrim(BASE_URL_NOSSL.'/'.$this->PMDR->getConfig('category_mod_rewrite'),'/').'/'.$category_path;
            if(!is_null($location_path)) {
                $url_string .= $this->PMDR->getConfig('location_mod_rewrite').'/'.$location_path;
            }
        } else {
            $url_string = BASE_URL_NOSSL.'/browse_categories.php?id='.$id;
            if(!is_null($location_id)) {
                $url_string .= '&location='.$location_id;
            }
        }
        return $url_string;
    }

    /**
    * Check category table and reset if needed
    * @return boolean True if reset was performed, false otherwise
    */
    function checkReset() {
        if(!$this->db->GetOne("SELECT COUNT(*) FROM ".$this->table)) {
            $this->db->Execute("INSERT INTO ".$this->table." (id,title,left_,right_,parent_id,level) VALUES (1,'ROOT',0,1,NULL,0)");
            $this->db->Execute("ALTER TABLE ".$this->table." AUTO_INCREMENT=2");
            return true;
        }
        return false;
    }

    /**
    * Get category children
    * @param int $id
    * @param int $levels
    * @param int $child_limit
    * @param string $where
    * @param array $fields
    * @return array
    */
    function getChildren($id, $levels=1, $child_limit=null, $where='', $fields=array('id','level', 'left_', 'right_', 'count', 'count_total', 'friendly_url', 'friendly_url_path', 'link', 'impressions', 'description_short', 'hidden', 'no_follow', 'display_columns', 'closed', 'small_image_url')) {
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return parent::getChildren($id,$levels,$child_limit,$where,$fields);
    }

    /**
    * Get a single category
    * @param int $id
    * @return array
    */
    function get($id) {
        $title = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetRow("SELECT *, $title FROM ".$this->table." WHERE id=?",array($id));
    }

    /**
    * Get categories from an ID list
    * @param string $ids
    * @return array
    */
    function getByIDList($ids) {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'level', 'count', 'count_total', 'hidden', 'link', 'no_follow', 'description_short', 'small_image_url');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE id IN(".$ids.")");
    }

    /**
    * Get root categories
    * @return array
    */
    function getRoots() {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'level AS depth', 'count', 'count_total', 'hidden', 'link', 'no_follow');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE level=1 ORDER BY left_");
    }

    /**
    * Get popular categories
    * @param int $limit
    * @return array
    */
    function getPopular($limit = 10) {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'impressions', 'level', 'count', 'hidden', 'count_total', 'description_short');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE level=1 AND impressions > 0 AND hidden=0 ORDER BY impressions DESC LIMIT ?",array(intval($limit)));
    }

    /**
    * Get an associative category result
    * @return array
    */
    function getAssoc() {
        $fields = array('id');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAssoc("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE hidden=0 AND level=1 ORDER BY left_");
    }

    /**
    * Get a category path
    * @param int $id
    * @return array
    */
    function getPath($id)    {
        $fields = array('parent.id', 'parent.friendly_url', 'parent.friendly_url_path', 'parent.no_follow', 'parent.level', 'parent.parent_id', 'parent.link');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title','parent.');
        $result = $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." AS node INNER JOIN ".$this->table." AS parent ON node.left_ BETWEEN parent.left_ AND parent.right_ WHERE node.id = ? ORDER BY parent.left_;",array($id));
        array_shift($result);
        return $result;
    }

    /**
    * Get category results for the cascading select field
    * @param int $parent_id
    * @param string $where
    * @return array
    */
    function getCascadingSelect($parent_id,$where='') {
        $fields = array('id', 'left_', 'right_', 'parent_id', 'level');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE parent_id=? $where ORDER BY left_",array($parent_id));
    }

    /**
    * Get category results for the tree select field
    * @param string $where
    * @return array
    */
    function getTreeSelect($where) {
        $fields = array('id', 'left_', 'right_', 'parent_id', 'level');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        if(!empty($where)) {
            $where = 'WHERE '.$where;
        }
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." $where ORDER BY left_");
    }

    /**
    * Get category results for select field
    * @param string $where
    * @param array $filter
    * @return array
    */
    function getSelect($where = array(),$filter=array()) {
        $fields = array('id', 'level', 'left_', 'right_');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        $where_sql = 'WHERE ID != 1';
        if(!empty($where)) {
            $where_sql .= ' AND '.implode('=? AND ',array_keys($where)).'=?';
        }
        if(!empty($filter)) {
            $where_sql .= ' AND id IN('.$filter.')';
        }
        return $this->db->GetAssoc("SELECT ".implode(',',$fields)." FROM ".$this->table." $where_sql ORDER BY left_",array_values($where));
    }

    /**
    * Get related categories
    * @param int $id Category ID to find related categories
    * @return array
    */
    function getRelated($id) {
        $fields = array('id', 'friendly_url_path');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        $related_categories = $this->db->GetAll("SELECT id, title, friendly_url_path, link FROM ".$this->table." c INNER JOIN ".$this->table_related." cr ON c.id=cr.related_category_id WHERE cr.category_id=?",array($id));
        foreach($related_categories AS &$related_category) {
            if($related_category['link'] == '') {
                $related_category['url'] = $this->getURL($related_category['id'],$related_category['friendly_url_path']);
            } else {
                $related_category['url'] = $related_category['link'];
            }
        }
        return $related_categories;
    }

    /**
    * Get a single category ID
    * Used when only one category exists but we do not know the ID
    * @return array
    */
    function getOneID() {
        return $this->db->GetOne("SELECT id FROM ".$this->table." WHERE id!=1 LIMIT 1");
    }

    /**
    * Check if a category is closed
    * @param int $id
    * @return boolean
    */
    function isClosed($id) {
        return $this->db->GetOne("SELECT closed FROM ".$this->table." WHERE id=?",array($id));
    }

    /**
    * Update category counters
    */
    function updateCounters() {
        $this->db->Execute("UPDATE ".$this->table." SET count=0, count_total=0");

        $this->db->Execute("UPDATE ".$this->table." c,
        (SELECT cat.id AS id, COUNT(lc.list_id) AS count
        FROM ".$this->table." AS cat, ".$this->table_lookup." AS lc, ".$this->table_type." AS l
        WHERE cat.id = lc.".$this->category_lookup_field." AND lc.list_id=l.id AND l.status='active' GROUP BY cat.id) cd
        SET c.count=cd.count WHERE c.id=cd.id");

        $this->db->Execute("UPDATE ".$this->table." c,
        (SELECT parent.id AS id, SUM(node.count) as count
        FROM ".$this->table." AS parent, ".$this->table." AS node
        WHERE node.left_ BETWEEN parent.left_ AND parent.right_
        GROUP BY parent.id) cd
        SET c.count_total = cd.count WHERE c.id=cd.id");
    }

    /**
    * Get sitemap results
    * @return array
    */
    function getSitemap() {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'level', 'count', 'count_total', 'hidden', 'left_', 'right_');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE id!=1 ORDER BY left_");
    }

    /**
    * Get category used in searches
    * @param int $id
    * @return array
    */
    function getSearchResult($id) {
        $fields = array('id', 'friendly_url_path', 'description_short', 'description');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetRow("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE id=?",array($id));
    }
    /**
    * Get admin category results
    * @param int $limit1
    * @param int $limit2
    * @return array
    */
    function getAdmin($limit1,$limit2) {
        $fields = array('id', 'count_total', 'description_short', 'left_', 'right_');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT SQL_CALC_FOUND_ROWS ".implode(',',$fields)." FROM ".$this->table." WHERE level=1 ORDER BY left_ ASC LIMIT ?,?",array($limit1,$limit2));
    }

    /**
    * Get matching category results
    * @param string $where
    * @return array
    */
    function getMatching($where) {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'count_total');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE ".($this->PMDR->getConfig('cat_empty_hidden') ? 'count_total > 0 AND' : '')." ".$where);
    }

    /**
    * Get category by title
    * @param string $title
    * @return array
    */
    function getByTitle($title) {
        $fields = array('id', 'friendly_url', 'friendly_url_path', 'count_total');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        return $this->db->GetRow("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE count_total > 0 AND title=?",array($title));
    }

    /**
    * Get summary categories
    * @return array
    */
    function getFeatured() {
        $fields = array('id', 'friendly_url_path', 'count_total', 'link', 'hidden','no_follow');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        if(!$categories = $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE featured=1 ORDER BY left_")) {
            $categories =  $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE level=1 ORDER BY count_total DESC, title ASC LIMIT 15");
        }
        return $categories;
    }

    function initializeSort() {
        $create_table = $this->db->GetRow("SHOW CREATE TABLE ".$this->table);
        // Rename current table to a tmp table
        $this->db->Execute("DROP TABLE IF EXISTS ".$this->table."_tmp");
        $this->db->Execute("RENAME TABLE ".$this->table." TO ".$this->table."_tmp");
        // Create new categories table that we will populate with sorted values
        $this->db->Execute($create_table['Create Table']);
    }

    function processSort($start, $limit) {
        $count = (int) $start;
        $records = $this->db->GetAll("SELECT * FROM ".$this->table."_tmp ORDER BY level, title LIMIT ?,?",array((int) $start,(int) $limit));
        foreach($records as $record) {
            if($record['level'] == '0') {
                $this->db->Execute("INSERT INTO ".$this->table." SET id=?, title=?, level=?, left_=?, right_=?, parent_id=?, impressions=?",array(1,'ROOT',0,0,1,NULL,0));
            } else {
                // Get the parent of the current record so we know where to insert it
                $record['placement_id'] = $record['parent_id'];
                $record['placement'] = 'subcategory';
                // Insert the category
                $this->insert($record);
            }
            $count++;
        }
        return floor(($count*100) / $this->db->GetOne("SELECT COUNT(*) FROM ".$this->table."_tmp"));
    }

    function finalizeSort() {
        if($this->db->GetOne("SELECT COUNT(*) FROM ".$this->table) == $this->db->GetOne("SELECT COUNT(*) FROM ".$this->table."_tmp"))  {
            // Delete original table
            $this->db->Execute("DROP TABLE ".$this->table."_tmp");
            return true;
        } else {
            // If counts are not equal we delete the new table, and restore the old table
            $this->db->Execute("DROP TABLE ".$this->table);
            $this->db->Execute("RENAME TABLE ".$this->table."_tmp  TO ".$this->table);
            return false;
        }
    }

    function quickSearch($value) {
        $records = $this->db->GetAll("SELECT id, title FROM ".$this->table." WHERE title LIKE ".$this->db->Clean($value."%")." ORDER BY title LIMIT 20");
        $data = '';
        if(count($records)) {
            foreach($records AS $record) {
                $record_path = $this->getPath($record['id']);
                foreach($record_path AS $key=>$path) {
                    if($key != 0) {
                        $data .= ' > ';
                    }
                    $data .= '<a href="'.BASE_URL_ADMIN.'/'.$this->file_name.'?action=edit&id='.$path['id'].'">';
                    $data .= $path['title'];
                    $data .= '</a>';

                }
                $data .= '<br />';
            }
        } else {
            $data = 'No Results';
        }
        return $data;
    }

    function exportInitialize() {
        @unlink($this->getExportFileName());
        $category_labels = $this->getLevelLabels();
        $csv_output = "\"".implode('","',$category_labels)."\"\r\n";
        $handle = fopen($this->getExportFileName(), 'w');
        fwrite($handle, $csv_output);
        fclose($handle);
    }

    function exportProcess($start,$limit) {
        $records = $this->db->GetAll("SELECT * FROM ".$this->table." WHERE id!=1 ORDER BY left_ ASC LIMIT ?,?",array((int) $start,(int) $limit));
        if(!$records) {
            return 100;
        }
        $handle = fopen($this->getExportFileName(), 'a');
        $count = (int) $start;
        $csv_output = '';
        foreach($records AS $record) {
            $category_path = $this->getPath($record['id']);
            $output = array();
            foreach($category_path AS $category) {
                $output[] = $category['title'];
            }
            $csv_output .= '"'.implode('","',$output).'"'."\r\n";
            $count++;
        }
        fwrite($handle, $csv_output);
        fclose($handle);
        usleep(10000);
        return floor(($count*100) / $this->getCount());
    }

    function getExportFileName() {
        return TEMP_UPLOAD_PATH.$this->type.'_export.csv';
    }

    function exportStatistics($id_header, $title_header, $impressions_header, $impressions_search_header) {
        $file = TEMP_UPLOAD_PATH.$this->type.'_impressions.csv';
        if(!$fp = fopen($file, 'w')) {
            return false;
        }
        $header = array($id_header,$title_header,$impressions_header,$impressions_search_header);
        fputcsv($fp, $header);
        $limit = 0;
        while($impressions = $this->db->GetAll("SELECT id, title, impressions, impressions_search FROM ".$this->table." WHERE id!=1 AND (impressions > 0 OR impressions_search > 0) ORDER BY impressions DESC LIMIT $limit,1000")) {
            foreach($impressions AS $impression) {
                fputcsv($fp, $impression,',','"');
            }
            $limit += 1000;
        }
        fclose($fp);
        return $file;
    }

    function getImpressions() {
        return $this->db->GetAll("SELECT id, title, impressions FROM ".$this->table." WHERE id!=1 AND impressions > 0 ORDER BY impressions DESC LIMIT 10");
    }

    function getSearchImpressions() {
        return $this->db->GetAll("SELECT id, title, impressions_search FROM ".$this->table." WHERE id!=1 AND impressions_search > 0 ORDER BY impressions_search DESC LIMIT 10");
    }

    function getStatisticsCounts() {
        return $this->db->GetAll("SELECT id, title, count FROM ".$this->table." WHERE id!=1 AND count > 0 ORDER BY count DESC LIMIT 10");
    }

    function replaceVariable($variable,$replace,$hackstack) {
        return preg_replace('/(\[([^\]]*))?\*'.$variable.'\*(([^\]]*)\])?/','${2}'.$replace.'${4}',$hackstack);
    }

    function addPrimaryCategoryField(&$form, $label, $fieldset) {
        $field_data = array(
            'label'=>$label,
            'fieldset'=>$fieldset,
            'value'=>''
        );
        if($this->PMDR->getConfig('category_select_type') == 'tree_select') {
            $field_data['options'] = $this->getSelect();
            $type = 'tree_select';
        } else {
            if($this->PMDR->getConfig('category_select_type') == 'tree_select_cascading' OR $this->PMDR->getConfig('category_select_type') == 'tree_select_cascading_multiple') {
                $type = 'tree_select_cascading';
            } else {
                $type = 'tree_select_expanding_radio';
            }
            $field_data['options'] = array('type'=>$this->type.'_tree','bypass_setup'=>true,'search'=>true);
        }
        $form->addField('primary_category_id',$type,$field_data);
    }

    function addCategoriesField(&$form, $label, $fieldset, $category_limit) {
        $field_data = array(
            'label'=>$label,
            'fieldset'=>$fieldset,
            'limit'=>$category_limit
        );
        if($this->PMDR->getConfig('category_select_type') == 'tree_select' OR $this->PMDR->getConfig('category_select_type') == 'tree_select_multiple') {
            $field_data['options'] = $this->getSelect();
            if($category_limit == 2) {
                $field_data['first_option'] = '';
                $type = 'tree_select';
            } else {
                $type = 'tree_select_multiple';
            }
        } elseif($this->PMDR->getConfig('category_select_type') == 'tree_select_cascading' OR $this->PMDR->getConfig('category_select_type') == 'tree_select_cascading_multiple') {
            $field_data['options'] = array('type'=>$this->type,'limit'=>$category_limit,'search'=>true);
            if($category_limit == 2) {
                $type = 'tree_select_cascading';
            } else {
                $type = 'tree_select_cascading_multiple';
            }
        } else {
            $field_data['options'] = array('type'=>$this->type.'_tree','search'=>true);
            if($category_limit == 2) {
                $type = 'tree_select_expanding_radio';
            } else {
                $type = 'tree_select_expanding_checkbox';

            }
        }
        $form->addField('categories',$type,$field_data);
    }

    function getFieldIDs($category_id) {
        return $this->db->GetCol("SELECT field_id FROM ".$this->table_fields." WHERE category_id=? GROUP BY field_id",array($category_id));
    }
}
?>