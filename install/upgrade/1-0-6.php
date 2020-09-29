<?php
function presync_1_0_6() {
    global $db;
}

function postsync_1_0_6() {
    global $db;
    $db->Execute("UPDATE ".T_LISTINGS." l SET primary_category_id=(SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." lc WHERE l.id=lc.list_id LIMIT 1) WHERE primary_category_id=0");

    $handle = opendir(LOGO_PATH);
    while (false != ($file = readdir($handle))) {
        $matches = array();
        if(preg_match('/^(\d+)\.([a-zA-Z]{3,4})$/',$file,$matches)) {
            $db->Execute("UPDATE ".T_LISTINGS." SET logo_extension=? WHERE id=?",array($matches[2],$matches[1]));
        }
    }
}
?>