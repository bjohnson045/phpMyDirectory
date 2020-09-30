<?php
/**
 * Locations Class
 */
class Locations extends Tree_Gateway {
    /**
    * Locations table
    * @var string
    */
    var $table = T_LOCATIONS;
    /**
    * Type or key to identify the data for the tree gateway
    * @var string
    */
    var $type = 'location';

    /**
    * Get a single location
    * @param int $id Location ID
    * @return array Listing data
    */
    public function get($id) {
        return $this->db->GetRow("SELECT * FROM ".T_LOCATIONS." WHERE id=?",array($id));
    }

    /**
    * Insert a location
    * @param array $data Location data
    * @return mixed False on failure, otherwise location ID
    */
    public function insert($data) {
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
            trigger_error('Invalid location placement ID '.$data['placement_id'].' while inserting a location.',E_USER_WARNING);
            return false;
        }

        $data = array_merge($data,$node_data);

        if(!$id = $this->PMDR->getNew('TableGateway',T_LOCATIONS)->insert($data)) {
            parent::delete($node_data);
        }

        $this->updateFriendlyPath($id);

        if(!empty($data['small_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['small_image'],LOCATION_IMAGE_PATH.$id.'-small.*');
        }
        if(!empty($data['large_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['large_image'],LOCATION_IMAGE_PATH.$id.'.*');
        }

        $this->PMDR->get('Cache')->deletePrefix('locations');

        return $id;
    }

    /**
    * Update location
    * @param array $data
    * @param int $id Location ID
    */
    function update($data, $id) {
        $location = $this->db->GetRow("SELECT id, left_, right_, closed FROM ".$this->table." WHERE id=?",array($id));
        $data['friendly_url'] = Strings::rewrite($data['friendly_url']);
        if(empty($data['display_columns'])) {
            $data['display_columns'] = null;
        }

        foreach($data AS $key=>$data_field) {
            if(strstr($key,'custom_') AND is_array($data_field)) {
                $data[$key] = implode("\n",$data_field);
            }
        }

        // If close is selected, close its sublocations as well.  Else check if it is already closed and if so, open its sublocations also.
        if($data['closed']) {
            $this->db->Execute("UPDATE ".$this->table." SET closed=1 WHERE left_ > ? AND right_ < ?",array($location['left_'],$location['right_']));
        } elseif($location['closed']) {
            $this->db->Execute("UPDATE ".$this->table." SET closed=0 WHERE left_ > ? AND right_ < ?",array($location['left_'],$location['right_']));
        }

        $this->PMDR->getNew('TableGateway',T_LOCATIONS)->update($data,$id);

        if($data['placement'] != '' AND $data['placement_id'] != '' AND !$this->isAChildOf($data['placement_id'],$id) AND $id != $data['placement_id']) {
            if($data['placement'] == 'before') {
                $this->moveToPreviousSibling($id,$data['placement_id']);
            } elseif($data['placement'] == 'after') {
                $this->moveToNextSibling($id,$data['placement_id']);
            }   elseif($data['placement'] == 'subcategory') {
                $this->moveToFirstChild($id,$data['placement_id']);
            }
        }

        $this->updateFriendlyPath($id);

        if($data['small_image_delete']) unlink(find_file(LOCATION_IMAGE_PATH.$id.'-small.*'));
        if($data['large_image_delete']) unlink(find_file(LOCATION_IMAGE_PATH.$id.'.*'));

        if(!empty($data['small_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['small_image'],LOCATION_IMAGE_PATH.$id.'-small.*');
        }
        if(!empty($data['large_image'])) {
            $this->PMDR->get('Image_Handler')->process($data['large_image'],LOCATION_IMAGE_PATH.$id.'.*');
        }
        $this->PMDR->get('Cache')->deletePrefix('locations');
    }

    /**
    * Delete location
    * @param int $id Location ID
    * @return void
    */
    function delete($id) {
        @unlink(find_file(LOCATION_IMAGE_PATH.$id.'.*'));
        @unlink(find_file(LOCATION_IMAGE_PATH.$id.'.-small*'));
        $this->db->Execute("DELETE FROM ".T_BANNERS_LOCATIONS." WHERE location_id=?",array($id));
        parent::delete($id);
    }

    /**
    * Get raw count from database
    * @param int $id Location ID
    * @return int
    */
    function getRawCount($id) {
        return $this->db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE location_id=?",array($id));
    }

    /**
    * Get true count
    * @param int $id Location ID
    * @return int
    */
    function getFullCount($id) {
        $location = $this->db->GetRow("SELECT left_, right_ FROM ".T_LOCATIONS." WHERE id=?",array($id));
        if($location) {
            return $this->db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." l INNER JOIN ".T_LOCATIONS." lc ON l.location_id=lc.id WHERE lc.left_ BETWEEN ".($location['left_'])." AND ".($location['right_']));
        }
        return false;
    }

    /**
    * Get location level labels from language variables
    * @return array Associative array containing language variables as keys and labels as values
    */
    function getLevelLabels($get_location_text = true) {
        $locations = $this->db->GetAssoc("SELECT CONCAT('location_',SUBSTR(variablename,-1)), content FROM ".T_LANGUAGE_PHRASES." WHERE (languageid=-1 OR languageid=".$this->PMDR->getConfig('language_admin').") AND variablename LIKE 'general_locations_levels_%' ORDER BY variablename ASC");
        if($get_location_text) {
            if($this->PMDR->getConfig('location_text_1')) {
                $locations['location_text_1'] = $this->PMDR->getLanguage('general_locations_text_1');
            }
            if($this->PMDR->getConfig('location_text_2')) {
                $locations['location_text_2'] = $this->PMDR->getLanguage('general_locations_text_2');
            }
            if($this->PMDR->getConfig('location_text_3')) {
                $locations['location_text_3'] = $this->PMDR->getLanguage('general_locations_text_3');
            }
        }
        return $locations;
    }

    /**
    * Update language variables
    * @return void
    */
    function updateLanguageVariables() {
        $current_level = $this->db->GetOne("SELECT COUNT(*) as count FROM ".T_LANGUAGE_PHRASES." WHERE languageid=-1 AND variablename LIKE 'general_locations_levels_%' AND languageid=-1");
        $max_level = $this->db->GetOne("SELECT MAX(level) as count FROM ".$this->table);

        if($max_level > $current_level) {
            for($x=$current_level; $x < $max_level; $x++) {
                $this->db->Execute("INSERT INTO ".T_LANGUAGE_PHRASES." SET languageid=-1, section='general_locations', variablename='general_locations_levels_".($x+1)."', content='Location Level ".($x+1)."'");
            }
        } elseif($max_level < $current_level) {
            for($x=$max_level; $x < $current_level; $x++) {
                $this->db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE variablename LIKE 'general_locations_levels_".($x+1)."'");
            }
        }
    }

    /**
    * Get Listings
    * @param int $location_id location ID
    * @param bool $only_count Only get number of listings
    * @param int $limit_start Limit starting number
    * @param int $limit_number Number to retreive
    * @param string $orderby ORDER BY query section
    */
    function getListings($location_id, $only_count=false, $limit_start=false, $limit_number=false, $sort_order='ordering ASC, title') {
        if($limit_start) $limit_sql = 'LIMIT '.$limit_start.', '.$limit_number.' ';
        if($sort_order) $sort_order = 'ORDER BY '.$sort_order;
        $sql = $only_count ? 'SELECT COUNT(*) AS count' : 'SELECT '.T_LISTINGS.'.*';

        $sql .= "
                FROM ".T_LISTINGS." WHERE ".T_LISTINGS.".location_id='$location_id'
                AND ".T_LISTINGS.".status = 'active'
                $sort_order $limit_sql";

        $result=$this->db->GetAll($sql);
        return $result;
    }

    /**
    * Parse for browsing
    * Parses location results for proper display
    * @param array $results
    * @return array Parsed results
    */
    function parseForBrowsing($results, $columns_number = null) {
        if(count($results)) {
            if(is_null($columns_number) OR $columns_number == 0) {
                $columns_number = $this->PMDR->getConfig('locations_num_columns');
            }
            // Initialize our first level count
            $first_level_count = 0;
            // Set the root level we are currently on
            $first_level_count_reference = $results[0]['level'];
            // Loop through all categories checking their level and if we are displaying it.  This is used to correctly build our table.  We do it here with
            // PHP because a DB query is most likely slower.
            if(is_array($results)) {
                for ($i=0;$i<sizeof($results);$i++) {
                    if($first_level_count_reference == $results[$i]['level'] AND ($results[$i]['count_total'] > 0 OR !$this->PMDR->getConfig('loc_empty_hidden')) AND !$results[$i]['hidden']) {
                        $first_level_count++;
                    }
                }
            }
            $records_per_column = floor($first_level_count/$columns_number);
            if($this->PMDR->getConfig('locations_vertical_sort')) {
                $extras_per_column = $first_level_count % $columns_number;
            }

            $j = 0;

            // Loop through our records
            $columns = array();
            if(is_array($results)) {
                for ($i=0;$i<sizeof($results);$i++) {
                    // We we want to hide empty and count is 0 or if its not a first level category or hidden, we skip it (subcategories come later)
                    if(($this->PMDR->getConfig('loc_empty_hidden') AND $results[$i]['count_total'] == 0) OR ($results[$i]['level'] != $first_level_count_reference) OR $results[$i]['hidden']) {
                        continue;
                    }

                    if($this->PMDR->getConfig('locations_vertical_sort')) {
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
                        $columns[$column_index][$i]['image'] = get_file_url_cdn(LOCATION_IMAGE_PATH.$results[$i]['id'].'-small.*');
                    }

                    $sub_counter = 0;
                    // If we have set to display more than 0 subcats
                    if ($this->PMDR->getConfig('loc_show_subs_number') > 0) {
                        // Loop through remaining categories starting from where we left off ($i)
                        if(is_array($results)) {
                            for($y=$i+1; $y<sizeof($results); $y++) {
                                // Its a higher level, so we know its a subcat, continue
                                if($results[$y]['level'] > $results[$i]['level']) {
                                    // If we want to hide empty counts and count = 0 or its hidden, we skip it
                                    if(($this->PMDR->getConfig('loc_empty_hidden') AND $results[$y]['count_total'] == 0) OR $results[$y]['hidden']) {
                                        continue;
                                    }
                                    if($sub_counter < $this->PMDR->getConfig('loc_show_subs_number')) {  // subtract one because we count before this comparison
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
    * Get location URL
    * @param int $id Location ID
    * @param string $location_path Category friendly URL path
    * @param int $category_id Category ID
    * @param string $category_path Category friendly URL path
    */
    function getURL($id=null, $location_path, $category_id = null, $category_path = null) {
        if(MOD_REWRITE) {
            $url_string = ltrim($this->PMDR->getConfig('location_mod_rewrite').'/','/').$location_path;
            if(!is_null($category_path)) {
                $url_string = ltrim($this->PMDR->getConfig('category_mod_rewrite').'/','/').$category_path.$url_string;
            }
            $url_string = BASE_URL_NOSSL.'/'.$url_string;
        } else {
            $url_string = BASE_URL_NOSSL.'/';
            if(!is_null($category_id)) {
                $url_string .= 'browse_categories.php?id='.$category_id.'&location='.$id;
            } else {
                $url_string .= 'browse_locations.php?id='.$id;
            }
        }
        return $url_string;
    }

    /**
    * Check location table and reset if needed
    * @return boolean True if reset was performed, false otherwise
    */
    function checkReset() {
        if(!$this->db->GetOne("SELECT COUNT(*) FROM ".T_LOCATIONS)) {
            $this->db->Execute("INSERT INTO ".T_LOCATIONS." (id,title,left_,right_,parent_id,level) VALUES (1,'ROOT',0,1,NULL,0)");
            $this->db->Execute("ALTER TABLE ".T_LOCATIONS." AUTO_INCREMENT=2");
            return true;
        }
        return false;
    }

    /**
    * Format an address
    * @param string $address_line1 Address Line 1
    * @param string $address_line2 Address Line 2
    * @param string $city City
    * @param string $state State
    * @param string $country Country
    * @param string $zip Zip code
    * @param string $newline New line character or spacing character
    * @return string Formatted address
    */
    function formatAddress($address_line1, $address_line2, $city, $state, $country, $zip, $newline = "\n") {
        $address = array();
        if(!empty($address_line1)) {
            $address[] = $address_line1;
        }
        if(!empty($address_line2)) {
            $address[] = $address_line2;
        }
        $address_string = '';
        $city = trim($city);
        if(!empty($city)) {
            $address_string .= $city;
        }
        $state = trim($state);
        if(!empty($state)) {
            $address_string .= ', '.$state;
        }
        $zip = trim($zip);
        if(!empty($zip)) {
            $address_string .= ' '.$zip;
        }
        $address[] = $address_string;
        $country = trim($country);
        if(!empty($country)) {
            $address[] = $country;
        }
        foreach($address AS $key=>$address_line) {
            $address[$key] = preg_replace('/[[:space:]]+/',' ',$address_line);
            $address[$key] = trim($address[$key],' ,');
        }
        return implode($newline,$address);
    }

    /**
    * Update the location listing counters
    * @return void
    */
    function updateCounters() {
        $this->db->Execute("UPDATE ".T_LOCATIONS." SET count=0, count_total=0");

        $this->db->Execute("UPDATE ".T_LOCATIONS." lc,
        (SELECT loc.id AS id, COUNT(l.id) AS count
        FROM ".T_LOCATIONS." AS loc, ".T_LISTINGS." AS l
        WHERE loc.id=l.location_id AND l.status='active' GROUP BY loc.id) ld
        SET lc.count=ld.count WHERE lc.id=ld.id");

        $this->db->Execute("UPDATE ".T_LOCATIONS." l,
        (SELECT parent.id AS id, SUM(node.count) as count
        FROM ".T_LOCATIONS." AS parent, ".T_LOCATIONS." AS node
        WHERE node.left_ BETWEEN parent.left_ AND parent.right_
        GROUP BY parent.id) ld
        SET l.count_total = ld.count WHERE l.id=ld.id");
    }

    /**
    * Get location results for the tree select field
    * @param string $where
    * @return array
    */
    function getTreeSelect($where) {
        $fields = array('id', 'title', 'left_', 'right_', 'parent_id', 'level');
        if(!empty($where)) {
            $where = 'WHERE '.$where;
        }
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." $where ORDER BY left_");
    }

    /**
    * Get locations from an ID list
    * @param string $ids
    * @return array
    */
    function getByIDList($ids) {
        $fields = array('id', 'title', 'friendly_url', 'friendly_url_path', 'level', 'count', 'count_total', 'hidden', 'link', 'no_follow', 'description_short', 'small_image_url');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".T_CATEGORIES." WHERE id IN(".$ids.")");
    }

    /**
    * Get location results for the cascading select field
    * @param int $parent_id
    * @param string $where
    * @return array
    */
    function getCascadingSelect($parent_id,$where='') {
        $fields = array('id', 'title', 'left_', 'right_', 'parent_id', 'level');
        return $this->db->GetAll("SELECT ".implode(',',$fields)." FROM ".$this->table." WHERE parent_id=? $where ORDER BY left_",array($parent_id));
    }
}
?>