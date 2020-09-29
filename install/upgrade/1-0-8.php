<?php
function presync_1_0_8() {
    global $db;
    $db->DropColumn(T_UPDATES,'data');
    $db->DropColumn(T_UPDATES,'file');
    $db->DropColumn(T_UPDATES,'file_type');
}
?>