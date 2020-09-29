<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_listing_counters($j) {
    global $PMDR, $db;

    $cron_data = array('status'=>false);

    if($PMDR->getConfig('show_indexes') OR $PMDR->getConfig('cat_empty_hidden')) {
        $PMDR->get('Categories')->updateCounters();
        $cron_data['data']['listing_counts'] = 1;
        $cron_data['status'] = true;
    }

    if($PMDR->getConfig('loc_show_indexes') OR $PMDR->getConfig('loc_empty_hidden')) {
        $PMDR->get('Locations')->updateCounters();
        $cron_data['data']['listing_counts'] = 1;
        $cron_data['status'] = true;
    }

    return $cron_data;
}
$cron['cron_listing_counters'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>4);
?>