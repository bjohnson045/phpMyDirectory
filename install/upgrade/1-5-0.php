<?php
function presync_1_5_0() {
    global $db;
}

function postsync_1_5_0() {
    global $PMDR, $db;
    $db->Execute("UPDATE ".T_LISTINGS." SET priority_calculated=priority");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='priority_calculated' WHERE varname IN('listing_search_order_1','listing_search_order_2','listing_browse_order_1','listing_browse_order_2') AND value='priority'");
}
?>