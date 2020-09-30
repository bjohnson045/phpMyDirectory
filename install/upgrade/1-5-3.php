<?php
function presync_1_5_2() {
    global $db;
}

function postsync_1_5_2() {
    global $PMDR, $db;
    
    $date_fields = $db->GetAll("SELECT TABLE_NAME, COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME RLIKE '".DB_TABLE_PREFIX.".*' AND (DATA_TYPE='datetime' OR DATA_TYPE='date') AND IS_NULLABLE='YES'",array(DB_NAME));
    foreach($date_fields AS $field) {
        $db->Execute("UPDATE ".$field['TABLE_NAME']." SET ".$field['COLUMN_NAME']."=NULL WHERE ".$field['COLUMN_NAME']."='0000-00-00' OR ".$field['COLUMN_NAME']."='0000-00-00 00:00:00'");
    }
}
?>