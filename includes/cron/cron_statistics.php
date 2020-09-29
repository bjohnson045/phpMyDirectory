<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

if(!function_exists('cron_statistics')) {
    function cron_statistics($j) {
        global $PMDR, $db;

        // Conslidate statistics
        $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT MAX(DATE(date)), type, type_id, COUNT(*) FROM ".T_STATISTICS_RAW." WHERE date > '".$j['last_run_date']."' AND date <= '".$j['current_run_date']."' GROUP BY type, type_id");

        // Delete old statistics
        $db->Execute("DELETE FROM ".T_STATISTICS_RAW." WHERE date < DATE_SUB(NOW(),INTERVAL 30 DAY)");

        // Calculate weekly listing impressions
        $db->Execute("UPDATE ".T_LISTINGS." l SET l.impressions_weekly=IFNULL((SELECT COALESCE(SUM(count),0) FROM ".T_STATISTICS." s WHERE s.type_id=l.id AND s.type='listing_impression' AND s.date > DATE_SUB(NOW(),INTERVAL 7 DAY) GROUP BY s.type_id),0)");

        // Purge statistics older than retention setting
        if(intval($PMDR->getConfig('statistics_purge_months')) > 0) {
            $db->Execute("DELETE FROM ".T_STATISTICS." WHERE date < DATE_SUB(NOW(),INTERVAL ? MONTH)",intval($PMDR->getConfig('statistics_purge_months')));
        }

        return array('status'=>true);
    }
    $cron['cron_statistics'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>1);
}
?>
