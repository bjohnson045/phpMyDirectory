<?php
include('../../../defaults.php');

$upgrade_data = $_SESSION['upgrade_data'];

include('./defaults_upgrade.php');

include('../../template/header.tpl');
echo '<h3>Step 1 of 7</h3><p>';

// Users - Anonymous user?
echo 'Importing users.... '; ob_flush();
// Get count of old users so we can resolve any conflicts
$total_old_users = $db->GetOne("SELECT MAX(userid) FROM ".OLD_T_USERS);
// Get the users who may conflict (userid = 1)
$db->Execute("UPDATE ".OLD_T_USERS." SET userid=? WHERE userid=1",array($total_old_users+1));
$db->Execute("UPDATE ".OLD_T_LISTINGS." SET userid=? WHERE userid=1",array($total_old_users+1));
$db->Execute("UPDATE ".OLD_T_INVOICES." SET userid=? WHERE userid=1",array($total_old_users+1));
//$db->Execute("UPDATE ".T_USERS." SET id=? WHERE id=1",array($total_old_users+1));
$db->Execute("DELETE FROM ".T_USERS." WHERE id!=1");
$db->Execute("DELETE FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id!=1");
$db->Execute("INSERT INTO ".T_USERS."
              (id,login,pass,cookie_salt,password_hash,user_email,user_first_name,user_last_name,user_organization,user_address1,user_address2,user_city,user_state,user_country,user_zip,user_phone,user_fax,user_comment)
              SELECT userid,login,pass,'".rand(4,4)."','md5',user_email,user_first_name,user_last_name,user_organization,user_address1,user_address2,user_city,user_state,user_country,user_zip,user_phone,user_fax,user_comment FROM ".OLD_T_USERS." ORDER BY userid DESC");
$db->Execute("INSERT INTO ".T_USERS_GROUPS_LOOKUP." (user_id,group_id) SELECT userid, 4 FROM ".OLD_T_USERS." WHERE mail_valid='1'");
$db->Execute("INSERT INTO ".T_USERS_GROUPS_LOOKUP." (user_id,group_id) SELECT userid, 5 FROM ".OLD_T_USERS." WHERE mail_valid='0'");

echo 'done.<br /> Importing admin accounts....'; ob_flush();
// Admin account
$old_accounts = $db->GetAll("SELECT * FROM ".OLD_T_ADMIN." WHERE userid!=1 ORDER BY userid ASC");
// Loop through any other admin accounts and add them
foreach($old_accounts as $old_account) {
    //if($old_account['userid'] == $total_old_users+2) {
    //    $main_account = array($old_account['email'],$old_account['password'],rand(4,4),$old_account['email'],($total_old_users+1));
    //    $db->Execute("UPDATE ".T_USERS." SET login=?, pass=?, salt=?, user_email=? WHERE id=?",array($main_account));
    //} else {
        $main_account = array($old_account['email'],$old_account['password'],rand(4,4),$old_account['email']);
        $db->Execute("INSERT INTO ".T_USERS." SET login=?, pass=?, cookie_salt=?, password_hash='md5', user_email=?",$main_account);
        $db->Execute("INSERT INTO ".T_USERS_GROUPS_LOOKUP." SET user_id=?, group_id=2",array($db->Insert_ID()));
    //}
    unset($main_account);
}
unset($old_accounts);
unset($old_account);

echo 'done.<br />Importing custom fields....'; ob_flush();
// Fields
$db->Execute("TRUNCATE ".T_LISTINGS);
$db->Execute("TRUNCATE ".T_FIELDS);
$db->Execute("TRUNCATE ".T_FIELDS_GROUPS);
$db->Execute("INSERT INTO ".T_FIELDS_GROUPS." SET title='Listing Fields', type='listings', ordering=1");
$group_id = $db->Insert_ID();
$old_fields = $db->GetAll("SELECT * FROM ".OLD_T_FIELDS);

$current_custom_fields = $db->GetCol("SHOW COLUMNS FROM ".T_LISTINGS." LIKE 'custom_%'");
foreach($current_custom_fields as $field) {
    $db->Execute("ALTER TABLE ".T_LISTINGS." DROP ".$field);
}

$current_custom_fields = $db->GetCol("SHOW COLUMNS FROM ".T_MEMBERSHIPS." LIKE 'custom_%'");
foreach($current_custom_fields as $field) {
    $db->Execute("ALTER TABLE ".T_MEMBERSHIPS." DROP ".$field);
}

foreach($old_fields as $key=>$f) {
    $insert_array['id'] = (int) $f['id'];
    $insert_array['group_id'] = (int) $group_id;
    $insert_array['group_type'] = 'listings';
    $insert_array['name'] = $f['name'];
    if($f['type'] == 'Input') {
        $insert_array['type'] = 'text';
    } elseif($f['type'] == 'Text Area') {
        $insert_array['type'] = 'textarea';
    } elseif($f['type'] == 'Multiple Select') {
        $insert_array['type'] = 'select_multiple';
    } else {
        $insert_array['type'] = strtolower($f['type']);
    }
    $insert_array['required'] =  (int) $f['required'];
    $insert_array['options'] = str_replace(',',"\n",$f['options']);
    $insert_array['selected'] = (int) $f['selected'];
    $insert_array['ordering'] = (int) $f['ordering'];
    $insert_array['search'] = 0;
    $insert_array['admin_only'] = 0;
    //$PMDR->get('Fields')->delete($f['id']);
    $PMDR->get('Fields')->insert($insert_array);
    unset($insert_array);
    unset($key);
}
unset($old_fields);

echo 'done.<br />Importing banner types....'; ob_flush();

$current_banner_types = $db->GetCol("SHOW COLUMNS FROM ".T_LISTINGS." LIKE 'banner_limit_%'");
foreach($current_banner_types as $field) {
    $db->Execute("ALTER TABLE ".T_LISTINGS." DROP ".$field);
}

$current_banner_types = $db->GetCol("SHOW COLUMNS FROM ".T_MEMBERSHIPS." LIKE 'banner_limit_%'");
foreach($current_banner_types as $field) {
    $db->Execute("ALTER TABLE ".T_MEMBERSHIPS." DROP ".$field);
}

$db->Execute("TRUNCATE ".T_BANNER_TYPES);
// Banner Types
$old_types = $db->GetAll("SELECT * FROM ".OLD_T_BANNER_TYPES);
foreach($old_types as $key=>$f) {
    $insert_array['id'] = (int) $f['id'];
    $insert_array['name'] = $f['name'];
    $insert_array['description'] = $f['description'];
    $insert_array['width'] = $f['width'];
    $insert_array['height'] = $f['height'];
    $insert_array['filesize'] = $f['filesize'];
    $insert_array['type'] = 'image';
    //$PMDR->get('Banners_Types')->delete($f['id']);
    $PMDR->get('Banners_Types')->insert($insert_array);
    unset($insert_array);
    unset($key);
}
unset($old_types);

echo 'done.<br />Importing memberships.... '; ob_flush();
// Memberships
$db->Execute("TRUNCATE ".T_MEMBERSHIPS);
$db->Execute("TRUNCATE ".T_PRODUCTS_GROUPS);
$db->Execute("TRUNCATE ".T_PRODUCTS);
$db->Execute("TRUNCATE ".T_PRODUCTS_PRICING);
$db->Execute("INSERT INTO ".T_PRODUCTS_GROUPS." SET name='Listing Memberships', hidden=0, ordering=0"); // do a replace?
$group_id = $db->Insert_ID();
$old_memberships = $db->GetAll("SELECT * FROM ".OLD_T_MEMBERSHIPS);
foreach($old_memberships as $om) {
    $custom_fields = array();
    $banner_types = array();
    foreach($om as $key=>$value) {
        if($value == 'YES') {
            $om[$key] = 1;
        } elseif($value == 'NO') {
            $om[$key] = 0;
        }
        if(strstr($key,'custom_')) {
            $custom_fields[$key] = $om[$key];
        } elseif(strstr($key,'banner_')) {
            $banner_types[$key] = $om[$key];
        }
    }
    $query = "INSERT INTO ".T_MEMBERSHIPS." SET
        id=".$om['selector'].",
        category_limit=".$om['category_limit'].",
        featured=".$om['featured_sidebox'].",
        friendly_url_allow=1,
        html_editor_allow=".$om['html_editor'].",
        phone_allow=1,
        fax_allow=1,
        address_allow=".$om['address'].",
        zip_allow=".$om['zip'].",
        email_allow=".$om['email'].",
        email_friend_allow=1,
        www_allow=".$om['www'].",
        map_allow=".$om['map'].",
        logo_allow=".$om['logo'].",
        reviews_allow=1,
        ratings_allow=1,
        suggestion_allow=1,
        claim_allow=1,
        addtofavorites_allow=1,
        pdf_allow=1,
        vcard_allow=1,
        print_allow=1,
        classifieds_images_allow=".$om['products_images'].",
        classifieds_limit=".$om['products_set_products'].",
        images_limit=".$om['set_gallery_images'].",
        documents_limit=".$om['set_documents'].",
        short_description_size=".$om['short_description_size'].",
        description_size=".$om['description_size'].",
        meta_description_size=".$om['description_size'].",
        meta_keywords_limit=10,
        keywords_limit=10";
    foreach($custom_fields as $key=>$value) {
        // Insert the field
        $query .= ",".$key."_allow=".$value;
    }
    foreach($banner_types as $key=>$value) {
        // Insert the banner type
        $query .= ",".str_replace('banner_','banner_limit_',$key)."=".$value;
    }
    $db->Execute($query);
    $db->Execute("INSERT INTO ".T_PRODUCTS." SET group_id=?,type='listing_membership',type_id=?,name=?,description='',hidden=?,ordering=?,taxed=0",array($group_id,$om['selector'],$om['name'],($om['enabled'] ? 0 : 1),$om['selector']));
    $product_id = $db->Insert_ID();
    $db->Execute("INSERT INTO ".T_PRODUCTS_PRICING." (id,product_id,period,period_count,setup_price,price,prorate,prorate_day,prorate_day_next_month,ordering,activate,renewable) VALUES
                  (?,?,?,?,'0.00',?,0,0,0,0,'payment',1)",array($om['selector'],$product_id,strtolower(str_replace(array('(',')'),'',$om['period'])),$om['expiration'],$om['price']));
}

echo 'done.'; ob_flush();

echo '</p><a class="btn btn-default" href="step_2.php">Continue to step 2 <i class="glyphicon glyphicon-chevron-right"></i></a>';

include('../../template/footer.tpl');
?>