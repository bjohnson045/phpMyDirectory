<?php
function presync_1_1_5() {
    global $db;
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."words");
    $db->Execute("DROP TABLE IF EXISTS ".DB_TABLE_PREFIX."words_lookup");
    $db->DropColumn(T_GATEWAYS,array('id','identifier'));
    $db->RenameColumn(T_GATEWAYS,'gateway_name','id',true);
}
?>