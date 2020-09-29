<?php
/**
* Listings Class
*/
class Listings extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;

    /**
    * Listings constructor
    * @param object $PMDR
    * @return Listings
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_LISTINGS;
    }

    /**
    * Get listing details
    * @param int $id
    * @return array
    */
    function getRow($id) {
        if((MOD_REWRITE AND $this->PMDR->getConfig('mod_rewrite_listings_id')) OR is_numeric($id)) {
            if(preg_match('/-([0-9]+)$/',$id,$matches)) {
                $id = $matches[1];
            }
            return $this->db->GetRow("SELECT * FROM ".T_LISTINGS."  WHERE id=?",array($id));
        } else {
            return $this->db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE friendly_url=?",array($id));
        }
    }

    /**
    * Get listing details joined with its user
    * @param int $id
    * @return array
    */
    function getJoinedUser($id) {
        if(!is_numeric($id) AND MOD_REWRITE) {
            if($this->PMDR->getConfig('mod_rewrite_listings_id') AND preg_match('/-([0-9]+)$/',$id,$matches)) {
                $id = $matches[1];
            } else {
                return $this->db->GetRow("SELECT l.*, u.login FROM ".T_LISTINGS." l LEFT JOIN ".T_USERS." u ON l.user_id=u.id WHERE l.friendly_url=?",array($id));
            }
        }
        return $this->db->GetRow("SELECT l.*, u.login FROM ".T_LISTINGS." l LEFT JOIN ".T_USERS." u ON l.user_id=u.id WHERE l.id=?",array($id));
    }

    /**
    * Insert listing
    * @param array $data
    * @return int Listing ID
    */
    function insert($data) {
        $id = parent::insert($data);
        if(isset($data['logo'])) {
            if($extension = $this->updateLogo($data,$id)) {
                $this->update(array('logo_extension'=>$extension),$id);
            }
        }
        if(!empty($data['logo_background'])) {
            if($extension = $this->updateLogoBackground($data,$id)) {
                $data['logo_background'] = $extension;
                $this->update(array('logo_background'=>$extension),$id);
            }
        }
        $this->updateLinkedListings($id,$data['linked_listings']);
        return $id;
    }

    /**
    * Update listing
    * @param array $data
    * @param int $id
    */
    function update($data, $id) {
        $listing_data = $this->db->GetRow("SELECT short_description_size, description_size, priority FROM ".T_LISTINGS." WHERE id=?",array($id));

        if(isset($data['friendly_url'])) {
            $data['friendly_url'] = Strings::rewrite($data['friendly_url']);
        }
        if(isset($data['description_short'])) {
            $data['description_short'] = Strings::limit_characters($data['description_short'],$listing_data['short_description_size']);
        }
        if(isset($data['www']) AND !empty($data['www'])) {
            $data['www'] = standardize_url($data['www']);
        }

        $data['location_search_text'] = trim(preg_replace('/,+/',',',$data['listing_address1'].','.$data['listing_address2'].','.$this->PMDR->get('Locations')->getPathString($data['location_id']).','.$data['location_text_1'].','.$data['location_text_2'].','.$data['location_text_3'].','.$data['listing_zip'].','.$this->PMDR->getConfig('map_city_static').','.$this->PMDR->getConfig('map_state_static').','.$this->PMDR->getConfig('map_country_static')),',');

        $data['date_update'] = $this->PMDR->get('Dates')->dateTimeNow();
        $data['ip_update'] = get_ip_address();


        $data['priority_calculated'] = $listing_data['priority'];
        if(isset($data['priority_weight'])) {
            $data['priority_calculated'] += intval($data['priority_weight']);
        }

        $fields = $this->PMDR->get('Fields')->getFields('listings');

        if(is_array($fields) AND sizeof($fields) > 0) {
            foreach($fields as $value) {
                if(isset($data['custom_'.$value['id']]) AND is_array($data['custom_'.$value['id']])) {
                    $data['custom_'.$value['id']] = @implode("\n",$data['custom_'.$value['id']]);
                }
            }
        }

        if(!isset($data['categories'])) {
            $data['categories'] = array();
        }
        $this->updateCategories($id,$data['categories'],$data['primary_category_id']);

        if($data['delete_logo']) {
            @unlink(find_file(LOGO_PATH.$id.'.*'));
            @unlink(find_file(LOGO_THUMB_PATH.$id.'.*'));
        }

        if(!empty($data['logo'])) {
            if($extension = $this->updateLogo($data,$id)) {
                $data['logo_extension'] = $extension;
                unset($extension);
            }
        }

        if($data['logo_background_delete']) {
            @unlink(find_file(LOGO_BACKGROUND_PATH.$id.'.*'));
        }

        if(!empty($data['logo_background'])) {
            if($extension = $this->updateLogoBackground($data,$id)) {
                $data['logo_background'] = $extension;
                unset($extension);
            }
        } else {
            unset($data['logo_background']);
        }

        $this->updateLinkedListings($id,$data['linked_listings']);

        unset($data['categories']);
        unset($data['logo']);
        unset($data['linked_listings']);

        parent::update($data,$id);
    }

    /**
    * Updated linked listings
    * @param int $id Listing ID
    * @param array $links Array of other listing IDs
    */
    function updateLinkedListings($id, $links = array()) {
        $this->db->Execute("DELETE FROM ".T_LISTINGS_LINKED." WHERE listing_id=? OR listing_linked_id=?",array($id,$id));
        if(empty($links)) {
            return false;
        }
        $value_string = '';
        $value_string_reversed = '';
        foreach($links as $link) {
            $value_string .= '('.$id.','.$link.'),';
            $value_string_reversed .= '('.$link.','.$id.'),';
        }
        $this->db->Execute("INSERT INTO ".T_LISTINGS_LINKED." (listing_id,listing_linked_id) VALUES ".trim($value_string,','));
        $this->db->Execute("INSERT IGNORE INTO ".T_LISTINGS_LINKED." (listing_id,listing_linked_id) VALUES ".trim($value_string_reversed,','));
    }

    /**
    * Update listing categories
    * @param int $id
    * @param array $categories
    * @param int $primary_category
    */
    function updateCategories($id, $categories, $primary_category) {
        if($categories == '') $categories = array();

        if(!is_array($categories)) {
            $categories = array($categories);
        }
        if(!in_array($primary_category,$categories)) {
            $categories[] = $primary_category;
        }
        $categories = array_filter(array_unique($categories));
        if(!count($categories)) {
            return false;
        }
        foreach($categories as $category) {
            $value_string .= '('.$id.','.$category.'),';
        }
        $this->db->Execute("DELETE FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=?",array($id));
        $this->db->Execute("INSERT INTO ".T_LISTINGS_CATEGORIES." (list_id,cat_id) VALUES ".trim($value_string,','));

        $banners = $this->db->GetAll("SELECT id FROM ".T_BANNERS." WHERE listing_id=?",array($id));
        foreach($banners AS $banner) {
            $this->PMDR->get('Banners')->updateCategories($banner['id']);
        }
    }

    /**
    * Update listing with details from product
    * @param int $id
    */
    function updateMembership($id) {
        $membership_id = $this->db->GetOne("SELECT p.type_id FROM ".T_ORDERS." o, ".T_PRODUCTS_PRICING." pp, ".T_PRODUCTS." p WHERE o.pricing_id=pp.id AND pp.product_id=p.id AND o.type_id=? AND o.type='listing_membership'",array($id));
        $columns = $this->db->MetaColumnNames(T_MEMBERSHIPS);
        $query = "UPDATE ".T_LISTINGS." l, ".T_MEMBERSHIPS." m SET";
        foreach($columns as $column) {
            if($column == 'id' or $column == 'name' OR $column == 'categories') continue;
            $query .= ' l.'.$column.'=m.'.$column.',';
        }
        $query = rtrim($query,',')." WHERE m.id=? AND l.id=?";
        $this->db->Execute($query,array($membership_id,$id));
        $this->syncMembership($id);
    }

    /**
    * Sync the listing with its product
    * Deletes banners/listings/documents etc.
    * @param int $id
    */
    function syncMembership($id) {
        $membership = $this->db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($id));
        $banner_types = $this->db->GetCol("SHOW COLUMNS FROM ".T_LISTINGS." LIKE 'banner_limit_%'");
        foreach($banner_types as $field) {
            if(!$membership[$field]) {
                preg_match('/.+(\d+)/',$field,$matches);
                $type_id = $matches[1];
                $banners = $this->db->GetAll("SELECT id FROM ".T_BANNERS." WHERE listing_id=? AND type_id=?",array($id,$type_id));
                foreach($banners as $banner) {
                    $this->PMDR->get('Banners')->delete($banner['id']);
                }
            }
        }
        if(!$membership['images_limit']) {
            $images = $this->db->GetAll("SELECT * FROM ".T_IMAGES." WHERE listing_id=?",array($id));
            foreach($images as $image) {
                @unlink(find_file(IMAGES_PATH.$image['id'].'.*'));
                @unlink(find_file(IMAGES_PATH.$image['id'].'-small.*'));
            }
            $this->db->Execute("DELETE FROM ".T_IMAGES." WHERE listing_id=?",array($id));
        }
        if(!$membership['documents_limit']) {
            $documents = $this->db->GetAll("SELECT * FROM ".T_DOCUMENTS." WHERE listing_id=?",array($id));
            foreach($documents as $document) {
                @unlink(DOCUMENTS_PATH.$document['id'].'.*');
            }
            $this->db->Execute ("DELETE FROM ".T_DOCUMENTS." WHERE listing_id=?",array($id));
        }
        if(!$membership['classifieds_limit']) {
            $classifieds = $this->db->GetAll("SELECT * FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($id));
            foreach($classifieds as $classified) {
                @unlink(find_file(CLASSIFIEDS_PATH.$classified['id'].'.*'));
                @unlink(find_file(CLASSIFIEDS_PATH.$classified['id'].'-small.*'));
            }
            $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($id));
        }
    }

    /**
    * Delete a listing
    * @param int $id
    * @return boolean
    */
    function delete($id) {
        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE type='listing' AND type_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS." where id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_RATINGS." WHERE listing_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_FAVORITES." WHERE listing_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS_CLAIMS." WHERE listing_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS_LINKED." WHERE listing_id=? OR listing_linked_id=?",array($id,$id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_LISTINGS_SUGGESTIONS." WHERE listing_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_UPDATES." WHERE type_id=? AND type='listing'",array($id));

        // Statistics
        $this->db->Execute("DELETE FROM ".T_STATISTICS." WHERE type LIKE 'listing_%' AND type_id=?",array($id));
        $this->db->Execute("DELETE FROM ".T_STATISTICS_RAW." WHERE type LIKE 'listing_%' AND type_id=?",array($id));

        // Reviews
        $reviews = $this->db->GetAll("SELECT id FROM ".T_REVIEWS." WHERE listing_id=?",array($id));
        foreach($reviews as $review) {
            $this->db->Execute ("DELETE FROM ".T_REVIEWS_COMMENTS." WHERE review_id=?",array($review['id']));
            $this->db->Execute ("DELETE FROM ".T_REVIEWS_QUALITY." WHERE review_id=?",array($review['id']));
        }
        $this->db->Execute ("DELETE FROM ".T_REVIEWS." WHERE listing_id=?",array($id));

        // Classifieds
        $this->deleteClassifieds($id);

        // Image Gallery
        $images = $this->db->GetAll("SELECT id FROM ".T_IMAGES." WHERE listing_id=?",array($id));
        foreach($images as $image) {
            @unlink(find_file(IMAGES_PATH.$image['id'].'.*'));
            @unlink(find_file(IMAGES_THUMBNAILS_PATH.$image['id'].'.*'));
        }
        $this->db->Execute("DELETE FROM ".T_IMAGES." WHERE listing_id=?",array($id));

        // Events
        $events = $this->db->GetAll("SELECT id FROM ".T_EVENTS." WHERE listing_id=?",array($id));
        foreach($events as $event) {
            $this->PMDR->get('Events')->delete($event['id']);
        }

        // Documents
        $documents = $this->db->GetAll("SELECT * FROM ".T_DOCUMENTS." WHERE listing_id=?",array($id));
        foreach($documents as $document) {
            @unlink(DOCUMENTS_PATH.$document['id'].'.*');
        }
        $this->db->Execute ("DELETE FROM ".T_DOCUMENTS." WHERE listing_id=?",array($id));

        // Banners
        $banners = $this->db->GetAll("SELECT * FROM ".T_BANNERS." WHERE listing_id=?",array($id));
        foreach($banners as $banner) {
            $this->PMDR->get('Banners')->delete($banner['id']);
        }

        // Logos
        while(@unlink(find_file(LOGO_PATH.$id.'.*'))) {}
        while(@unlink(find_file(LOGO_BACKGROUND_PATH.$id.'.*'))) {}
        while(@unlink(find_file(LOGO_THUMB_PATH.$id.'.*'))) {}

        return true;
    }

    /**
    * Delete a listing's classifieds
    * @param int $id Listing ID
    */
    function deleteClassifieds($id, $primary_category_id = null) {
        $classifieds = $this->db->GetAll("SELECT id FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($id));
        foreach($classifieds as $classified) {
            $this->PMDR->get('Classifieds')->delete($classified['id'],false);
        }
        $this->db->Execute("DELETE FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($id));
    }

    /**
    * Get a listings categories
    * @param int $id
    * @return array
    */
    function getCategories($id,$paths = false) {
        $fields = array('id AS id_assoc', 'id', 'friendly_url', 'friendly_url_path', 'level AS depth', 'count', 'count_total', 'hidden', 'link');
        $fields[] = $this->PMDR->get('Languages')->getFieldName('title');
        $listing_categories = $this->db->GetAssoc("SELECT ".implode($fields,',')." FROM ".T_CATEGORIES.", ".T_LISTINGS_CATEGORIES." WHERE ".T_CATEGORIES.".id=".T_LISTINGS_CATEGORIES.".cat_id AND ".T_LISTINGS_CATEGORIES.".list_id=? ORDER BY left_",array($id));
        foreach($listing_categories as $key=>$category) {
            if($category['link'] != '') {
                $listing_categories[$key]['url'] = $category['link'];
            } else {
                $listing_categories[$key]['url'] = $this->PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
            }
            if($paths) {
                $listing_categories[$key]['path'] = $this->PMDR->get('Categories')->getPath($category['id']);
                $listing_categories[$key]['path_url'] = $this->PMDR->get('Categories')->getPathDisplay($listing_categories[$key]['path'],'&raquo;');
                foreach($listing_categories[$key]['path'] AS &$path) {
                    if($path['link'] != '') {
                        $path['url'] = $path['link'];
                    } else {
                        $path['url'] = $this->PMDR->get('Categories')->getURL($path['id'],$path['friendly_url_path']);
                    }
                }
                $listing_categories[$key]['path_url'] = $this->PMDR->get('Categories')->getPathDisplay($listing_categories[$key]['path'],'&raquo;');
            }
        }
        return $listing_categories;
    }

    /**
    * Update a listing logo
    * @param array $data
    * @param int $listing_id
    */
    function updateLogo($data, $listing_id) {
        $options = array(
            'width'=>$this->PMDR->getConfig('image_logo_width'),
            'height'=>$this->PMDR->getConfig('image_logo_height'),
            'enlarge'=>$this->PMDR->getConfig('image_logo_small'),
            'remove_existing'=>true
        );
        if($extension = $this->PMDR->get('Image_Handler')->process($data['logo'],LOGO_PATH.$listing_id.'.*',$options)) {
            $options = array(
                'width'=>$this->PMDR->getConfig('image_logo_thumb_width'),
                'height'=>$this->PMDR->getConfig('image_logo_thumb_height'),
                'enlarge'=>$this->PMDR->getConfig('image_logo_thumb_small'),
                'crop'=>$this->PMDR->getConfig('image_logo_thumb_crop'),
                'remove_existing'=>true
            );
            $this->PMDR->get('Image_Handler')->process($data['logo'],LOGO_THUMB_PATH.$listing_id.'.*',$options);
            return $extension;
        }
        return false;
    }

    /**
    * Update a listing logo background
    * @param array $data
    * @param int $listing_id
    */
    function updateLogoBackground($data, $listing_id) {
        $options = array(
            'width'=>$this->PMDR->getConfig('logo_background_width'),
            'height'=>$this->PMDR->getConfig('logo_background_height'),
            'enlarge'=>$this->PMDR->getConfig('logo_background_small'),
            'remove_existing'=>true
        );
        return $this->PMDR->get('Image_Handler')->process($data['logo_background'],LOGO_BACKGROUND_PATH.$listing_id.'.*',$options);
    }

    /**
    * Check if a listing should be considered "New"
    * @param string $datetime
    * @return boolean
    */
    function ifNew($datetime) {
        $date = strtotime($datetime);
        $days = round((time() - $date) / 86400);
        return $days <= 5;
    }

    /**
    * Check if a listing should be considered "Hot"
    * @param int $rating
    * @return boolean
    */
    function ifHot($rating) {
        return ($rating >= 4);
    }

    /**
    * Check if a listing should be considered "Updated"
    * @param string $unix_datetime
    * @return boolean
    */
    function ifUpdated($unix_datetime) {
        $date = strtotime($unix_datetime);
        $days = round((time() - $date) / 86400);
        return ($days <= 5 AND $date != 0);
    }

    /**
    * Update listing rating
    * @param int $id
    */
    function updateRating($id) {
        // Only count ratings that are not linked to a review that is pending.
        $rating = $this->db->GetRow("SELECT COUNT(r.id) AS count, SUM(r.rating) as rating FROM ".T_RATINGS." r LEFT JOIN ".T_REVIEWS." rv ON rv.rating_id = r.id WHERE r.listing_id=? AND (rv.status NOT LIKE 'pending' OR rv.status IS NULL) GROUP BY r.listing_id",array($id));
        if(!$rating) {
            $rating['rating'] = 0;
            $rating['count'] = 0;
        }
        if($rating['count'] != 0) {
            $rating['rating'] = round(($rating['rating'] / $rating['count']),2);
        }
        $this->db->Execute("UPDATE ".T_LISTINGS." SET rating=?, votes=? WHERE id=?",array($rating['rating'],$rating['count'],$id));
    }

    /**
    * Get listing URL
    * @param mixed $id
    * @param string $friendly_url
    * @param string $query_string
    * @param string $query_string_rewrite
    * @param string $filename
    */
    function getURL($id, $friendly_url, $query_string='', $query_string_rewrite='.html', $filename='listing.php') {
        if(MOD_REWRITE AND !empty($friendly_url)) {
            return BASE_URL.'/'.$friendly_url.($this->PMDR->getConfig('mod_rewrite_listings_id') ? '-'.$id : '').$query_string_rewrite;
        } else {
            return BASE_URL.'/'.$filename.'?id='.$id.$query_string;
        }
    }

    /**
    * Change a listing's user
    * @param int $listing_id
    * @param int $new_user_id
    */
    function changeUser($listing_id, $new_user_id) {
        $this->db->Execute("UPDATE ".T_LISTINGS." SET user_id=? WHERE id=?",array($new_user_id, $listing_id));
        $this->db->Execute("UPDATE ".T_EMAIL_QUEUE." SET user_id=? WHERE type='listing' AND type_id=?",array($new_user_id, $listing_id));
        $this->db->Execute("UPDATE ".T_INVOICES." SET user_id=? WHERE type='listing_membership' AND type_id=?",array($new_user_id, $listing_id));
        $this->db->Execute("UPDATE ".T_ORDERS." SET user_id=? WHERE type='listing_membership' AND type_id=?",array($new_user_id, $listing_id));
    }

    /**
    * Change a listing's status
    * @param int $id
    * @param string $status
    */
    function changeStatus($id,$status) {
        $this->db->Execute("UPDATE ".T_LISTINGS." SET status=? WHERE id=?",array($status,$id));
        $this->db->Execute("UPDATE ".T_BANNERS." SET status=? WHERE listing_id=?",array($status,$id));
    }

    /**
    * Load the listing and setup specific information related to the parent listing
    * @param int $id
    * @return array
    */
    function getListingChildPage($id) {
        $listing = $this->getRow($id);

        if($listing['location_id'] AND $listing['location_id'] > 1) {
            $this->PMDR->set('active_location',array('id'=>$listing['location_id'],'friendly_url_path'=>$this->db->GetOne("SELECT friendly_url_path FROM ".T_LOCATIONS." WHERE id=?",array($listing['location_id']))));
        }

        if($listing['primary_category_id']) {
            $this->PMDR->set('active_category',array('id'=>$listing['primary_category_id'],'friendly_url_path'=>$this->db->GetOne("SELECT friendly_url_path FROM ".T_CATEGORIES." WHERE id=?",array($listing['primary_category_id']))));
        }

        if(trim($listing['header_template_file']) != '') {
            $this->PMDR->set('header_file',$listing['header_template_file']);
        }
        if(trim($listing['footer_template_file']) != '') {
            $this->PMDR->set('footer_file',$listing['footer_template_file']);
        }
        if(trim($listing['wrapper_template_file']) != '') {
            $this->PMDR->set('wrapper_file',$listing['wrapper_template_file']);
        }

        return $listing;
    }

    /**
    * Insert a listing locations
    * @param array $data Location data
    * @param int $listing_id Listing ID to add location to
    * @return int $id Location ID
    */
    function insertLocation($data,$listing_id) {
        $formatted = $this->getFormattedAddress($data);
        $id = $this->db->Execute("INSERT INTO ".T_LISTINGS_LOCATIONS." (title,address1,address2,location_id,location_text_1,location_text_2,location_text_3,zip,phone,url,email,formatted,listing_id) VALUES
        (?,?,?,?,?,?,?,?,?,?,?,?,?)",array($data['title'],$data['address1'],$data['address2'],$data['location_id'],$data['location_text_1'],$data['location_text_2'],$data['location_text_3'],$data['zip'],$data['phone'],$data['url'],$data['email'],$formatted,$listing_id));
        return $id;
    }

    /**
    * Update listing location
    * @param array $data Location data
    * @param int $id ID of existing location to update
    */
    function updateLocation($data,$id) {
        $formatted = $this->getFormattedAddress($data);
        $this->db->Execute("UPDATE ".T_LISTINGS_LOCATIONS." SET title=?, address1=?, address2=?, location_text_1=?, location_text_2=?, location_text_3=?, zip=?, phone=?, url=?, email=?, formatted=?, location_id=? WHERE id=?",array($data['title'],$data['address1'],$data['address2'],$data['location_text_1'],$data['location_text_2'],$data['location_text_3'],$data['zip'],$data['phone'],$data['url'],$data['email'],$formatted,$data['location_id'],$id));
    }

    /**
    * Format a location address
    *
    * @param array $data Location Data
    * @return string Formatted location
    */
    function getFormattedAddress($data) {
        $location_path = $this->PMDR->get('Locations')->getPath($data['location_id']);
        foreach($location_path as $key=>$location) {
            $data['location_'.($key+1)] = $location['title'];
        }
        $country = coalesce($this->PMDR->getConfig('map_country_static'),$data[$this->PMDR->getConfig('map_country')]);
        $state = coalesce($this->PMDR->getConfig('map_state_static'),$data[$this->PMDR->getConfig('map_state')]);
        $city = coalesce($this->PMDR->getConfig('map_city_static'),$data[$this->PMDR->getConfig('map_city')]);

        $formatted = array();
        $formatted[] = $data['address1'];
        if(!empty($data['address2'])) {
            $formatted[] = $data['address2'];
        }
        $formatted[] = implode(', ',array_filter(array($city,$state,$data['zip'])));
        $formatted[] = $country;
        return implode("\n",$formatted);
    }

    /**
    * Get a single location
    * @param int $id ID of location to get
    * @return array Location data
    */
    function getLocation($id) {
        return $this->db->GetRow("SELECT * FROM ".T_LISTINGS_LOCATIONS." WHERE id=?",array($id));
    }

    /**
    * Get a listings extra locations
    *
    * @param int $id  Listing ID
    * @return array Locations
    */
    function getLocationsDisplay($id) {
        return $this->db->GetAll("SELECT title, formatted, url, phone, email FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($id));
    }

    /**
    * Delete a listing location
    * @param int $id Location ID to delete
    * @return void
    */
    function deleteLocation($id) {
        $this->db->Execute("DELETE FROM ".T_LISTINGS_LOCATIONS." WHERE id=?",array($id));
    }
}
?>