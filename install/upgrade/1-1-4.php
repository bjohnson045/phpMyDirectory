<?php
function presync_1_1_4() {
    global $db;

    $db->DropColumn(T_CRON,array('name','active','filename'));
}

function postsync_1_1_4() {
    global $db;
    if($db->TableExists(T_IP_LIMIT)) {
        $db->Execute("TRUNCATE ".T_IP_LIMIT);
    }
    $db->Execute("TRUNCATE ".T_CRON);
    $db->Execute("TRUNCATE ".T_CRON_LOG);

    $db->Execute("DELETE FROM ".T_MEMBERSHIPS." WHERE id NOT IN(SELECT type_id FROM ".T_PRODUCTS." WHERE type='listing_membership')");
    $db->Execute("DELETE FROM ".T_PRODUCTS." WHERE type='listing_membership' AND type_id NOT IN(SELECT id FROM ".T_MEMBERSHIPS.")");
}
?>