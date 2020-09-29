<?php
function presync_1_3_4() {
    global $db;
    $db->RenameColumn(T_USERS,'salt','cookie_salt',true);
    $db->RenameColumn(T_CATEGORIES,'title_override','meta_title',true);
    $db->RenameColumn(T_LOCATIONS,'title_override','meta_title',true);
}

function postsync_1_3_4() {
    global $db;
    $db->Execute("UPDATE ".T_CATEGORIES." SET friendly_url_path_hash=MD5(friendly_url_path)");
    $db->Execute("UPDATE ".T_LOCATIONS." SET friendly_url_path_hash=MD5(friendly_url_path)");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='google' WHERE varname='geocoding_service'");

    if(file_exists(PMDROOT.'/includes/cron/class_mail_queue.php')) {
        unlink(PMDROOT.'/includes/cron/class_mail_queue.php');
    }

    $db->Execute("UPDATE ".T_USERS." SET password_hash='md5'");
}
?>