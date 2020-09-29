<?php
function postsync_1_1_1() {
    global $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='affiliate_program_code' WHERE varname='affiliate_program_url'");
}
?>