<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_listings','general_locations','email_templates','user_orders'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$listing = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($_GET['id']));

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$listing['categories'] = $db->GetCol("SELECT cat_id FROM ".T_LISTINGS_CATEGORIES." WHERE list_id=? AND cat_id!=?",array($listing['id'],$listing['primary_category_id']));
$listing['date'] = $PMDR->get('Dates_Local')->formatDate($listing['date']);
$listing['date_update'] = $PMDR->get('Dates_Local')->formatDate($listing['date_update']);
$listing['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
$listing['url_short'] = preg_replace('/(?<=^.{22}).{4,}(?=.{20}$)/', '...', $listing['url']);


$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_listings_summary.tpl');

$template_content->set('share',$PMDR->get('Sharing')->getHTML($listing['url'],$listing['title'],null,true));

$locations = $PMDR->get('Locations')->getPath($listing['location_id']);
foreach($locations as $loc_key=>$loc_value) {
    $listing['location_'.($loc_key+1)] = $loc_value['title'];
}
if($listing['address_allow']) {
    $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
    $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
    $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];
    $listing['address'] = $PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip'],', ');
}

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('summary'));

$order = $db->GetRow("SELECT * FROM ".T_ORDERS." WHERE type_id=? AND type='listing_membership'",array($listing['id']));
$product_labels = $db->GetRow("SELECT pg.name AS group_name, p.name AS name, pp.label FROM ".T_PRODUCTS_GROUPS." pg INNER JOIN ".T_PRODUCTS." p ON pg.id=p.group_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=?",$order['pricing_id']);

if($PMDR->get('Dates')->isZero($record['next_due_date'])) {
     $order['next_due_date'] = '-';
} else {
     $order['next_due_date'] = $PMDR->get('Dates_Local')->formatDate($record['next_due_date']);
    if(strtotime($record['next_due_date']) < time() AND $record['renewable']) {
         $order['overdue'] = true;
        if($record['amount_recurring'] == 0.00 AND !$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($record['id']))) {
             $order['renew'] = true;
        }
    }
}
$order['date'] = $PMDR->get('Dates_Local')->formatDateTime($order['date']);
$order['product_group_name'] = $product_labels['group_name'];
$order['product_name'] = $product_labels['name'];
$order['subscription_id'] =  $order['subscription_id'] != '' ? $order['subscription_id'] : '-';
if($order['upgrades'] != '' AND $order['status'] != 'pending') {
    $order['upgrades_link'] = true;
}
$order['product_title'] = $product['title'];
$template_content->set('order',$order);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_summary'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_summary'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_listings_summary.php?id='.$listing['id'],'text'=>$PMDR->getLanguage('user_listings_summary')));

$template_content->set('listing',$listing);

$template_content->set('locations_used',$db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($listing['id'])));
$template_content->set('documents_used',$db->GetOne("SELECT COUNT(*) FROM ".T_DOCUMENTS." WHERE listing_id=?",array($listing['id'])));
$template_content->set('classifieds_used',$db->GetOne("SELECT COUNT(*) FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($listing['id'])));
$template_content->set('images_used',$db->GetOne("SELECT COUNT(*) FROM ".T_IMAGES." WHERE listing_id=?",array($listing['id'])));
$template_content->set('blog_posts_used',$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG." WHERE listing_id=?",array($listing['id'])));
$template_content->set('events_used',$db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS." WHERE listing_id=?",array($listing['id'])));

$banner_types = $db->GetAll("SELECT id, name FROM ".T_BANNER_TYPES);
foreach($banner_types AS $type) {
    $template_content->set('banners_used_'.$type['id'],$db->GetOne("SELECT COUNT(*) FROM ".T_BANNERS." WHERE listing_id=? AND type_id=?",array($listing['id'],$type['id'])));
}
$template_content->set('banner_types',$banner_types);

$reviews = $db->GetAll("SELECT r.id, r.listing_id, r.date, r.title, ra.rating FROM ".T_REVIEWS." r INNER JOIN ".T_RATINGS." ra ON r.rating_id=ra.id WHERE r.listing_id=? LIMIT 5",array($listing['id']));
foreach($reviews AS &$review) {
    $review['date'] = $PMDR->get('Dates_Local')->formatDate($review['date']);
    $review['rating_static'] = $PMDR->get('Ratings')->printRatingStatic($review['rating']);
}
$template_content->set('reviews',$reviews);
$template_content->set('primary_category',$PMDR->get('Categories')->getPathDisplay($PMDR->get('Categories')->getPath($listing['primary_category_id']),' &#8594; ',true,'','_blank'));

if($listing['require_reciprocal'] AND !$listing['www_reciprocal']) {
    $template_message = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/message.tpl');
    $template_message->set('message_types',array('error'=>array($PMDR->getLanguage('user_listings_reciprocal_error',array('user_listings.php?action=edit&id='.$listing['id'])))));
    $template_content->set('reciprocal_retry',$template_message);
}

include(PMDROOT.'/includes/template_setup.php');
?>