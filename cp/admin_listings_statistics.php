<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_listings','admin_users','admin_listings_statistics','email_templates'));

$PMDR->get('Authentication')->checkPermission('admin_listings_view');

$listings = $PMDR->get('Listings');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_listings_statistics.tpl');

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

if(!empty($_GET['listing_id'])) {
    if($listing = $listings->getRow($_GET['listing_id'])) {
        $template_content->set('listing_header',$PMDR->get('Listing',$_GET['listing_id'])->getAdminHeader('statistics'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    } else {
        redirect();
    }
}

if(isset($_GET['action']) AND $_GET['action'] == 'send_statistics_email') {
    $PMDR->get('Email_Templates')->send('listings_statistics',array('to'=>$listing['user_id'],'listing_id'=>$listing['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('email_sent'));
    redirect(rebuild_url(array(),array('action')));
}

$template_content->set('send_statistics_url',rebuild_url(array('action'=>'send_statistics_email')));

$range_form = $PMDR->getNew('Form');
$range_form->addField('date_start','date');
$range_form->addField('date_end','date');
$range_form->addField('submit','submit');

$template_content->set('title',$PMDR->getLanguage('admin_listings_statistics'));
$template_content->set('statistics',$PMDR->get('Statistics')->getStatistics($_GET['type'],$_GET['listing_id'],$_GET['date_start'],$_GET['date_end']));
$template_content->set('group_type',$PMDR->get('Statistics')->getGroupType($_GET['type']));

if(!isset($_GET['type'])) {
    $_GET['type'] = 'all_time';
}
$sub_title = $PMDR->getLanguage('admin_listings_statistics_'.$_GET['type']);

if($range_form->wasSubmitted('submit')) {
    $data = $range_form->loadValues();
    redirect(null,array('type'=>'date_range','listing_id'=>$_GET['listing_id'],'date_start'=>$data['date_start'],'date_end'=>$data['date_end']));
}

$template_content->set('subtitle',$sub_title);
$template_content->set('range_form',$range_form);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>