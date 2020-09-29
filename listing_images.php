<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_listing','public_listing_images','email_templates'));

// Load the listing from the database, if it does not exist then redirect to the home page
if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['images_limit']) {
    $PMDR->get('Error',404);
}

// Load the template file
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_images.tpl');

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_images'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_images'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_images'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_images'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_images'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_images')));

// Load the needed CSS and javascript for displaying listing images
$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', gallery:{enabled:true}, closeOnContentClick: true });});</script>',20);

$paging = $PMDR->get('Paging');
$paging->setResultsNumber(20);

// Get the images from the database
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_IMAGES." WHERE listing_id=? ORDER BY ordering ASC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
$paging->setTotalResults($db->FoundRows());
$page_array = $paging->getPageArray();
$template_page_navigation = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$page_array);
$template_content->set('page_navigation',$template_page_navigation);

// Looks through the images and get the thumbnail url, image url, and description
foreach($records as $key=>$record) {
    $records[$key]['thumb'] = get_file_url_cdn(IMAGES_THUMBNAILS_PATH.$record['id'].'.'.$record['extension']);
    $records[$key]['image'] = get_file_url_cdn(IMAGES_PATH.$record['id'].'.'.$record['extension']);
    $records[$key]['description'] = Strings::nl2br_replace($record['description']);
}

// Set the template variables
$template_content->set('images',$records);
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>