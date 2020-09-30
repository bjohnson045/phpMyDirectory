<?php
function presync_1_2_1() {
    global $db;
    $db->DropColumn(T_SEARCH_LOG,'sort');
    $db->Execute("TRUNCATE TABLE ".T_TEMPLATES_DATA);
    $db->Execute("TRUNCATE TABLE ".T_TEMPLATES);
}

function postsync_1_2_1() {
    global $db;
    if(in_array('facebook_id',$db->MetaColumnNames(T_USERS))) {
        if($db->ColumnExists(T_USERS,'login_id')) {
            $db->Execute("INSERT IGNORE INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider) SELECT id, CONCAT('http://www.facebook.com/profile.php?id=',facebook_id), 'Facebook' FROM ".T_USERS." WHERE facebook_id != 0");
        }
    }
    $db->DropColumn(T_USERS,'facebook_id');

    @unlink(PMDROOT.'/error_log');
    @unlink(PMDROOT.'/includes/error_log');
    @unlink(PMDROOT.MEMBERS_FOLDER.'error_log');
    @unlink(PMDROOT_ADMIN.'/error_log');
    @unlink_directory(PMDROOT.'/modules/login/FacebookConnect/');

    $db->Execute("DELETE ra.* FROM ".T_RATINGS." ra, ".T_REVIEWS." r WHERE ra.id!=r.rating_id");
}
?>