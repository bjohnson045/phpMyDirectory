<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_listings','user_orders','user_listings_statistics'));

/** @var AuthenticationUser */
$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

/** @var Users */
$users = $PMDR->get('Users');
/** @var Listings */
$listings = $PMDR->get('Listings');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

$listing = $listings->getRow($_GET['listing_id']);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_statistics'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_statistics'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

if(!isset($_GET['type'])) {
    $_GET['type'] = 'all_time';
}

$sub_title = $PMDR->getLanguage('user_listings_statistics_'.$_GET['type']);

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/members/user_listings_statistics.tpl');

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('statistics'));

$template_content->set('title',$PMDR->getLanguage('user_listings_statistics'));
$template_content->set('listing',$listing);
$template_content->set('statistics',$PMDR->get('Statistics')->getStatistics($_GET['type'],$_GET['listing_id'],$_GET['date_start'],$_GET['date_end']));
$template_content->set('group_type',$PMDR->get('Statistics')->getGroupType($_GET['type']));
$template_content->set('subtitle',$sub_title);

$range_form = $PMDR->getNew('Form');
$range_form->addField('date_start','date');
$range_form->addField('date_end','date');
$range_form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit')));

if($range_form->wasSubmitted('submit')) {
    $data = $range_form->loadValues();
    redirect(null,array('type'=>'date_range','listing_id'=>$_GET['listing_id'],'date_start'=>$data['date_start'],'date_end'=>$data['date_end']));
}
$template_content->set('range_form',$range_form);

include(PMDROOT.'/includes/template_setup.php');
?>