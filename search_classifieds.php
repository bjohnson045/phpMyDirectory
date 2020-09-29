<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_search_classifieds'));

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search_classifieds'));

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search.php','text'=>$PMDR->getLanguage('public_search_classifieds')));

// Initialize the paging object to show page links
$paging = $PMDR->get('Paging');
$paging->modRewrite = false;
$paging->setResultsNumber($PMDR->getConfig('count_search'));
$paging->linksNumber = 5;

// Get the classified results
$classified_results = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS c.id, c.listing_id, c.title, c.description, c.friendly_url, l.title AS listing_title, l.friendly_url AS listing_friendly_url
                                 FROM ".T_CLASSIFIEDS." c INNER JOIN ".T_LISTINGS." l ON c.listing_id=l.id
                                 WHERE (c.title LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%")." OR c.description LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%").") AND (c.expire_date > NOW() OR c.expire_date IS NULL) AND l.status='active'
                                 ORDER BY c.title LIMIT ".$paging->limit1.",".$paging->limit2);

$paging->setTotalResults($classified_count = $db->FoundRows());
$pageArray = $paging->getPageArray();
$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

// For all classifieds set the image url and url
foreach($classified_results as $key=>$classified) {
    $classified_results[$key]['image_url'] = get_file_url_cdn(CLASSIFIEDS_THUMBNAILS_PATH.$classified['id'].'-*');
    $classified_results[$key]['url'] = $PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']);
    if(!is_null($classified['listing_id'])) {
        $classified_results[$key]['listing_url'] = $PMDR->get('Listings')->getURL($classified['listing_id'],$classified['listing_friendly_url'],'','/classifieds.html','listing_classifieds.php');
    }
}

// Get the search_classifieds.tpl template file and set template data to control display
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search_classifieds.tpl');
$template_content->set('classified_results',$classified_results);
$template_content->set('classified_count',$classified_count);
$template_content->set('page_navigation',$template_page_navigation);

include(PMDROOT.'/includes/template_setup.php');
?>