<?php
define('PMD_SECTION', 'public');

include('./defaults.php' );

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_events'));

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_events_events_search'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('events_search_meta_title'),$PMDR->getLanguage('public_events_events_search')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('events_search_meta_title'),$PMDR->getLanguage('public_events_events_search')));

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/events_search.php','text'=>$PMDR->getLanguage('public_events_events_search')));

// Initialize paging object and default settings
$paging = $PMDR->get('Paging');
$paging->linksNumber = 10;
$paging->setResultsNumber($PMDR->getConfig('count_directory'));
$paging->modRewrite = false;

// Load the template used for this page
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'events_search.tpl');

$where = '';
$where_parts = array();
$where_parts[] = 'ed.date_end > NOW()';
if(isset($_GET['keyword']) AND !empty($_GET['keyword'])) {
    $where_parts[] = "MATCH(e.title,e.description_short,e.keywords) AGAINST (".$PMDR->get('Cleaner')->clean_db($_GET['keyword']).")";
}
if(isset($_GET['location']) AND !empty($_GET['location'])) {
    $where_parts[] = "MATCH(e.location) AGAINST (".$PMDR->get('Cleaner')->clean_db($_GET['location']).")";
}
$category_join = '';
if(isset($_GET['category_id']) AND !empty($_GET['category_id'])) {
    $category_join = "INNER JOIN ".T_EVENTS_CATEGORIES_LOOKUP." cl ON e.id=cl.event_id";
    $where_parts[] = "cl.category_id=".$PMDR->get('Cleaner')->clean_db($_GET['category_id']);
}
$where = 'WHERE '.implode(' AND ',$where_parts);
$events = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS e.*, ed.date_start AS date_start_instance, ed.date_end AS date_end_instance FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id $category_join $where ORDER BY ed.date_start ASC LIMIT ?,?",array(intval($paging->limit1),intval($paging->limit2)));
$events_count = $db->FoundRows();
$paging->setTotalResults($events_count);

unset($_GET['submit_search']);

// Set up the paging template
$pageArray = $paging->getPageArray();
$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

// Get the listing results template
$template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/events_results.tpl');
$template_results->set('page',$pageArray);

if($events) {
    $events_results = '';
    foreach($events AS $event) {
        $event['date'] = $PMDR->get('Dates_Local')->formatDateTime($event['date']);
        $event['date_start'] = $PMDR->get('Dates_Local')->formatDateTime($event['date_start_instance']);
        $event['date_end'] = $PMDR->get('Dates_Local')->formatDateTime($event['date_end_instance']);
        $event['url'] = $PMDR->get('Events')->getURL($event['id'],$event['friendly_url']);
        $events_results_template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/events_results_default.tpl');
        $events_results_template->set('event',$event);
        $events_results .= $events_results_template->render();
    }
    $template_results->set('events_results',$events_results);
    $template_results->set('page_navigation',$template_page_navigation);
} else {
    $template_content->set('error_message',$PMDR->getLanguage('public_search_users_no_results'));
}

$form = $PMDR->getNew('Form');
$form->method = 'GET';
$form->addField('keyword','text',array('label'=>'keyword','placeholder'=>$PMDR->getLanguage('public_events_keyword'),'value'=>$_GET['keyword']));
$form->addField('location','text',array('label'=>'location','placeholder'=>$PMDR->getLanguage('public_events_location'),'value'=>$_GET['location']));
$categories = $db->GetAssoc("SELECT id, title FROM ".T_EVENTS_CATEGORIES." ORDER BY title");
$form->addField('category_id','select',array('first_option'=>array(''=>$PMDR->getLanguage('public_events_category')),'options'=>$categories,'label'=>'category','placeholder'=>$PMDR->getLanguage('public_events_category'),'value'=>$_GET['category_id']));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('search')));
$template_content->set('form',$form);

// Send remaining details to the template to control the data display
$template_content->set('events_results',$template_results);
$template_content->set('events_count', $events_count);

include(PMDROOT.'/includes/template_setup.php');
?>