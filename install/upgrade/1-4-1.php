<?php
function presync_1_4_1() {
    global $db;
    $db->RenameColumn(T_CATEGORIES,'hits','impressions',true);
    $db->RenameColumn(T_LOCATIONS,'hits','impressions',true);
    $db->RenameColumn(T_LISTINGS,'hits','impressions',true);
    $db->RenameColumn(T_LISTINGS,'email_counter','emails',true);
    $db->RenameColumn(T_LISTINGS,'www_hits','website_clicks',true);
    $db->RenameColumn(T_LISTINGS,'banner_click','banner_clicks',true);
    $db->RenameColumn(T_LISTINGS,'banner_show','banner_impressions',true);
    $db->RenameColumn(T_CATEGORIES,'columns','display_columns',true);
    $db->RenameColumn(T_LOCATIONS,'columns','display_columns',true);
    $db->DropColumn(T_SETTINGS,'display_order');
    $db->DropColumn(T_LANGUAGE_PHRASES,array('used','date_update'));
}

function postsync_1_4_1() {
    global $db;
    // This is run post sync because the blog table may or may not exist depending on the version being upgraded from
    $db->RenameColumn(T_BLOG,'hits','impressions',true);

    $db->Execute("UPDATE ".T_IMPORTS." SET status='failed' WHERE status='incomplete'");
    $db->Execute("UPDATE ".T_LANGUAGES." SET locale=LOWER(REPLACE(locale,'-','_'))");
    $db->Execute("UPDATE ".T_PRODUCTS_PRICING." SET overdue_action='suspend'");
    $db->Execute("UPDATE ".T_LANGUAGES." SET active=1");

    // Carry over old statistics into a count with a null date
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_impression', id, impressions FROM ".T_LISTINGS." WHERE impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_website', id, website_clicks FROM ".T_LISTINGS." WHERE impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'banner_click', id, clicks FROM ".T_BANNERS." WHERE clicks > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'banner_impression', id, impressions FROM ".T_BANNERS." WHERE impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_email', id, emails FROM ".T_LISTINGS." WHERE emails > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_banner_impression', id, banner_impressions FROM ".T_LISTINGS." WHERE banner_impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_banner_click', id, banner_clicks FROM ".T_LISTINGS." WHERE banner_clicks > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'blog_impression', id, impressions FROM ".T_BLOG." WHERE impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'blog_impression', id, impressions FROM ".T_LOCATIONS." WHERE impressions > 0");
    $db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'blog_impression', id, impressions FROM ".T_CATEGORIES." WHERE impressions > 0");

    $captcha_type = $db->GetOne("SELECT value FROM ".T_SETTINGS." WHERE varname='captcha_type'");
    if($captcha_type == 'recaptcha') {
        $captcha_settings = $db->GetAssoc("SELECT varname, value FROM ".T_SETTINGS." WHERE varname='recaptcha_public_key' OR varname='recaptcha_private_key'");
        if(!$db->GetRow("SELECT * FROM ".T_CAPTCHAS." WHERE id='reCaptcha'")) {
            if(!empty($captcha_settings['recaptcha_public_key']) AND !empty($captcha_settings['recaptcha_private_key'])) {
                $settings = array();
                $settings['public_key'] = $captcha_settings['recaptcha_public_key'];
                $settings['private_key'] = $captcha_settings['recaptcha_private_key'];
                $db->Execute("INSERT INTO ".T_CAPTCHAS." (id,settings) VALUES ('reCaptcha',?)",array(serialize($settings)));
                $db->Execute("UPDATE ".T_SETTINGS." SET value='reCaptcha' WHERE varname='captcha_type'");
            }
        }
    } else {
        $db->Execute("UPDATE ".T_SETTINGS." SET value='Image' WHERE varname='captcha_type' AND value='image'");
    }

    $db->Execute("UPDATE ".T_MENU_LINKS." SET page_id=NULL WHERE page_id=0");
    $db->Execute("UPDATE ".T_MENU_LINKS." SET parent_id=NULL WHERE parent_id=0");

    $db->Execute("UPDATE ".T_SETTINGS." SET value='impressions' WHERE value='hits' AND varname IN('listing_search_order_1','listing_search_order_2','listing_browse_order_1','listing_browse_order_2')");
}
?>