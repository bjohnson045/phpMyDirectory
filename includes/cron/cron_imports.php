<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_imports($j) {
    global $PMDR, $db;

    $PMDR->loadLanguage('email_templates');

    $PMDR->get('Imports')->runScheduled();

    return array('status'=>true);
}
// Add the CRON job to the queue and set it to run based on the backup CRON days setting
$cron['cron_imports'] = array('day'=>-1,'hour'=>-1,'minute'=>5,'run_order'=>20);
?>