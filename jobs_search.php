<?php
define('PMD_SECTION', 'public');

include('./defaults.php' );

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_jobs'));

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_jobs_search'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('jobs_search_meta_title'),$PMDR->getLanguage('public_jobs_search')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('jobs_search_meta_description'),$PMDR->getLanguage('public_jobs_search')));

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/jobs_search.php','text'=>$PMDR->getLanguage('public_jobs_search')));

// Initialize paging object and default settings
$paging = $PMDR->get('Paging');
$paging->linksNumber = 20;
$paging->setResultsNumber($PMDR->getConfig('count_directory'));
$paging->modRewrite = false;

// Load the template used for this page
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'jobs_search.tpl');

$where = '';
$where_parts = array();
$where_parts[] = "status='active'";
if(isset($_GET['keyword']) AND !empty($_GET['keyword'])) {
    $where_parts[] = "MATCH(j.title,j.description_short,j.keywords) AGAINST (".$PMDR->get('Cleaner')->clean_db($_GET['keyword']).")";
}
$category_join = '';
if(isset($_GET['category_id']) AND !empty($_GET['category_id'])) {
    $category_join = "INNER JOIN ".T_JOBS_CATEGORIES_LOOKUP." cl ON j.id=cl.job_id";
    $where_parts[] = "cl.category_id=".$PMDR->get('Cleaner')->clean_db($_GET['category_id']);
}
if(count($where_parts)) {
    $where = 'WHERE '.implode(' AND ',$where_parts);
}
$jobs = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS j.* FROM ".T_JOBS." j $category_join $where ORDER BY j.date DESC LIMIT ?,?",array(intval($paging->limit1),intval($paging->limit2)));
$jobs_count = $db->FoundRows();
$paging->setTotalResults($jobs_count);

unset($_GET['submit_search']);

// Set up the paging template
$pageArray = $paging->getPageArray();
$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

// Get the listing results template
$template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/jobs_results.tpl');
$template_results->set('page',$pageArray);

if($jobs) {
    $jobs_results = '';
    foreach($jobs AS $job) {
        $job['date'] = $PMDR->get('Dates_Local')->formatDateTime($job['date']);
        $job['date_updated'] = $PMDR->get('Dates_Local')->formatDateTime($job['date_updated']);
        $job['url'] = $PMDR->get('Jobs')->getURL($job['id'],$job['friendly_url']);
        $jobs_results_template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/jobs_results_default.tpl');
        $jobs_results_template->set('job',$job);
        $jobs_results .= $jobs_results_template->render();
    }
    $template_results->set('jobs_results',$jobs_results);
    $template_results->set('page_navigation',$template_page_navigation);
} else {
    $template_content->set('error_message',$PMDR->getLanguage('public_search_users_no_results'));
}

$form = $PMDR->getNew('Form');
$form->method = 'GET';
$form->addField('keyword','text',array('label'=>'keyword','placeholder'=>$PMDR->getLanguage('public_jobs_keyword'),'value'=>$_GET['keyword']));
$categories = $db->GetAssoc("SELECT id, title FROM ".T_JOBS_CATEGORIES." ORDER BY title");
$form->addField('category_id','select',array('first_option'=>array(''=>$PMDR->getLanguage('public_jobs_category')),'options'=>$categories,'label'=>'category','placeholder'=>$PMDR->getLanguage('public_jobs_category'),'value'=>$_GET['category_id']));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('search')));
$template_content->set('form',$form);

// Send remaining details to the template to control the data display
$template_content->set('jobs_results',$template_results);
$template_content->set('jobs_count', $jobs_count);

include(PMDROOT.'/includes/template_setup.php');
?>