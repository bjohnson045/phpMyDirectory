<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_email_queue($j) {
    global $PMDR, $db;

    // Max emails to send per hour
    $email_count = $PMDR->getConfig('email_queue_rate');

    // Get the elapsed time.  Used if CRON runs more than once per hour.
    $elapsed_time = time() - strtotime($j['last_run_date']);

    // If less than an hour has passed calculate number of emails to send based on elapsed time
    if($elapsed_time < 3600) {
        // Get the number of seconds per email based on the max count
        $seconds_per_email = 3600 / $email_count;
        // Get the number of emails to send based on the elapsed time and seconds per email
        $email_count = round($elapsed_time / $seconds_per_email);
    }

    // Process part of the mail queue
    $sent_number = $PMDR->get('Email_Queue')->processQueue($email_count);
    if($sent_number) {
        $cron_data['queue_sent'] = $sent_number;
    }
    $cron_data['status'] = true;
    return $cron_data;
}
$cron['cron_email_queue'] = array('day'=>-1,'hour'=>-1,'minute'=>5,'run_order'=>3);
?>