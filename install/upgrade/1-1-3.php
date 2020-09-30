<?php
function postsync_1_1_3() {
    global $PMDR, $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='affiliate_program_code' WHERE varname='affiliate_program_url'");

    $custom_fields = $db->GetAll("SHOW COLUMNS FROM ".T_MEMBERSHIPS." LIKE 'custom_%'");
    foreach($custom_fields AS $field) {
        $parts = explode('_',$field['Field']);
        if(!$db->GetRow("SELECT id FROM ".T_FIELDS." WHERE id=?",array($parts[1]))) {
            $PMDR->get('Fields')->delete($parts[1]);
        }
    }
    $custom_fields = $db->GetAll("SHOW COLUMNS FROM ".T_USERS." LIKE 'custom_%'");
    foreach($custom_fields AS $field) {
        $parts = explode('_',$field['Field']);
        if(!$db->GetRow("SELECT id FROM ".T_FIELDS." WHERE id=?",array($parts[1]))) {
            $PMDR->get('Fields')->delete($parts[1]);
        }
    }
    $custom_fields = $db->GetAll("SHOW COLUMNS FROM ".T_REVIEWS." LIKE 'custom_%'");
    foreach($custom_fields AS $field) {
        $parts = explode('_',$field['Field']);
        if(!$db->GetRow("SELECT id FROM ".T_FIELDS." WHERE id=?",array($parts[1]))) {
            $PMDR->get('Fields')->delete($parts[1]);
        }
    }

    if(in_array('import_id',$db->MetaColumnNames(T_LISTINGS))) {
        $db->Execute("UPDATE ".T_LISTINGS." l, ".T_ORDERS." o SET o.import_id=l.import_id WHERE o.type_id=l.id AND o.type='listing_membership'");
        $db->DropColumn(T_LISTINGS,'import_id');
    }
}
?>