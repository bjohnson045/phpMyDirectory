<?php
function postsync_1_0_3() {
    global $db;
    $fields = $db->GetAll("SELECT * FROM ".T_FIELDS." WHERE type IN('checkbox','select','select_multiple','radio')");
    foreach($fields as $field) {
        $db->Execute("UPDATE ".T_LISTINGS." SET custom_".$field['id']." = REPLACE(custom_".$field['id'].",'#','\n')");
    }

    if($db->ColumnExists(T_PRODUCTS_PRICING,'priority') AND $db->ColumnExists(T_PRODUCTS,'priority')) {
        $db->Execute("UPDATE ".T_PRODUCTS_PRICING." pp, ".T_PRODUCTS." p SET pp.priority=p.priority WHERE pp.product_id=p.id");
    }
    $db->DropColumn(T_PRODUCTS,'auto_activate');
    $db->DropColumn(T_PRODUCTS,'priority');

    $handle = opendir(LOGO_PATH);
    while (false != ($file = readdir($handle))) {
        if(preg_match('/^(\d+)\.([a-zA-Z]{3,4})$/',$file,$matches)) {
            $db->Execute("UPDATE ".T_LISTINGS." SET logo_extension=? WHERE id=?",array($matches[2],$matches[1]));
        }
    }
    closedir($handle);

    $db->Execute("UPDATE ".T_SETTINGS." SET varname='loc_empty_hidden' WHERE varname='loc_category_hidden'");
}
?>