<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_notifications($j) {
    global $PMDR, $db;

    $updated_listings = $db->GetAll("SELECT id, title, friendly_url, date_update FROM ".T_LISTINGS." WHERE status='active' AND date_update > '".$j['last_run_date']."' AND date_update <= '".$j['current_run_date']."'");
    foreach($updated_listings AS $listing) {
        $listing_data = array();
        $listing_data['listing_url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
        $listing_data['listing_date_update'] = $PMDR->get('Dates_Local')->formatDate($listing['date_update']);
        $listing_data['listing_title'] = $listing['title'];
        $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued,data) SELECT f.user_id, 'user', f.listing_id, 'user_listing_update', NOW(), ? FROM ".T_FAVORITES." f INNER JOIN ".T_USERS." u ON u.id=f.user_id WHERE f.listing_id=? AND u.favorites_notify=1",array(serialize(array('variables'=>$listing_data)),$listing['id']));
    }
    return array('status'=>true);
}
$cron['cron_notifications'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>6);
?>