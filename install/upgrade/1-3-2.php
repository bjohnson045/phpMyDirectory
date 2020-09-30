<?php
function postsync_1_3_2() {
    global $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'google_apikey', grouptitle = 'other' WHERE varname = 'googlemaps_apikey'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'yahoo_apikey', grouptitle = 'other' WHERE varname = 'yahoomaps_apikey'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname = 'bing_apikey', grouptitle = 'other' WHERE varname = 'virtualearth_apikey'");
}
?>