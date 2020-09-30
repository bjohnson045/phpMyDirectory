<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_search_images'));

$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', gallery:{enabled:true}, closeOnContentClick: true });});</script>',20);

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search_images'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search.php','text'=>$PMDR->getLanguage('public_search_images')));

$paging = $PMDR->get('Paging');
$paging->modRewrite = false;
$paging->setResultsNumber($PMDR->getConfig('count_search'));
$paging->linksNumber = 5;

$image_results = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS i.id, i.listing_id, i.title, i.description, i.date, i.extension, l.title AS listing_title, l.friendly_url
                                 FROM ".T_IMAGES." i INNER JOIN ".T_LISTINGS." l ON i.listing_id=l.id
                                 WHERE (i.title LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%")." OR i.description LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%").") AND l.status='active'
                                 LIMIT ".$paging->limit1.",".$paging->limit2);

$paging->setTotalResults($image_count = $db->FoundRows());
$pageArray = $paging->getPageArray();

$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

foreach($image_results as $key=>$image) {
    $image_results[$key]['thumb'] = get_file_url_cdn(IMAGES_THUMBNAILS_PATH.$image['id'].'.'.$image['extension']);
    $image_results[$key]['image'] = get_file_url_cdn(IMAGES_PATH.$image['id'].'.'.$image['extension']);
    $image_results[$key]['url'] = $PMDR->get('Listings')->getURL($image['listing_id'],$image['friendly_url'],'','/images.html','listing_images.php');
    $image_results[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($image['date']);
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search_images.tpl');
$template_content->set('image_results',$image_results);
$template_content->set('image_count',$image_count);
$template_content->set('page_navigation',$template_page_navigation);

include(PMDROOT.'/includes/template_setup.php');
?>