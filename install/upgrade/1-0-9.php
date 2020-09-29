<?php
function presync_1_0_9() {
    global $db;
    $db->DropColumn(T_UPDATES,'file_type');
}
function postsync_1_0_9() {
    global $db;
    $db->Execute("UPDATE ".T_MEMBERSHIPS." SET vcard_allow=1, addtofavorites_allow=1, pdf_allow=1");
    $db->Execute("UPDATE ".T_LISTINGS." SET vcard_allow=1, addtofavorites_allow=1, pdf_allow=1");

    $db->Execute("UPDATE ".T_SETTINGS." SET varname='googlemaps_apikey' WHERE varname = 'map_google_apikey'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='yahoomaps_api' WHERE varname = 'map_yahoo_api'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='mapquest_api' WHERE varname = 'map_mapquest_api'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='googlemaps_api' WHERE varname = 'map_google_api'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='mapquest_apikey' WHERE varname = 'map_mapquest_apikey'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='yahoomaps_apikey' WHERE varname = 'map_yahoo_apikey'");
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='virtualearth_apikey' WHERE varname = 'map_virtualearth_apikey'");

    $db->Execute("UPDATE ".T_SETTINGS." SET value='googlemaps' WHERE value='google' AND varname='map_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='yahoomaps' WHERE value='yahoo' AND varname='map_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='googlemaps' WHERE value='google' AND varname='geocoding_service'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='yahoomaps' WHERE value='yahoo' AND varname='geocoding_service'");
}
?>