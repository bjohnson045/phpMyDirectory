<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_link_checker($j) {
    global $PMDR, $db;
    $cron_data['data'] = $PMDR->get('LinkChecker')->checkLinks(intval($PMDR->getConfig('reciprocal_per_day')),intval($PMDR->getConfig('reciprocal_buffer')));
    $cron_data['status'] = true;
    return $cron_data;
}
$cron['cron_link_checker'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>2);
?>