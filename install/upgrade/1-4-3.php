<?php
function presync_1_4_3() {
    global $db;
    $db->Execute("UPDATE ".T_SETTINGS." SET varname='search_match_all' WHERE varname='search_boolean_match_all'");
}

function postsync_1_4_3() {
    global $db;
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET active=1 AND hidden=0");
    $db->Execute("UPDATE ".T_SETTINGS." SET optioncode_type='text' WHERE optioncode_type='' OR optioncode_type='input'");

    $social_fields = array(
        'facebook_page_id',
        'twitter_id',
        'google_page_id',
        'linkedin_id',
        'linkedin_company_id',
        'pinterest_id',
        'youtube_id',
        'foursquare_id',
        'instagram_id',
    );

    foreach($social_fields AS $field) {
        if(isset($_SESSION[$field]) AND is_numeric($_SESSION[$field])) {
            $db->Execute("UPDATE ".T_LISTINGS." SET ".$field." = SUBSTRING_INDEX(custom_".$_SESSION[$field].",'/',-1) WHERE custom_".$_SESSION[$field]." LIKE 'http://%' OR custom_".$_SESSION[$field]." LIKE 'https://%' AND ".$field."=''");
            $db->Execute("UPDATE ".T_LISTINGS." SET ".$field." = custom_".$_SESSION[$field]." WHERE ".$field."=''");
        }
    }

    $languages = $db->GetCol("SELECT languageid FROM ".T_LANGUAGES." WHERE languageid!=1");
    foreach($languages AS $languageid) {
        $db->AddColumn(T_CATEGORIES, 'title_'.$languageid, 'varchar(255)', true, null);
        $db->AddColumn(T_MENU_LINKS, 'title_'.$languageid, 'varchar(255)', true, null);
    }

    $db->Execute("UPDATE ".T_REVIEWS." SET user_id=NULL WHERE user_id=0");
}
?>