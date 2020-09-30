<?php
function presync_1_4_2() {
    global $db;
    $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET id='admin_contact_submission' WHERE id='contact_submission'");
    $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET id='admin_bot_detection' WHERE id='bot_detection'");
    if($html_tags = $db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='allowed_html_tags'")) {
        $html_tags = explode(',',$html_tags);
        if(count($html_tags)) {
            if($key = array_search('a',$html_tags)) {
                $html_tags[$key] = 'a[href]';
            }
            if($key = array_search('img',$html_tags)) {
                $html_tags[$key] = 'img[src]';
            }
            $html_tags[] = '*[style]';
            $db->Execute("UPDATE ".T_SETTINGS." SET value=? WHERE varname='allowed_html_tags'",array(implode(',',$html_tags)));
        }
    }
    $classifieds = $db->GetAll("SELECT id, price FROM ".T_CLASSIFIEDS." WHERE price != '' AND price != '0.00'");
    foreach($classifieds AS $classified) {
        $classified['price'] = preg_replace('/[^\d\.,]+/','',$classified['price']);

        if(($decimal_position = strpos($classified['price'],'.')) AND ($comma_position = strpos($classified['price'],','))) {
            if($comma_position < $decimal_position) {
                $classified['price'] = str_replace(',','',$classified['price']);
            } else {
                $classified['price'] = str_replace(array('.',','),array('','.'),$classified['price']);
            }
        } else {
            $comma_count = explode(',',$classified['price']);
            if($comma_count > 1) {
                $classified['price'] = str_replace(',','',$classified['price']).'.00';
            } else {
                $classified['price'] = str_replace(',','.',$classified['price']);
            }
        }
        $db->Execute("UPDATE ".T_CLASSIFIEDS." SET price=? WHERE id=?",array($price,$classified['id']));
    }
}

function postsync_1_4_2() {
    global $db;
    if($db->ColumnExists(T_USERS,'login_id')) {
        $db->Execute("INSERT IGNORE INTO ".T_USERS_LOGIN_PROVIDERS." (user_id,login_id,login_provider) SELECT id, login_id, login_provider FROM ".T_USERS." WHERE login_id !=''");
    }
    $db->DropColumn(T_USERS,'login_id');
    $db->DropColumn(T_USERS,'login_provider');
    $db->Execute("UPDATE ".T_STATISTICS_RAW." SET type='listing_website' WHERE type='website_click'");
    $db->Execute("UPDATE ".T_STATISTICS." SET type='listing_website' WHERE type='website_click'");
    $db->Execute("UPDATE ".T_LANGUAGES." SET active=1 WHERE languageid=1");
    $db->Execute("UPDATE ".T_CATEGORIES." SET parent_id=NULL WHERE id=1");
    $db->Execute("UPDATE ".T_LOCATIONS." SET parent_id=NULL WHERE id=1");

    $db->Execute("UPDATE ".T_LOCATIONS." a, (SELECT IF(@previous_parent_id = parent_id, @row := @row +1, @row :=1) AS ROW,
    @previous_parent_id := parent_id, parent_id, id
    FROM ".T_LOCATIONS." JOIN (SELECT @row :=0, @previous_parent_id :=0) i ORDER BY parent_id) aa
    SET a.child_row_id = aa.row WHERE a.parent_id = aa.parent_id AND a.id = aa.id");

    $db->Execute("UPDATE ".T_CATEGORIES." a, (SELECT IF(@previous_parent_id = parent_id, @row := @row +1, @row :=1) AS ROW,
    @previous_parent_id := parent_id, parent_id, id
    FROM ".T_CATEGORIES." JOIN (SELECT @row :=0, @previous_parent_id :=0) i ORDER BY parent_id) aa
    SET a.child_row_id = aa.row WHERE a.parent_id = aa.parent_id AND a.id = aa.id");
}
?>