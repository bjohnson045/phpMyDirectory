<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_classified'));

if(!$classified = $db->GetRow("SELECT * FROM ".T_CLASSIFIEDS." WHERE id=? AND (expire_date > NOW() OR expire_date IS NULL)",array($_GET['classified_id']))) {
    $PMDR->get('Error',404);
}

if(!$classified_images = $PMDR->get('Classifieds')->getImages($classified['id'])) {
    redirect($PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']));
}

if(!is_null($classified['listing_id'])) {
    $listing = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($classified['listing_id']));
    if(!$listing['classifieds_images_allow']) {
        redirect($PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']));
    }
    $listing['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
}

$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', gallery:{enabled:true}, closeOnContentClick: true });});</script>',20);

$PMDR->setAdd('page_title',$classified['title'].' '.$PMDR->getLanguage('public_classified_images'));
$PMDR->set('meta_title',$classified['title'].' '.$PMDR->getLanguage('public_classified_images'));
$PMDR->setAddArray('breadcrumb',array('link'=>$listing['url'],'text'=>$listing['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/classifieds.html','listing_classifieds.php'),'text'=>$PMDR->getLanguage('public_classified')));
$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']),'text'=>$classified['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_classified_images')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'classified_images.tpl');
$template_content->set('classified_images',$classified_images);
$template_content->set('classified_url',$PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']));
$template_content->set('classified_title',$classified['title']);
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>