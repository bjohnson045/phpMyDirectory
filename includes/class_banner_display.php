<?php
/**
* Banner Display
* Displays a banner based on type
*/
class Banner_Display {
    /**
    * Registry
    * @var object
    */
    var $PMDR;
    /**
    * Category ID
    * @var int
    */
    var $category = 0;
    /**
    * Location ID
    * @var int
    */
    var $location = 0;
    /**
    * Database
    * @var object
    */
    var $db;
    /**
    * Previously displayed banners
    * @var array
    */
    var $displayed = array();

    /**
    * Get the registry object, load the database, and get location/category data
    * @param object $PMDR
    * @return Banner_Display
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->loadCategoryData();
        $this->loadLocationData();
    }

    /**
    * Load the category ID
    */
    function loadCategoryData() {
        if($category = $this->PMDR->get('active_category')) {
            $this->category = $category['id'];
            unset($category);
        }
    }

    /**
    * Load the location ID
    */
    function loadLocationData() {
        if($location = $this->PMDR->get('active_location')) {
            $this->location = $location['id'];
            unset($location);
        }
    }

    /**
    * Get a banner from the database
    * @param int $type Banner type ID
    */
    function getRecord($type) {
        $exclude_sql = '';
        // If we have previously displayed banners we want to exclude them from this query
        // so we do not display the same banner twice
        if(is_array($this->displayed) AND sizeof($this->displayed) > 0) {
            $exclude_sql = ' AND b.id NOT IN(';
            foreach($this->displayed as $value) {
                $exclude_sql .= $value.',';
            }
            $exclude_sql = rtrim($exclude_sql,',').')';
        }
        // If we have a category or location ID loaded we need to query the banner by this data
        if($this->category > 1 OR $this->location > 1) {
            // Check for both category and location
            if($this->category > 1 AND $this->location > 1) {
                if($banner_ids = $this->db->GetCol("SELECT bc.banner_id FROM ".T_BANNERS_CATEGORIES." bc INNER JOIN ".T_BANNERS_LOCATIONS." bl ON bc.banner_id=bl.banner_id WHERE bc.category_id=? AND bl.location_id=?",array($this->category,$this->location))) {
                    $banner_where = "AND b.id IN(".implode(',',$banner_ids).")";
                }
            // Check for category
            } elseif($this->category > 1) {
                $banner_join = "INNER JOIN ".T_BANNERS_CATEGORIES." bc ON bc.banner_id=b.id";
                $banner_where = "AND bc.category_id=".$this->PMDR->get('Cleaner')->clean_db($this->category);
            // Check for location
            } else {
                $banner_join = "INNER JOIN ".T_BANNERS_LOCATIONS." bl ON bl.banner_id=b.id";
                $banner_where = "AND bl.location_id=".$this->PMDR->get('Cleaner')->clean_db($this->location);
            }

            if(isset($banner_where)) {
                // We can't do AND l.banner_limit_".$type." != 0 here because banner limit fields do not get added to the listing table
                $banner = $this->db->GetRow("
                SELECT b.id, b.listing_id, b.extension, b.type_id, b.url, b.target, b.alt_text, b.code
                FROM ".T_BANNERS." b $banner_join
                WHERE b.status = 'active'
                    $banner_where
                    AND b.type_id=?
                    $exclude_sql
                ORDER BY
                    date_last_displayed ASC, RAND('".session_id()."')
                ",array($type));
            }

            // If no banner was found and we have the setting to get a random banner, get a random banner
            if(!$banner AND $this->PMDR->getConfig('banner_by_cat_random')) {
                $banner = $this->db->GetRow("SELECT b.id, b.listing_id, b.extension, b.type_id, b.url, b.target, b.alt_text, b.code FROM ".T_BANNERS." b WHERE b.type_id=? AND b.status='active' $exclude_sql ORDER BY date_last_displayed ASC, RAND('".session_id()."')",array($type));
            }
        // Get a banner if no location/category data is set
        } else {
            // Remote banners do not display unless all pages = 1
            $banner = $this->db->GetRow("SELECT b.id, b.listing_id, b.extension, b.type_id, b.url, b.target, b.alt_text, b.code FROM ".T_BANNERS." b WHERE b.type_id=? AND b.status='active' AND b.all_pages=1 $exclude_sql ORDER BY date_last_displayed ASC, RAND('".session_id()."')",array($type));
        }

        // If all methods to get a banner failed, return false
        if(!$banner) {
            return false;
        }

        // Update the banners impressions and last displayed date
        $this->PMDR->get('Statistics')->insert('banner_impression',$banner['id']);
        $this->PMDR->get('Statistics')->insert('banner_type_impression',$banner['type_id']);
        if(!is_null($banner['listing_id'])) {
            $this->PMDR->get('Statistics')->insert('listing_banner_impression',$banner['listing_id']);
        }
        $this->db->Execute("UPDATE LOW_PRIORITY ".T_BANNERS." SET date_last_displayed=NOW() WHERE id=?",array($banner['id']));

        // Add the banner to the displayed array so we don't display it again
        $this->displayed[] = $banner['id'];

        return($banner);
     }

     /**
     * Get the banner display code
     * @param int $type Banner type ID
     * @param boolean $remote If this is a remote banner or not
     * @return mixed
     */
     function getBanner($type, $remote = false) {
        // Abort if we can't find a banner from the database
        if(!$banner = $this->getRecord($type)) {
            return false;
        }

        // Get the banner type information from the database
        $banner_type = $this->db->GetRow("SELECT * FROM ".T_BANNER_TYPES." WHERE id=?",array($type));

        // Set the link defaults
        $url = null;
        $onclick = null;
        $target = 'new';

        // If the banner belongs to a listing we need to set the link according to the listing information
        if(!is_null($banner['listing_id']) AND ($listing = $this->db->GetRow("SELECT www, www_allow, friendly_url FROM ".T_LISTINGS." WHERE id=?",array($banner['listing_id'])))) {
            $banner = array_merge($banner,$listing);
            unset($listing);
            if($this->PMDR->getConfig('banner_link') == "WEBSITE" AND $banner['www'] != '' AND $banner['www_allow']) {
                if($this->PMDR->getConfig('js_click_counting') AND !$remote) {
                    $url = $banner['www'];
                    $onclick = '$.ajax({async: false, cache: false, timeout: 30000, data: ({action: \'banner_click\', id: '.$banner['id'].'}), error: function() { return true; }, success: function() { return true; }});';
                } else {
                    if(MOD_REWRITE AND !$remote) {
                        $url = BASE_URL.'/out-'.$banner['listing_id'].'-'.$banner['id'].'.html';
                    } else {
                        $url = BASE_URL.'/out.php?listing_id='.$banner['listing_id'].'&banner_id='.$banner['id'];

                    }
                }
            } else {
                if(!$remote) {
                    $url = $this->PMDR->get('Listings')->getURL($banner['listing_id'],$banner['friendly_url']);
                    $onclick = '$.ajax({async: false, cache: false, timeout: 30000, data: ({action: \'banner_click\', id: '.$banner['id'].'}), error: function() { return true; }, success: function() { return true; }});';
                    $target = null;
                } else {
                    $url = BASE_URL.'/out.php?listing_id='.$banner['listing_id'].'&banner_id='.$banner['id'];
                }

            }
        // If the banner has a URL set we need to set the link according to the URL
        } elseif($banner['url'] != '') {
            if(!$remote) {
                if($this->PMDR->getConfig('js_click_counting')) {
                    $onclick = '$.ajax({async: false, cache: false, timeout: 30000, data: ({action: \'banner_click\', id: '.$banner['id'].'}), error: function() { return true; }, success: function() { return true; }});';
                    $url = $banner['url'];
                } else {
                    $url = BASE_URL.'/out.php?banner_id='.$banner['id'];
                }
                $target = $banner['target'];
            } else {
                $url = BASE_URL.'/out.php?banner_id='.$banner['id'];
            }
        }

        if(!is_null($url)) {
            $link = '<a href="'.$url.'"';
            if(!is_null($target)) {
                $link .= ' target="'.$target.'"';
            }
            if(!is_null($onclick)) {
                $link .= ' onclick="'.$onclick.'"';
            }
            $link .= '>';
        }

        if($banner['extension'] != '' AND $banner_url = get_file_url_cdn(BANNERS_PATH.$banner['id'].'.'.$banner['extension'])) {
            if(strtolower($banner['extension']) ==  "swf") {
                $banner_content = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/banner_swf.tpl');
                $banner_content->set('file',get_file_url_cdn(BANNERS_PATH.$banner['id'].'.'.$banner['extension']));
                $banner_content->set('width',$banner_type['width']);
                $banner_content->set('height',$banner_type['height']);
                $img_code = $banner_content->render();
                unset($banner_content);
                $img_code .= "</object><br />";
                if($link != '') {
                    $img_code .= $link.$this->PMDR->getLanguage('visit_advertisment').'</a>';
                }
            } else {
                $banner_content = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/banner_image.tpl');
                $banner_content->set('width',$banner_type['width']);
                $banner_content->set('height',$banner_type['height']);
                $banner_content->set('id',$banner['id']);
                $banner_content->set('type_id',$banner_type['id']);
                // Random string attached to image to fix IE caching
                $banner_content->set('image_url',get_file_url_cdn(BANNERS_PATH.$banner['id'].'.'.$banner['extension']).'?random='.filemtime(BANNERS_PATH.$banner['id'].'.'.$banner['extension']));
                $banner_content->set('alt_text',($banner['alt_text'] != '' ? $banner['alt_text'] : $banner['title']));
                $img_code = $banner_content->render();
                unset($banner_content);
                if($link != '') {
                    $img_code = $link.$img_code.'</a>';
                }
            }
        } elseif($banner['code'] != '') {
            $img_code = $banner['code'];
            if($link != '') {
                $img_code = $link.$img_code.'</a>';
            }
        } else {
            return false;
        }
        return $img_code;
     }
}
?>