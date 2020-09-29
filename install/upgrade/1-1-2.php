<?php
function presync_1_1_2() {
    global $db;
    $db->DropColumn(T_MEMBERSHIPS,'name');
}
function postsync_1_1_2() {
    global $db;
    if(isset($_SESSION['field_phone']) AND is_numeric($_SESSION['field_phone'])) {
        $db->Execute("UPDATE ".T_LISTINGS." SET phone = custom_".$_SESSION['field_phone']);
    }

    $duplicate_ratings = $db->GetAll("SELECT listing_id, user_id, COUNT(*) AS count FROM ".T_RATINGS." GROUP BY user_id, listing_id HAVING count > 1");
    foreach($duplicate_ratings as $rating) {
        $db->Execute("DELETE FROM ".T_RATINGS." WHERE listing_id=? AND user_id=? LIMIT ".($rating['count']-1),array($rating['listing_id'],$rating['user_id']));
    }

    $checkbox_fields = $db->GetAll("SELECT f.id, f.type, f.selected, fg.type AS group_type FROM ".T_FIELDS." f INNER JOIN ".T_FIELDS_GROUPS." fg ON f.group_id=fg.id WHERE f.type='checkbox'");
    foreach($checkbox_fields as $field) {
        if($field['group_type'] == 'listings') {
            $db->Execute("ALTER TABLE ".T_LISTINGS." CHANGE `custom_".$field['id']."` `custom_".$field['id']."` TEXT NOT NULL");
        }
        if($field['group_type'] == 'users') {
            $db->Execute("ALTER TABLE ".T_USERS." CHANGE `custom_".$field['id']."` `custom_".$field['id']."` TEXT NOT NULL");
        }
        if($field['group_type'] == 'reviews') {
            $db->Execute("ALTER TABLE ".T_REVIEWS." CHANGE `custom_".$field['id']."` `custom_".$field['id']."` TEXT NOT NULL");
        }
    }
}
?>