<?php
class Listings_Featured_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_listings_featured_number'));
        }
        if($limit)  {
            $block_listings_featured_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_listings_featured.tpl');
            $block_listings_featured_template->cache_id = 'block_listings_featured'.$limit;
            $category_sql = '';
            $location_sql = '';
            if(($this->PMDR->getConfig('block_listings_featured_filter') == 'category' OR $this->PMDR->getConfig('block_listings_featured_filter') == 'category_location') AND $category = $this->PMDR->get('active_category')) {
                $category_sql = ' AND primary_category_id='.$category['id'];
                $block_listings_featured_template->cache_id .= '_category_'.$category['id'];
            }
            if(($this->PMDR->getConfig('block_listings_featured_filter') == 'location' OR $this->PMDR->getConfig('block_listings_featured_filter') == 'category_location') AND $location = $this->PMDR->get('active_location')) {
                $location_sql = ' AND location_id='.$location['id'];
                $block_listings_featured_template->cache_id .= '_location_'.$location['id'];
            }
            $block_listings_featured_template->expire = 900;
            if(!$block_listings_featured_template->isCached()) {
                $block_listings_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_listings.tpl');
                if(!($block_listings_template->isCached())) {

                    $results = $this->db->GetAll("SELECT
                                                id,
                                                description_short,
                                                short_description_size,
                                                title,
                                                friendly_url,
                                                date,
                                                listing_address1,
                                                listing_address2,
                                                listing_zip,
                                                location_id,
                                                location_text_1,
                                                location_text_2,
                                                location_text_3,
                                                phone,
                                                logo_extension,
                                                logo_allow,
                                                address_allow,
                                                www_screenshot_allow
                                            FROM ".T_LISTINGS."
                                            WHERE
                                                status = 'active'
                                                AND featured = 1
                                                $category_sql
                                                $location_sql
                                            ORDER BY featured_date ASC
                                            LIMIT 0, ?",array(intval($limit)));

                    if(is_array($results) AND sizeof($results) > 0) {
                        $update = array();
                        foreach($results as $key=>$value) {
                            $update[] = $value['id'];
                            if($value['short_description_size']) {
                                $results[$key]['description_short'] = Strings::limit_words($value['description_short'], $this->PMDR->getConfig('block_description_size'));
                            }

                            $locations = $this->PMDR->get('Locations')->getPath($value['location_id']);
                            foreach($locations as $loc_key=>$loc_value) {
                                $results[$key]['location_'.($loc_key+1)] = $loc_value['title'];
                            }
                            if($value['address_allow']) {
                                $map_country = $this->PMDR->getConfig('map_country_static') != '' ? $this->PMDR->getConfig('map_country_static') : $results[$key][$this->PMDR->getConfig('map_country')];
                                $map_state = $this->PMDR->getConfig('map_state_static') != '' ? $this->PMDR->getConfig('map_state_static') :  $results[$key][$this->PMDR->getConfig('map_state')];
                                $map_city = $this->PMDR->getConfig('map_city_static') != '' ? $this->PMDR->getConfig('map_city_static') : $results[$key][$this->PMDR->getConfig('map_city')];
                                $results[$key]['address'] = $this->PMDR->get('Locations')->formatAddress($value['listing_address1'],$value['listing_address2'],$map_city,$map_state,$map_country,$value['listing_zip']);
                            }
                            $results[$key]['link'] = $this->PMDR->get('Listings')->getURL($value['id'],$value['friendly_url']);
                            $results[$key]['details'] = '';
                            if($value['logo_allow'] AND file_exists(LOGO_THUMB_PATH.$value['id'].'.'.$value['logo_extension'])) {
                                $results[$key]['logo_thumb_url'] = get_file_url_cdn(LOGO_THUMB_PATH.$value['id'].'.'.$value['logo_extension']);
                            } elseif($value['www_screenshot_allow'] AND file_exists(SCREENSHOTS_PATH.$value['id'].'.jpg')) {
                                $results[$key]['logo_thumb_url'] = get_file_url_cdn(SCREENSHOTS_PATH.$value['id'].'.jpg');
                            }
                        }
                        $this->db->Execute("UPDATE LOW_PRIORITY ".T_LISTINGS." SET featured_date=NOW() WHERE id IN(".implode(',',$update).")");
                    }
                }
                $block_listings_template->set('listings',$results);
                $block_listings_featured_template->set('listings',$results);
                $block_listings_featured_template->set('content',$block_listings_template);
            }
            return $block_listings_featured_template;
        }
    }
}
?>