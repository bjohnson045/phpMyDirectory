<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_events'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_events_events_list'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/events_list.tpl');

$paging = $PMDR->get('Paging');
$paging->setResultsNumber(20);
$events_list = $PMDR->get('Events')->getFuture($paging->limit1,$paging->limit2);
$paging->setTotalResults($db->FoundRows());
$page_array = $paging->getPageArray();
$template_page_navigation = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$page_array);
$template_content->set('page_navigation',$template_page_navigation);

$template_content->set('events_list',$events_list);

include(PMDROOT.'/includes/template_setup.php');
?>