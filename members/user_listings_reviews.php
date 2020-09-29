<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_listings'));

/** @var AuthenticationUser */
$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

/** @var Users */
$users = $PMDR->get('Users');
/** @var Listings */
$listings = $PMDR->get('Listings');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

$listing = $listings->getRow($_GET['listing_id']);

$PMDR->get('Sharing')->loadJavascript();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_reviews'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_reviews'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/members/user_listings_reviews.tpl');
$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('reviews'));
$template_content->set('title',$PMDR->getLanguage('user_listings_reviews'));

$template_content->set('share_button_script',$PMDR->get('Sharing')->getButtonScript());

$table_list = $PMDR->get('TableList');
$table_list->addColumn('title',$PMDR->getLanguage('user_listings_reviews_title'));
$table_list->addColumn('status',$PMDR->getLanguage('user_listings_reviews_status'));
$table_list->addColumn('date',$PMDR->getLanguage('user_listings_reviews_date'));
$table_list->addColumn('rating',$PMDR->getLanguage('user_listings_reviews_rating'));
$table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
$paging = $PMDR->get('Paging');

$records = $db->GetAll("
SELECT reviews.*, ra.rating, COUNT(rc.id) AS comments
FROM
    (SELECT r.* FROM ".T_REVIEWS." r WHERE listing_id=? LIMIT ?,?) AS reviews
    LEFT JOIN ".T_RATINGS." ra ON reviews.rating_id=ra.id
    LEFT JOIN ".T_REVIEWS_COMMENTS." rc ON reviews.id=rc.review_id
GROUP BY reviews.id",
array($listing['id'],$table_list->paging->limit1,$table_list->paging->limit2));

$table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS." r WHERE listing_id=?",array($listing['id'])));

foreach($records as $key=>$record) {
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $records[$key]['rating_static'] = $PMDR->get('Ratings')->printRatingStatic($record['rating']);
}
$table_list->addRecords($records);
$table_list->addPaging($paging);
$table_list->addToTemplate($template_content);

include(PMDROOT.'/includes/template_setup.php');
?>