<?php
function presync_1_4_0() {
    global $db;
    $db->RenameColumn(T_USERS,'timezone_offset','timezone',true);
    $db->DropTable(DB_TABLE_PREFIX."listings_website_clicks");
}

function postsync_1_4_0() {
    global $db;
    $db->Execute("UPDATE ".T_USERS." SET timezone=''");
    $db->Execute("UPDATE ".T_CATEGORIES." SET parent_id=NULL WHERE id=1");
    $db->Execute("UPDATE ".T_LOCATIONS." SET parent_id=NULL WHERE id=1");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='tree_select_expanding_radio' WHERE value='dhtmlx_tree_radio' AND varname='category_select_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='tree_select_expanding_checkbox' WHERE value='dhtmlx_tree_checkbox' AND varname='category_select_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='tree_select_expanding_radio' WHERE value='dhtmlx_tree_radio' AND varname='location_select_type'");

    $db->Execute("DELETE bc.* FROM ".T_BANNERS_CATEGORIES." bc LEFT JOIN ".T_CATEGORIES." c ON bc.category_id=c.id WHERE c.id IS NULL");
    $db->Execute("DELETE bl.* FROM ".T_BANNERS_LOCATIONS." bl LEFT JOIN ".T_LOCATIONS." l ON bl.location_id=l.id WHERE l.id IS NULL");

    $categories = $db->GetAll("SELECT id, left_, right_, level FROM ".T_CATEGORIES." WHERE id!=1 ORDER BY left_");
    foreach($categories AS $category) {
        $parent_id = $db->GetOne("SELECT id FROM ".T_CATEGORIES." WHERE left_ < ".$category['left_']." AND right_ > ".$category['right_']." AND level=".($category['level']-1)." LIMIT 1");
        if($parent_id) {
            $db->Execute("UPDATE ".T_CATEGORIES." SET parent_id=? WHERE id=?",array($parent_id,$category['id']));
        } else {
            $db->Execute("UPDATE ".T_CATEGORIES." SET parent_id=NULL WHERE id=?",array($category['id']));
        }
    }
    unset($categories,$category,$parent_id);

    $locations = $db->GetAll("SELECT id, left_, right_, level FROM ".T_LOCATIONS." WHERE id!=1 ORDER BY left_");
    foreach($locations AS $location) {
        $parent_id = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE left_ < ".$location['left_']." AND right_ > ".$location['right_']." AND level=".($location['level']-1)." LIMIT 1");
        if($parent_id) {
            $db->Execute("UPDATE ".T_LOCATIONS." SET parent_id=? WHERE id=?",array($parent_id,$location['id']));
        } else {
            $db->Execute("UPDATE ".T_CATEGORIES." SET parent_id=NULL WHERE id=?",array($location['id']));
        }
    }
    unset($locations,$location,$parent_id);
}
?>