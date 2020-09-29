<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_listing_documents'));

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['documents_limit']) {
    $PMDR->get('Error',404);
}

if(value($_GET,'action') == 'download') {
    $PMDR->get('Documents')->download($_GET['download_id']);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_documents'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_documents'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_documents'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_documents'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_documents'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_documents')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_documents.tpl');

$records = $db->GetAll("SELECT * FROM ".T_DOCUMENTS." WHERE listing_id=? ORDER BY id ASC",array($listing['id']));
foreach($records as $key=>$record) {
    $records[$key]['download_url'] = URL_NOQUERY.'?action=download&download_id='.$record['id'].'&id='.$listing['id'];
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $records[$key]['share'] = $PMDR->get('Sharing')->getHTML($records[$key]['download_url'],$record['title'])->render();
}
$template_content->set('records',$records);

$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>