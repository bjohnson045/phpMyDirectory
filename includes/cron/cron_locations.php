<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

if(!function_exists('cron_locations')) {
    function cron_cleanup($j) {
        global $PMDR, $db;

        $locations = $db->GetAll("SELECT id FROM ".T_LOCSTIONS." WHERE updated=1");
        foreach($locations AS $location) {


        }

        foreach($listings AS $listing) {
            $locations = $PMDR->get('Locations')->getPath($listing['location_id']);
            foreach($locations as $key=>$location) {
                $level = $key+1;
                $listing['location_'.$level] = $location['title'];
                $listing['location_'.$level.'_abbreviation'] = $location['abbreviation'];
                $listing['location_'.$level.'_url'] = $PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path']);
                if($location['disable_geocoding']) {
                    $listing['disable_geocoding'] = true;
                }
            }
            $data['country'] = $this->PMDR->getConfig('map_country_static') != '' ? $this->PMDR->getConfig('map_country_static') : $data[$this->PMDR->getConfig('map_country')];
            $data['state'] = $this->PMDR->getConfig('map_state_static') != '' ? $this->PMDR->getConfig('map_state_static') :  $data[$this->PMDR->getConfig('map_state')];
            $data['city'] = $this->PMDR->getConfig('map_city_static') != '' ? $this->PMDR->getConfig('map_city_static') : $data[$this->PMDR->getConfig('map_city')];

            $db->Execute("UPDATE )
        }

        return array('status'=>true);
    }
    $cron['cron_locations'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>1);
}
?>