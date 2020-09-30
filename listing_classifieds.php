<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_classified','email_templates'));

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['classifieds_limit']) {
    $PMDR->get('Error',404);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_classified'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_classified'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_classified'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_classified'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_classified'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_classified')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'listing_classifieds.tpl');

$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS c.*, GROUP_CONCAT(ci.id,'.',ci.extension SEPARATOR ',') AS images FROM ".T_CLASSIFIEDS." c LEFT JOIN ".T_CLASSIFIEDS_IMAGES." ci ON c.id=ci.classified_id WHERE c.listing_id=? AND (expire_date > NOW() OR expire_date IS NULL) GROUP BY c.id ORDER BY date DESC LIMIT ".$paging->limit1.",".$paging->limit2,array($listing['id']));
$paging->setTotalResults($db->FoundRows());
$page_array = $paging->getPageArray();
$template_page_navigation = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$page_array);
$template_content->set('page_navigation',$template_page_navigation);
foreach($records as $key=>$record) {
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $records[$key]['expire_date'] = $PMDR->get('Dates_Local')->formatDateTime($record['expire_date']);
    $images = array_filter(explode(',',$record['images']));
    $records[$key]['image_url'] = get_file_url_cdn(CLASSIFIEDS_THUMBNAILS_PATH.$record['id'].'-'.$images[0]);
    if(MOD_REWRITE) {
        $records[$key]['images_url'] = BASE_URL.'/classified/'.$record['friendly_url'].'-'.$record['id'].'/images.html';
    } else {
        $records[$key]['images_url'] = BASE_URL.'/classified_images.php?id='.$listing['id'].'&classified_id='.$record['id'];
    }
    $records[$key]['url'] = $PMDR->get('Classifieds')->getURL($record['id'],$record['friendly_url']);
}
$template_content->set('records',$records);
$paging->setTotalResults($db->FoundRows());

$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>