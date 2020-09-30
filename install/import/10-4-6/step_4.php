<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 4 of 7</h3><p>';

$batcher = $PMDR->get('Database_Batcher');

if(!isset($_GET['action'])) {
    echo 'Importing listings.... '; ob_flush();
    $db->Execute("TRUNCATE ".T_LISTINGS_CATEGORIES);
    if($db->GetRow("SELECT * FROM ".OLD_T_CATEGORIES." WHERE cat_id=1")) {
        $db->Execute("INSERT INTO ".T_LISTINGS_CATEGORIES." (list_id, cat_id) SELECT list_id, cat_id+1 as new_cat_id FROM ".OLD_T_LIST2CAT);
    } else {
        $db->Execute("INSERT INTO ".T_LISTINGS_CATEGORIES." (list_id, cat_id) SELECT list_id, cat_id FROM ".OLD_T_LIST2CAT);
    }
    // Listings
    $custom_fields = $db->GetCol("SELECT CONCAT('custom_',id)  FROM ".T_FIELDS);
    $banner_types = $db->GetCol("SELECT id FROM ".T_BANNER_TYPES);

    $listings_query = "
    INSERT INTO ".T_LISTINGS." (
        id,user_id,title,description,location_id,listing_address1,listing_zip,location_text_1,www,
        ip,date,date_update,ip_update,impressions,website_clicks,emails,rating,banner_impressions,banner_clicks,counterip,mail,comment,votes,phone,fax";
    if(count($custom_fields)) {
        $listings_query .= ','.implode(',',$custom_fields);
    }
    $listings_query .= "
    )
    SELECT selector,userid,firmname,business,IF(loc_four,loc_four,IF(loc_three,loc_three,IF(loc_two,loc_two,loc_one))),address,zip,loc_text,www,
    ip,date,date_update,ip_update,counter,webcounter,mailcounter,rating,banner_show,banner_click,counterip,mail,comment,votes,phone,fax";
    if(count($custom_fields)) {
        $listings_query .= str_replace('custom_','reserved_',','.implode(',',$custom_fields));
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

    echo "<script language = 'JavaScript' type = 'text/javascript'>function runAgain() { window.location.href = \"".URL_NOQUERY."?action=batching&current=0&source=".OLD_T_LISTINGS."\"; } window.onload = runAgain;</script>";
}

if($_GET['action'] == 'batching') {
    // Load the batch and see where we are
    $batcher->loadBatch($_GET['source'], $_GET['current']);
    // Set the batcher query (to get the data)
    $batcher->query = "SELECT * FROM ".$batcher->source." ORDER BY selector ASC LIMIT ?,?";
    // Get the records within the current batch
    $records = $batcher->getBatch();
    // Cycle through the records in the batch
    foreach($records as $record) {
        if($record['flag'] == '') {
            $record['flag'] = 'D';
        }

        $membership = $db->GetRow("SELECT * FROM ".OLD_T_MEMBERSHIPS." WHERE flag=?",array($record['flag']));

        if(!$membership) continue;

        $update_array = array();
        if($record['loc_four']) {
            $location = 'four_'.$record['loc_four'];
        } elseif($record['loc_three']) {
            $location = 'three_'.$record['loc_three'];
        } elseif($record['loc_two']) {
            $location = 'two_'.$record['loc_two'];
        } else {
            $location = 'one_'.$record['loc_one'];
        }
        $update_array['location_id'] = $db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE importer_original_id=?",array($location));

        if($membership['expiration'] == 0 OR $record['date_upgrade'] == '0000-00-00' OR $record['date_upgrade'] == NULL OR $record['date_upgrade'] == 'NULL') {
            $update_array['next_due_date'] = '0000-00-00';
            $update_array['next_invoice_date'] = '0000-00-00';
            $update_array['status'] = 'active';
        } else {
            $update_array['next_due_date'] = strtotime($record['date_upgrade']);
            $update_array['next_invoice_date'] = strtotime($record['date_upgrade']);

            if($membership['period'] == 'Day(s)') {
                $update_array['next_due_date'] += $membership['expiration'] * 86400;
                $update_array['next_invoice_date'] += $membership['expiration'] * 86400;
            } elseif($membership['period'] == 'Month(s)') {
                $update_array['next_due_date'] += $membership['expiration'] * 2629743;
                $update_array['next_invoice_date'] += $membership['expiration'] * 2629743;
            } elseif($membership['period'] == 'Year(s)') {
                $update_array['next_due_date'] += $membership['expiration'] * 31556926;
                $update_array['next_invoice_date'] += $membership['expiration'] * 31556926;
            }

            $update_array['next_due_date'] = date('Y-m-d',$update_array['next_due_date']);
            $update_array['next_invoice_date'] = date('Y-m-d',$update_array['next_invoice_date']);

            if($record['firmstate'] == 'off' OR $update_array['next_due_date'] < time()) {
                $update_array['status'] = 'suspended';
            } else {
                $update_array['status'] = 'active';
            }
        }
        if($membership['price'] == '0.00') {
            $update_array['next_invoice_date'] = '0000-00-00';
        }

        $db->Execute("UPDATE ".T_LISTINGS." SET  friendly_url=?, primary_category_id=?, status=?, location_id=? WHERE id=?",
        array(Strings::rewrite($record['firmname']),
        $db->GetOne("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=?",array($record['selector'])),$update_array['status'],$update_array['location_id'],$record['selector']));

        $PMDR->get('Orders')->insert(
            array(
                'order_id'=>$PMDR->get('Orders')->getRandomOrderID(),
                'type'=>'listing_membership',
                'type_id'=>$record['selector'],
                'user_id'=>$record['userid'],
                'pricing_id'=>$membership['selector'],
                'suspend_overdue_days'=>1,
                'date'=>$record['date'],
                'status'=>'active',
                'next_due_date'=>$update_array['next_due_date'],
                'next_invoice_date'=>$update_array['next_invoice_date'],
                'ip_address'=>(is_null($record['ip']) ? '' : $record['ip']),
                'renewable'=>1
            )
        );

        echo 'Listing '.$record['selector'].' imported.<br />'; ob_flush();
    }
    // Unload the batch and redirect if needed
    echo $batcher->unloadBatch();
} elseif($_GET['action'] == 'complete') {
    // Set listing membership period/price details
    $db->Execute("UPDATE ".T_ORDERS." o, ".OLD_T_MEMBERSHIPS." m SET o.amount_recurring=m.price, o.period=REPLACE(REPLACE(LOWER(m.period),'(',''),')',''), o.period_count=m.expiration WHERE o.pricing_id=m.selector");

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

    // Listings
    echo 'Importing listings.... done.<br />Importing listing logos.... '; ob_flush();
    $handle = opendir(OLD_PMDROOT.'logo/');
    while (false != ($file = readdir($handle))) {
        $matches = array();
        if(preg_match('/^(\d+)\.([a-zA-Z]{3,4})$/',$file,$matches)) {
            copy(OLD_PMDROOT.'logo/'.$file,LOGO_PATH.$file);
            $db->Execute("UPDATE ".T_LISTINGS." SET logo_extension=? WHERE id=?",array($matches[2],$matches[1]));
            $image = $PMDR->get('Image_Handler');
            $image->loadImage(OLD_PMDROOT.'logo/'.$file);
            $image->setOptions(array('enlarge'=>true,'width'=>120,'height'=>100));
            $image->save(LOGO_THUMB_PATH.$file);
        }
    }
    closedir($handle);

    echo 'Done!</p><a class="btn btn-default" href="step_5.php">Continue to step 5</a>';
}

include('../../template/footer.tpl');
?>