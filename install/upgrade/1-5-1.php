<?php
function presync_1_5_1() {
    global $db;
    if($db->ColumnExists(T_USERS_PERMISSIONS,'permission_key')) {
        $db->RenameColumn(T_USERS_PERMISSIONS,'id','id_old');
        // Remove auto increment from id_old or else the new key will cause a SQL error.
        $db->Execute("ALTER TABLE ".T_USERS_PERMISSIONS." CHANGE `id_old` `id_old` INT(10) UNSIGNED NOT NULL");
        $db->RenameColumn(T_USERS_PERMISSIONS,'permission_key','id');
    }
}

function postsync_1_5_1() {
    global $PMDR, $db;
    $db->Execute("DELETE FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE permission_id=''");
    if($db->ColumnExists(T_USERS_PERMISSIONS,'id_old')) {
        $old_permissions = $db->GetAssoc("SELECT id_old, id FROM ".T_USERS_PERMISSIONS." up");
        foreach($old_permissions AS $old_id=>$id) {
            $db->Execute("UPDATE ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." ugpl SET ugpl.permission_id=? WHERE ugpl.permission_id=?",array($id,$old_id));
        }
    }
    $db->Execute("DELETE FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE permission_id=''");
    $db->Execute("UPDATE ".T_USERS_GROUPS." SET administrator=1, advertiser=1, user=1 WHERE id IN (1,2,3)");
    $db->Execute("UPDATE ".T_USERS_GROUPS." SET advertiser=1, user=1 WHERE id IN (4,5)");
    $db->Execute("INSERT INTO ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." (group_id,permission_id) VALUES (1,'administrator'),(2,'administrator'),(3,'administrator'),(4,'advertiser'),(4,'user'),(5,'advertiser'),(5,'user')");
    $db->DropColumn(T_USERS_PERMISSIONS,'id_old');
}
?>