<?php
function presync_1_3_3() {
    global $db;
    $db->Execute("TRUNCATE ".DB_TABLE_PREFIX."email_queue");
    $db->DropColumn(DB_TABLE_PREFIX."email_queue",array('batch_id','listing_id','status','date_sent','subject','body_plain','body_html'));
    $db->RenameColumn(T_LANGUAGES,'currencysymbol','currency_prefix',true);
}

function postsync_1_3_3() {
    global $PMDR, $db;
    $db->DropTable(DB_TABLE_PREFIX."email_queue_batches");

    $db->Execute("UPDATE ".T_SETTINGS." SET value='google' WHERE varname='geocoding_service'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='google' WHERE value='googlemaps' AND varname='map_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='yahoo' WHERE value='yahoomaps' AND varname='map_type'");
    $db->Execute("UPDATE ".T_SETTINGS." SET value='bing' WHERE value='virtualearth' AND varname='map_type'");

    $db->Execute("UPDATE ".T_SETTINGS." SET value=ROUND(value/1024) WHERE value!=0 AND varname IN ('image_logo_size','classified_image_size','documents_size','gallery_image_size','profile_image_size')");
    $db->Execute("UPDATE ".T_BANNER_TYPES." SET filesize=ROUND(filesize/1024) WHERE filesize!=0");

    $db->Execute("UPDATE ".T_FIELDS." SET editable=1");

    $db->Execute("UPDATE ".T_LANGUAGES." SET decimalplaces=2 WHERE languageid=1");

    if($db->ColumnExists(T_EMAIL_TEMPLATES,'fromname')) {
        $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET fromname='' WHERE fromname=?",array($PMDR->getConfig('email_from_name')));
    }
    if($db->ColumnExists(T_EMAIL_TEMPLATES,'fromaddress')) {
        $db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET fromaddress='' WHERE fromaddress=?",array($PMDR->getConfig('email_from_address')));
    }

    $api_settings = $db->GetAll("SELECT value FROM ".T_SETTINGS." WHERE varname IN('googlemaps_api','yahoomaps_api','mapquest_api','virtualearth_api')");
    foreach($api_settings AS $setting) {
        if($setting['value'] == 1) {
            $db->Execute("UPDATE ".T_SETTINGS." SET value='dynamic' WHERE varname='map_display_type'");
            break;
        }
    }
}
?>