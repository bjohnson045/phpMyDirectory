<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_email_schedules($j) {
    global $PMDR, $db;
    $PMDR->get('Email_Schedules')->queueAll();

    // Send out statistics email monthly and edit email content to match new field names
    if(date('j') === 1) {
        $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT user_id, 'listing', id, 'listings_statistics', NOW() FROM ".T_LISTINGS);
    }

    $cron_data['status'] = true;
    return $cron_data;
}
$cron['cron_email_schedules'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>3);
?>