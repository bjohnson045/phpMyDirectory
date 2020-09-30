<?php
function presync_1_0_4() {
    global $db;
    $db->DropColumn(T_EMAIL_QUEUE,'copy_to');
}

function postsync_1_0_4() {
    global $db;
    if($db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='block_description_size'") == '5') {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='50' WHERE varname='block_description_size'");
    }
}
?>