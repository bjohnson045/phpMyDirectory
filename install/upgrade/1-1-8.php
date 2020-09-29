<?php
function postsync_1_1_8() {
    global $db;
    $db->Execute("UPDATE ".T_ORDERS." SET renewable=1");
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET renewable=1");
}
?>