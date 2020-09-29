<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 2 of 7</h3><p>';

echo 'Importing listings.... '; ob_flush();
// Listing Categories
$db->Execute("TRUNCATE ".T_LISTINGS_CATEGORIES);
$db->Execute("INSERT INTO ".T_LISTINGS_CATEGORIES." (list_id, cat_id) SELECT list_id, cat_id FROM ".OLD_T_LIST2CAT);

$custom_fields = $db->GetCol("SELECT CONCAT('custom_',id)  FROM ".T_FIELDS);
$banner_types = $db->GetCol("SELECT id FROM ".T_BANNER_TYPES);

// Listings
$listings_query = "
    INSERT INTO ".T_LISTINGS." (
        id,user_id,status,title,friendly_url,primary_category_id,description,description_short,
        location_id,listing_address1,listing_address2,listing_zip,location_text_1,www,ip,date,date_update,
        ip_update,impressions,website_clicks,emails,rating,banner_impressions,banner_clicks,counterip,mail,comment,votes";
    if(in_array('custom_2',$custom_fields)) {
        $listings_query .= ",phone";
    }
    if(in_array('custom_3',$custom_fields)) {
        $listings_query .= ",fax";
    }
    if(count($custom_fields)) {
        $listings_query .= ','.implode(',',$custom_fields);
    }
    $listings_query .= "
    )
    SELECT selector,userid,IF(date_expire < NOW(),'suspended',IF(admin_approved=1,'active',IF(admin_approved=2,'suspended','pending'))) as status,firmname,friendly_url,0,
    business,business_short,loc_id,listing_address1,listing_address2,listing_zip,loc_text,www,
    ip,date,date_update,ip_update,counter,webcounter,mailcounter,rating,banner_show,banner_click,counterip,mail,
    comment,votes";
    if(in_array('custom_2',$custom_fields)) {
        $listings_query .= ",custom_2";
    }
    if(in_array('custom_3',$custom_fields)) {
        $listings_query .= ",custom_3";
    }
    if(count($custom_fields)) {
        $listings_query .= ','.implode(',',$custom_fields);
    }
    $listings_query .= " FROM ".OLD_T_LISTINGS;
$db->Execute("TRUNCATE ".T_LISTINGS);
$db->Execute("TRUNCATE ".T_ORDERS);
$db->Execute($listings_query);

$db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_impression', id, impressions FROM ".T_LISTINGS." WHERE impressions > 0");
$db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_website', id, website_clicks FROM ".T_LISTINGS." WHERE impressions > 0");
$db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_email', id, emails FROM ".T_LISTINGS." WHERE emails > 0");
$db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_banner_impression', id, banner_impressions FROM ".T_LISTINGS." WHERE banner_impressions > 0");
$db->Execute("INSERT IGNORE INTO ".T_STATISTICS." (date,type,type_id,count) SELECT null, 'listing_banner_click', id, banner_clicks FROM ".T_LISTINGS." WHERE banner_clicks > 0");

$db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&amp;','&');");
$db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&#039;','\'');");
$db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&quot;','\"');");
$db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&gt;','>');");
$db->Execute("UPDATE ".T_LISTINGS." SET title=REPLACE(title,'&lt;','<');");

$db->Execute("UPDATE ".T_LISTINGS." l SET primary_category_id=(SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." lc WHERE l.id=lc.list_id LIMIT 1)");
$db->Execute("UPDATE ".T_LISTINGS." l SET location_search_text = CONCAT_WS(', ',NULLIF(l.location_text_1,''),NULLIF(l.location_text_2,''),NULLIF(l.location_text_3,''),NULLIF(l.listing_address1,''),NULLIF(l.listing_address2,''),NULLIF(l.listing_zip,''),(SELECT GROUP_CONCAT(parent.title SEPARATOR ', ') FROM ".T_LOCATIONS." AS node INNER JOIN ".T_LOCATIONS." AS parent ON node.left_ BETWEEN parent.left_ AND parent.right_ WHERE node.id=l.location_id AND parent.level > 0 GROUP BY node.id ORDER BY parent.left_));");

$db->Execute("ALTER TABLE ".T_ORDERS." DROP INDEX order_id");

