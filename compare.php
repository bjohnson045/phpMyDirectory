<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

if(ADDON_DISCOUNT_CODES) {
    $PMDR->get('Discount_Codes')->setURLCode();
}

$PMDR->loadLanguage(array('public_compare'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_compare'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('compare_meta_title'),$PMDR->getLanguage('public_compare')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('compare_meta_description'),$PMDR->getLanguage('public_compare')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/compare.php','text'=>$PMDR->getLanguage('public_compare')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'compare.tpl');
$template_content->cache_id = 'compare'.intval(value($_GET,'group_id'));
$template_content->expire = 900;
if(!$template_content->isCached()) {
    if(isset($_GET['group_id'])) {
        $memberships = $db->GetAssoc("SELECT m.*, p.name, p.description, p.id AS product_id FROM ".T_MEMBERSHIPS." m INNER JOIN ".T_PRODUCTS." p ON m.id=p.type_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON pp.product_id=p.id WHERE p.group_id=? AND p.type='listing_membership' AND p.hidden = 0 AND p.active = 1 AND pp.hidden = 0 AND pp.active = 1 GROUP BY p.id ORDER BY p.ordering ASC",array($_GET['group_id']));
    } elseif(isset($_GET['product_id'])) {
        $memberships = $db->GetAssoc("SELECT m.*, p.name, p.description, p.id AS product_id FROM ".T_MEMBERSHIPS." m INNER JOIN ".T_PRODUCTS." p ON m.id=p.type_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON pp.product_id=p.id WHERE p.id=? AND p.type='listing_membership' AND p.hidden = 0 AND p.active = 1 AND pp.hidden = 0 AND pp.active = 1 GROUP BY p.id ORDER BY p.ordering ASC",array($_GET['product_id']));
    } else {
        $memberships = $db->GetAssoc("SELECT m.*, p.name, p.description, p.id AS product_id FROM ".T_MEMBERSHIPS." m INNER JOIN ".T_PRODUCTS." p ON m.id=p.type_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON pp.product_id=p.id WHERE p.type='listing_membership' AND p.hidden = 0 AND p.active = 1 AND pp.hidden = 0 AND pp.active = 1 GROUP BY p.id ORDER BY p.ordering ASC");
    }
    $pricing = $db->GetAll("SELECT pp.id, product_id, period, period_count, price, setup_price, label FROM ".T_PRODUCTS_PRICING." pp INNER JOIN ".T_PRODUCTS." p ON pp.product_id=p.id WHERE  pp.hidden = 0 AND pp.active = 1 ORDER BY p.ordering ASC, pp.ordering ASC");
    $fields = $PMDR->get('Fields')->getFields('listings');
    $banners = $db->GetAll("SELECT * FROM ".T_BANNER_TYPES);

    // Add pricing to each membership
    $pricing_parsed = array();
    foreach($memberships as $key=>$membership) {
        foreach($pricing as $price) {
            if($price['product_id'] == $membership['product_id']) {
                $price['period'] = $PMDR->getLanguage('public_compare_'.$price['period']);
                $pricing_parsed[$key]['pricing'][] = $price;
            }
        }
    }

    $rows = array(
        'category_limit'=>'numerical',
        'featured'=>'boolean',
        'logo_allow'=>'boolean',
        'www_allow'=>'boolean',
        'title_size'=>'numerical',
        'short_description_size'=>'numerical',
        'description_size'=>'numerical',
        'html_editor_allow'=>'boolean',
        'keywords_limit'=>'numerical',
        'phone_allow'=>'boolean',
        'fax_allow'=>'boolean',
        'address_allow'=>'boolean',
        'zip_allow'=>'boolean',
        'map_allow'=>'boolean',
        'hours_allow'=>'boolean',
        'email_allow'=>'boolean',
        'email_friend_allow'=>'boolean',
        'print_allow'=>'boolean',
        'pdf_allow'=>'boolean',
        'vcard_allow'=>'boolean',
        'contact_requests_allow'=>'boolean',
        'reviews_allow'=>'boolean',
        'ratings_allow'=>'boolean',
        'suggestion_allow'=>'boolean',
        'classifieds_limit'=>'numerical',
        'classifieds_images_allow'=>'boolean',
        'images_limit'=>'numerical',
        'documents_limit'=>'numerical',
        'events_limit'=>'numerical',
        'blog_posts_limit'=>'numerical',
        'jobs_limit'=>'numerical',
        'locations_limit'=>'numerical',
        'meta_keywords_limit'=>'numerical',
        'meta_title_size'=>'numerical',
        'meta_description_size'=>'numerical',
        'qrcode_allow'=>'boolean',
        'addtofavorites_allow'=>'boolean',
        'social_links_allow'=>'boolean',
        'share_allow'=>'boolean',
    );

    if(ADDON_LINK_CHECKER) $rows['require_reciprocal'] = 'boolean';

    $rows['www_screenshot_allow'] = 'boolean';

    /** NEW **/
    $option_titles = array();
    foreach($rows AS $value=>$type) {
        $option_titles[$value] = $PMDR->getLanguage('public_compare_'.str_replace('_allow','',$value));
    }
    foreach($fields as $key=>$value) {
        if($value['hidden']) continue;
        $option_titles['custom_'.$value['id'].'_allow'] = $value['name'];
    }
    foreach($banners as $banner) {
        $option_titles['banner_limit_'.$banner['id']] = $banner['name'];
    }
    /** End New **/

    // Setup row titles
    $memberships_parsed = array();
    foreach($rows AS $value=>$type) {
        $memberships_parsed[$value]['title'] = $PMDR->getLanguage('public_compare_'.str_replace('_allow','',$value));
    }
    foreach($fields as $key=>$value) {
        if($value['hidden']) continue;
        $memberships_parsed['custom_'.$value['id'].'_allow']['title'] = $value['name'];
    }
    foreach($banners as $banner) {
        $memberships_parsed['banner_limit_'.$banner['id']]['title'] = $banner['name'];
    }

    // Add custom fields to the boolean array
    foreach($fields as $key=>$value) {
        if($value['hidden']) continue;
        $rows['custom_'.$value['id'].'_allow'] = 'boolean';
    }

    // Add banners to the numerical array
    foreach($banners as $banner) {
        $rows['banner_limit_'.$banner['id']] = 'numerical';
    }

    /** New **/
    $products = array();
    foreach($memberships AS $key=>$m) {
        $products[$m['product_id']] = array(
            'title'=>$m['name'],
            'description'=>$m['description'],
            'options'=>array(),
            'pricing'=>array()
        );
        foreach($rows AS $value=>$type) {
            if($type=='numerical') {
                $products[$m['product_id']]['options'][$value] = intval($m[$value]);
            } else {
                $products[$m['product_id']]['options'][$value] = ($m[$value]) ? 'yes' : '-';
            }
        }
        foreach($pricing as $price) {
            if($price['product_id'] == $m['product_id']) {
                $price['period'] = $PMDR->getLanguage('public_compare_'.$price['period']);
                $products[$m['product_id']]['pricing'][] = $price;
            }
        }
    }
    /** END NEW **/

    // Add values to each row from all memberships
    $membership_names = array();
    foreach($memberships as $m) {
        $membership_names[] = $m['name'];
        foreach($rows AS $value=>$type) {
            if($type=='numerical') {
                $memberships_parsed[$value]['values'][] = intval($m[$value]);
            } else {
                $memberships_parsed[$value]['values'][] = ($m[$value]) ? 'yes' : '-';
            }
        }
    }

    // Remove any rows that are ALL empty
    foreach($rows AS $value=>$type) {
        // Delete any with all same value
        $previous = null;
        foreach($memberships AS $m) {
            if(!is_null($previous) AND $m[$value] !== $previous) continue 2;
            $previous = $m[$value];
        }
        // Delete any all empty/turned off.
        foreach($memberships AS $m) {
            if($m[$value] > 0) continue 2;
        }
        unset($memberships_parsed[$value]);
        unset($option_titles[$value]);
    }

    if(ADDON_DISCOUNT_CODES) {
        $template_content->set('discount_codes',$PMDR->get('Discount_Codes')->getDisplay());
    }

    $template_content->set('options',$option_titles);
    $template_content->set('products',$products);
    $template_content->set('memberships',$memberships_parsed);
    $template_content->set('pricing',$pricing_parsed);
    $template_content->set('membership_names',$membership_names);
}

include(PMDROOT.'/includes/template_setup.php');
?>