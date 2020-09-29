<?php
function postsync_1_1_9() {
    global $PMDR, $db;
    if(!in_array('status_new',$db->MetaColumnNames(T_INVOICES))) {
        $db->Execute("ALTER TABLE ".T_INVOICES." ADD `status_new` ENUM('paid','canceled','unpaid') NOT NULL DEFAULT 'unpaid' AFTER `status`");
        $db->Execute("UPDATE ".T_INVOICES." SET status_new=status WHERE status IN('paid','canceled','unpaid')");
        $db->Execute("UPDATE ".T_INVOICES." SET status_new='unpaid' WHERE status NOT IN('paid','canceled','unpaid')");

        $db->DropColumn(T_INVOICES,'status');
        $db->RenameColumn(T_INVOICES,'status_new','status',true);
    }

    $db->Execute("UPDATE ".T_SETTINGS." SET value='".$PMDR->getConfig('image_logo_width')."' WHERE varname='website_screenshot_size'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='website_screenshot_cron_amount' WHERE varname='website_screenshots_cron_amount'");
    $db->Execute("UPDATE ".T_SETTINGS." SET grouptitle='other' WHERE varname='backup_path'");

    if($PMDR->getConfig('website_screenshot_cache_days')) {
        $db->Execute("UPDATE ".T_LISTINGS." SET www_screenshot_last_updated = DATE_SUB(NOW(),INTERVAL ".$PMDR->getConfig('website_screenshot_cache_days')." DAY)");
    }
}
?>