$db->Execute("INSERT INTO ".T_ORDERS." (order_id,type,type_id,user_id,pricing_id,suspend_overdue_days,date,status,next_due_date,next_invoice_date,ip_address,renewable)
SELECT 0, 'listing_membership', selector, userid, membership, 1, date, IF(date_expire < NOW(),'canceled',IF(admin_approved=1,'active',IF(admin_approved=2,'suspended','pending'))), date_expire, date_expire, ip, 1 FROM ".OLD_T_LISTINGS);

$db->Execute("UPDATE ".T_ORDERS." SET order_id = 3294967295+type_id");

$db->Execute("ALTER TABLE ".T_ORDERS." ADD UNIQUE (order_id)");

// Set listing membership period/price details
$db->Execute("UPDATE ".T_ORDERS." o, ".OLD_T_MEMBERSHIPS." m SET o.amount_recurring=m.price, o.period=REPLACE(REPLACE(LOWER(m.period),'(',''),')',''), o.period_count=m.expiration, next_due_date=IF(m.expiration=0,'0000-00-00',next_due_date), next_invoice_date=IF(m.expiration=0,'0000-00-00',next_due_date) WHERE o.pricing_id=m.selector");

// Fix next invoice dates
$db->Execute("UPDATE ".T_ORDERS." SET next_invoice_date='0000-00-00' WHERE amount_recurring=0.00 OR period_count=0");

// Set listing membership properties
$memberships_update_query = "
    UPDATE
        ".T_LISTINGS." l,".T_MEMBERSHIPS." m,".T_ORDERS." o
    SET
        l.keywords_limit=10,
        l.meta_keywords_limit=10,
        l.meta_description_size=m.meta_description_size,
        l.description_size=m.description_size,
        l.short_description_size=m.short_description_size,
        l.documents_limit=m.documents_limit,
        l.images_limit=m.images_limit,
        l.classifieds_limit=m.classifieds_limit,
        l.classifieds_images_allow=m.classifieds_images_allow,
        l.print_allow=m.print_allow,
        l.claim_allow=m.claim_allow,
        l.pdf_allow=m.pdf_allow,
        l.vcard_allow=m.vcard_allow,
        l.addtofavorites_allow=m.addtofavorites_allow,
        l.suggestion_allow=m.suggestion_allow,
        l.ratings_allow=m.ratings_allow,
        l.reviews_allow=m.reviews_allow,
        l.logo_allow=m.logo_allow,
        l.map_allow=m.map_allow,
        l.www_allow=m.www_allow,
        l.email_friend_allow=m.email_friend_allow,
        l.email_allow=m.email_allow,
        l.zip_allow=m.zip_allow,
        l.phone_allow=m.phone_allow,
        l.fax_allow=m.fax_allow,
        l.address_allow=m.address_allow,
        l.html_editor_allow=m.html_editor_allow,
        l.friendly_url_allow=m.friendly_url_allow,
        l.featured=m.featured,
        l.category_limit=m.category_limit";

    if(count($custom_fields)) {
        foreach($custom_fields as $id) {
            $memberships_update_query .= ',l.'.$id.'_allow=m.'.$id.'_allow';
        }
    }
    if(count($banner_types)) {
        foreach($banner_types as $id) {
            $memberships_update_query .= ',l.banner_limit_'.$id.'=m.banner_limit_'.$id;
        }
    }
    $memberships_update_query .= "
    WHERE
        o.pricing_id=m.id AND o.type_id=l.id AND o.type='listing_membership'";

$db->Execute($memberships_update_query);

// auto detect gateway_id based on string in invoices table?
// auto detect a subscription_id? by using regex on the last transactionid in an invoice?

// Listings
echo 'done.<br /> Importing listing logos.... '; ob_flush();
$handle = opendir(OLD_PMDROOT.'/user_media/logo/images/');
while (false != ($file = readdir($handle))) {
    $matches = array();
    if(preg_match('/^(\d+)\.([a-zA-Z]{3,4})$/',$file,$matches)) {
        $db->Execute("UPDATE ".T_LISTINGS." SET logo_extension=? WHERE id=?",array($matches[2],$matches[1]));
        copy(OLD_PMDROOT.'user_media/logo/images/'.$file,LOGO_PATH.$file);
    }
}
closedir($handle);
$handle = opendir(OLD_PMDROOT.'/user_media/logo/thumb/');
while (false != ($file = readdir($handle))) {
    if(preg_match('/^\d+\.[a-zA-Z]{3,4}$/',$file)) {
        copy(OLD_PMDROOT.'user_media/logo/thumb/'.$file,LOGO_THUMB_PATH.$file);
    }
}
closedir($handle);

echo 'done.'; ob_flush();

echo '</p><a class="btn btn-default" href="step_3.php">Continue to step 3 <i class="glyphicon glyphicon-chevron-right"></i></a>';

include('../../template/footer.tpl');
?>