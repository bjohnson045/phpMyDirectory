<?php
class Listings_Popular_Block extends Template_Block {
    function content($limit = null) {
        if(is_null($limit)) {
            $limit = intval($this->PMDR->getConfig('block_listings_popular_number'));
        }
        if($limit) {
            $block_listings_popular_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_listings_popular.tpl');
            $block_listings_popular_template->cache_id = 'block_listings_popular'.$limit;
            $block_listings_popular_template->expire = 900;
            if(!$block_listings_popular_template->isCached()) {
                $block_listings_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/block_listings.tpl');
                if(!($block_listings_template->isCached())) {
                    $results = $this->db->GetAll("SELECT
                                                id,
                                                description_short,
                                                short_description_size,
                                                title,
                                                friendly_url,
                                                impressions,
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
                                                address_allow
                                           FROM ".T_LISTINGS."
                                           WHERE status = 'active'
                                           ORDER by impressions_weekly DESC LIMIT 0, ?",array(intval($limit)));

                    if(is_array($results) AND sizeof($results) > 0) {
                        foreach($results as $key=>$value) {
                            if($value['short_description_size']) {
                                $results[$key]['description_short'] =  Strings::limit_words($value['description_short'], $this->PMDR->getConfig('block_description_size'));
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
                            $results[$key]['details'] = sprintf('('.$this->PMDR->getLanguage('block_impressions').')',$value['impressions'],$this->PMDR->get('Dates_Local')->formatDateTime($value['date']));
                            if($value['logo_allow'] AND file_exists(LOGO_THUMB_PATH.$value['id'].'.'.$value['logo_extension'])) {
                                $results[$key]['logo_thumb_url'] = get_file_url_cdn(LOGO_THUMB_PATH.$value['id'].'.'.$value['logo_extension']);
                            } elseif($value['www_screenshot_allow'] AND file_exists(SCREENSHOTS_PATH.$value['id'].'.jpg')) {
                                $results[$key]['logo_thumb_url'] = get_file_url_cdn(SCREENSHOTS_PATH.$value['id'].'.jpg');
                            }
                        }
                    }
                }
                $block_listings_template->set('listings',$results);
                $block_listings_popular_template->set('listings',$results);
                $block_listings_popular_template->set('content',$block_listings_template);
            }
            return $block_listings_popular_template;
        }
    }
}
?>