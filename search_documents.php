<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_search_documents'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search_documents'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search.php','text'=>$PMDR->getLanguage('public_search_documents')));

$paging = $PMDR->get('Paging');
$paging->modRewrite = false;
$paging->setResultsNumber($PMDR->getConfig('count_search'));
$paging->linksNumber = 5;

$document_results = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS d.id, d.listing_id, d.title, d.description, d.extension, l.title AS listing_title, l.friendly_url
                                 FROM ".T_DOCUMENTS." d INNER JOIN ".T_LISTINGS." l ON d.listing_id=l.id
                                 WHERE (d.title LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%")." OR d.description LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%").") AND l.status='active'
                                 LIMIT ".$paging->limit1.",".$paging->limit2);

$paging->setTotalResults($document_count = $db->FoundRows());
$pageArray = $paging->getPageArray();

$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

foreach($document_results as $key=>$document) {
    $document_results[$key]['document_url'] = get_file_url(DOCUMENTS_PATH.$document['id'].'.'.$document['extension']);
    $document_results[$key]['url'] = $PMDR->get('Listings')->getURL($document['listing_id'],$document['friendly_url'],'','/documents.html','listing_documents.php');
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search_documents.tpl');
$template_content->set('document_results',$document_results);
$template_content->set('document_count',$document_count);
$template_content->set('page_navigation',$template_page_navigation);

include(PMDROOT.'/includes/template_setup.php');
?>