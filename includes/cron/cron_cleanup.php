<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

if(isset($integrity_check)) {
    return 'sdfskjfslkjf*454k5jkdjfskds';
}

if(!function_exists('cron_cleanup')) {
    function cron_cleanup($j) {
        global $PMDR, $db;

        // Remove failed logins older then 15 days
        $db->Execute("DELETE FROM ".T_USERS_LOGIN_FAILS." WHERE date < DATE_SUB(NOW(),INTERVAL 15 DAY)");

        // Clear cron log past 15 days
        $db->Execute("DELETE FROM ".T_CRON_LOG." WHERE date < DATE_SUB(NOW(),INTERVAL 15 DAY)");

        // Clear main log if it goes over 10,000 entries
        $log_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_LOG);
        if($log_count > 10000) {
            $db->Execute("DELETE FROM ".T_LOG." ORDER BY id ASC LIMIT ".($log_count-10000));
        }

        // Clear the SQL log if we don't have debugging on -- not really necesarry, perhaps allow logging without debug being on
        if(!DEBUG_MODE AND @!$_SESSION['debug_mode']) {
            $db->Execute("TRUNCATE ".T_LOG_SQL);
        }

        // Remove IP limit logs past 7 days
        $PMDR->get('IP_Limits')->deleteOld(7);

        // Remove payment gateway logs past 60 days
        $db->Execute("DELETE FROM ".T_GATEWAYS_LOG." WHERE date < DATE_SUB(NOW(), INTERVAL 60 DAY)");

        // Remove old search log entries past 60 days
        $db->Execute("DELETE FROM ".T_SEARCH_LOG." WHERE date < DATE_SUB(NOW(), INTERVAL 60 DAY)");

        // Remove old email log entries
        if($PMDR->getConfig('email_log_expiration_days') > 0) {
            $db->Execute("DELETE FROM ".T_EMAIL_LOG." WHERE date < DATE_SUB(NOW(), INTERVAL ".$PMDR->getConfig('email_log_expiration_days')." DAY)");
        }

        // Remove old error log entries past 3 days
        $db->Execute("DELETE FROM ".T_ERROR_LOG." WHERE date < DATE_SUB(NOW(),INTERVAL 3 DAY)");

        // Delete expired redirects older than 90 days
        $db->Execute("DELETE FROM ".T_REDIRECTS." WHERE date_redirected < DATE_SUB(NOW(),INTERVAL 90 DAY)");

        // Sync email marketing
        if($PMDR->get('Email_Marketing')) {
            $PMDR->get('Email_Marketing')->processQueue();
        }

        // Do a "soft" check on the license.  Failure will get logged remotely for fraud detection.
        $PMDR->getNew('PMDLicense')->validateSilent();

        return array('status'=>true);
    }
    $cron['cron_cleanup'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>1);
}
?>