<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_events','email_templates'));

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['events_limit']) {
    $PMDR->get('Error',404);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_events'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_events'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_events'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_events'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_events'),'listing_title'=>$listing['title']);
foreach($meta_replace AS $find=>$replace) {
    $title = str_replace('*'.$find.'*',$replace,$title);
    $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
    $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
    $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),'text'=>$listing['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_events')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_events.tpl');

$paging = $PMDR->get('Paging');
$records = $PMDR->get('Events')->getListingEvents($listing['id'],$paging->limit1,$paging->limit2);
$template_content->set('records',$records);
$paging->setTotalResults($db->FoundRows());

$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>