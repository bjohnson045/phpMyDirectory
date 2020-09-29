<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_geocoding($j) {
    global $PMDR, $db;

    $limit = 20;
    $listings = $db->GetAll("SELECT id, location_id, listing_address1, listing_address2, listing_zip, location_text_1, location_text_2, location_text_3 FROM ".T_LISTINGS." WHERE latitude = '0.0000000000' AND address_allow=1 AND zip_allow=1 ORDER BY coordinates_date_checked ASC LIMIT $limit");
    $cron_data['data']['coordinates_calculated'] = 0;
    $cron_data['data']['coordinates_failed'] = array();
    $map = $PMDR->get('Map');
    foreach($listings as $data) {
        $locations = $PMDR->get('Locations')->getPath($data['location_id']);
        foreach($locations as $loc_key=>$loc_value) {
            $data['location_'.($loc_key+1)] = $loc_value['title'];
            if($loc_value['disable_geocoding']) {
                $data['disable_geocoding'] = true;
            }
        }
        if(!$data['disable_geocoding']) {
            $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $data[$PMDR->getConfig('map_country')];
            $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $data[$PMDR->getConfig('map_state')];
            $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $data[$PMDR->getConfig('map_city')];
            if($coordinates = $map->getGeocode($data['listing_address1'], $map_city, $map_state, $map_country, $data['listing_zip'])) {
                if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                    $db->Execute("UPDATE ".T_LISTINGS." SET latitude=?, longitude=? WHERE id=?",array($coordinates['lat'],$coordinates['lon'],$data['id']));
                    $cron_data['data']['coordinates_calculated']++;
                }
            }
        }

        if(!$coordinates OR abs($coordinates['lat']) == 0) {
            $db->Execute("UPDATE ".T_LISTINGS." SET coordinates_date_checked=NOW() WHERE id=?",array($data['id']));
            $cron_data['data']['coordinates_failed'][] = $data['id'];
        }
    }
    unset($map);
    unset($listings);
    unset($data);

    $cron_data['status'] = true;

    return $cron_data;
}
$cron['cron_geocoding'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>7);
?>