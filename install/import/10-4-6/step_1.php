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
$db->Execute("DELETE FROM ".T_USERS." WHERE id!=1");
$db->Execute("DELETE FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id!=1");
$db->Execute("INSERT INTO ".T_USERS."
              (id,login,pass,cookie_salt,password_hash,user_email)
              SELECT userid,login,pass,'".rand(4,4)."','md5',mail FROM ".OLD_T_USERS." ORDER BY userid DESC");
$db->Execute("INSERT INTO ".T_USERS_GROUPS_LOOKUP." (user_id,group_id) SELECT id, 4 FROM ".T_USERS." WHERE id!=1");

echo 'done.<br />Importing custom fields....'; ob_flush();
// Fields
$db->Execute("TRUNCATE ".T_LISTINGS);
$db->Execute("TRUNCATE ".T_FIELDS);
$db->Execute("TRUNCATE ".T_FIELDS_GROUPS);
$db->Execute("INSERT INTO ".T_FIELDS_GROUPS." SET title='Listing Fields', type='listings', ordering=1");
$group_id = $db->Insert_ID();

$current_custom_fields = $db->GetCol("SHOW COLUMNS FROM ".T_LISTINGS." LIKE 'custom_%'");
foreach($current_custom_fields as $field) {
    $db->Execute("ALTER TABLE ".T_LISTINGS." DROP ".$field);
}

$current_custom_fields = $db->GetCol("SHOW COLUMNS FROM ".T_MEMBERSHIPS." LIKE 'custom_%'");
foreach($current_custom_fields as $field) {
    $db->Execute("ALTER TABLE ".T_MEMBERSHIPS." DROP ".$field);
}

// Reserved 1
$insert_array['group_id'] = (int) $group_id;
$insert_array['group_type'] = 'listings';
$insert_array['name'] = 'Reserved Field 1';
$insert_array['type'] = 'text';
$insert_array['required'] = 0;
$insert_array['options'] = '';
$insert_array['selected'] = '';
$insert_array['ordering'] = 0;
$insert_array['search'] = 0;
$insert_array['admin_only'] = 0;
$PMDR->get('Fields')->insert($insert_array);
// Reserved 2
$insert_array['name'] = 'Reserved Field 2';
$PMDR->get('Fields')->insert($insert_array);
// Reserved 3
$insert_array['name'] = 'Reserved Field 3';
$PMDR->get('Fields')->insert($insert_array);
unset($insert_array);

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
$insert_array['name'] = 'Side Banner';
$insert_array['width'] = '200';
$insert_array['height'] = '200';
$insert_array['description'] = '';
$insert_array['filesize'] = '50000';
$insert_array['type'] = 'image';
$PMDR->get('Banners_Types')->insert($insert_array);
$insert_array['name'] = 'Top Banner';
$insert_array['width'] = '468';
$insert_array['height'] = '68';
$PMDR->get('Banners_Types')->insert($insert_array);
unset($insert_array);

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
        if(strstr($key,'reserved_')) {
            if($om[$key] == '') $om[$key] = 0;
            $custom_fields[str_replace('reserved_','custom_',$key)] = $om[$key];
        }
    }
    $query = "INSERT INTO ".T_MEMBERSHIPS." SET
        id=".$om['selector'].",
        category_limit=5,
        featured=0,
        friendly_url_allow=1,
        html_editor_allow=1,
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
        print_allow=1,
        pdf_allow=1,
        vcard_allow=1,
        classifieds_images_allow=".$om['products_images'].",
        classifieds_limit=".$om['products_set_products'].",
        images_limit=".$om['set_gallery_images'].",
        documents_limit=".$om['set_documents'].",
        short_description_size=100,
        description_size=".$om['description_size'].",
        meta_description_size=".$om['description_size'].",
        meta_keywords_limit=10,
        keywords_limit=10,
        banner_limit_1=".$om['banner'].",
        banner_limit_2=".$om['banner2'];
    foreach($custom_fields as $key=>$value) {
        // Insert the field
        $query .= ",".$key."_allow=".$value;
    }

    $db->Execute($query);
    $db->Execute("INSERT INTO ".T_PRODUCTS." SET group_id=?,type='listing_membership',type_id=?,name=?,description='',hidden=?,ordering=?,taxed=0",array($group_id,$om['selector'],$om['name'],($om['enabled'] ? 0 : 1),$om['selector']));
    $product_id = $db->Insert_ID();
    $db->Execute("INSERT INTO ".T_PRODUCTS_PRICING." (id,product_id,period,period_count,setup_price,price,prorate,prorate_day,prorate_day_next_month,ordering,activate,renewable) VALUES
                  (?,?,?,?,'0.00',?,0,0,0,0,'payment',1)",array($om['selector'],$product_id,strtolower(str_replace(array('(',')'),'',$om['period'])),$om['expiration'],$om['price']));
}

echo 'done.'; ob_flush();

echo '</p><a class="btn btn-default" href="step_2.php">Continue to step 2</a>';

include('../../template/footer.tpl');
?>