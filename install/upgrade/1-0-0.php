<?php
function presync_1_0_0() {
    global $db;
    $db->RenameColumn(T_LOG_SQL,'sql','sql_query',true);
}
?>