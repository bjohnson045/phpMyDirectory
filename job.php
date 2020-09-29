<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_jobs'));

// Get job from database
$job = $db->GetRow("SELECT j.*, l.title AS listing_title, l.friendly_url AS listing_friendly_url FROM ".T_JOBS." j LEFT JOIN ".T_LISTINGS." l ON j.listing_id=l.id WHERE j.id=?",array($_GET['id']));

// Generate the current job URL
$job['url'] = $PMDR->get('Jobs')->getURL($job['id'],$job['friendly_url']);

// If the job URL does not match the current URL, give a 404 error
if(!$job OR $job['url'] != URL_NOQUERY OR ($job['status'] != 'active' AND $job['user_id'] != $PMDR->get('Session')->get('user_id') AND @!in_array('admin_login',$_SESSION['admin_permissions']))) {
    $PMDR->get('Error',404);
}

$PMDR->set('page_header',null);

// Load the jobs template file
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'job.tpl');

$template_content->set('share',$PMDR->get('Sharing')->getHTML());

$title = coalesce($PMDR->getConfig('title_job_default'),$job['title']);
$meta_title = coalesce($job['meta_title'],$PMDR->getConfig('meta_title_job_default'),$job['title']);
$meta_description = coalesce($job['meta_description'],$PMDR->getConfig('meta_description_job_default'),$job['description'],$PMDR->getConfig('meta_description_default').' '.$job['title']);
$meta_keywords = coalesce($job['meta_keywords'],$PMDR->getConfig('meta_keywords_job_default'),$job['keywords'],$PMDR->getConfig('meta_keywords_default').' '.$job['title']);

if(!is_null($job['listing_id'])) {
    $meta_replace = array('listing_title'=>$job['listing_title']);
    foreach($meta_replace AS $find=>$replace) {
        $title = str_replace('*'.$find.'*',$replace,$title);
        $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
        $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
        $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
    }
}
$job_fields = $PMDR->get('Fields')->getFields('jobs');
foreach($job_fields as $key=>$field) {
    $title = str_replace('*custom_'.$field['id'].'*',$job['custom_'.$field['id']],$title);
    $meta_title = str_replace('*custom_'.$field['id'].'*',$job['custom_'.$field['id']],$meta_title);
    $meta_description = str_replace('*custom_'.$field['id'].'*',$job['custom_'.$field['id']],$meta_description);
    $meta_keywords = str_replace('*custom_'.$field['id'].'*',$job['custom_'.$field['id']],$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

// If the job belongs to a listing load the listing specific data
if(!is_null($job['listing_id'])) {
    // If the listing has a custom header template file defined and that file exists, load it
    if(trim($job['listing_header_template_file']) != '') {
        $PMDR->set('header_file',$job['listing_header_template_file']);
    }
    // If the listing has a custom footer template file defined and that file exists, load it
    if(trim($job['listing_footer_template_file']) != '') {
        $PMDR->set('footer_file',$job['listing_footer_template_file']);
    }
    // If the listing has a custom wrapper template file defined and that file exists, load it
    if(trim($job['listing_wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$job['listing_wrapper_template_file']);
    }

    // Generate the listing URL and set in the template file
    $template_content->set('listing_url',$PMDR->get('Listings')->getURL($job['listing_id'],$job['listing_friendly_url']));
    // Set the listing title in the template file
    $template_content->set('listing_title',$job['listing_title']);

    // Look for other jobs from this same listing.
    if($other_jobs = $db->GetAll("SELECT id, title, friendly_url FROM ".T_JOBS." WHERE id!=? AND listing_id=? AND status='active' LIMIT 10",array($job['id'],$job['listing_id']))) {
        // Generate the URLs for each of the found jobs
        foreach($other_jobs  AS $key=>$other_job) {
            $other_jobs [$key]['url'] = $PMDR->get('Jobs')->getURL($other_job['id'],$other_job['friendly_url']);
        }
        unset($other_job,$key);
        $template_content->set('other_jobs',$other_jobs );
    }
}

// Set the breakcrump link text and URLs
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('public_jobs')));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$job['title']));

// Set job variables in the template
$template_content->set('id',$job['id']);
$template_content->set('url',$job['url']);
$template_content->set('title',$job['title']);
$template_content->set('date',$PMDR->get('Dates_Local')->formatDateTime($job['date']));
$template_content->set('date_update',$PMDR->get('Dates_Local')->formatDateTime($job['date_update']));
$template_content->set('description',nl2br($PMDR->get('Cleaner')->unclean_html($job['description'])));
$template_content->set('description_short',nl2br($PMDR->get('Cleaner')->unclean_html($job['description_short'])));
$template_content->set('website',$job['website']);
$template_content->set('phone',$job['phone']);
$template_content->set('email',$job['email']);
$template_content->set('requirements',nl2br($PMDR->get('Cleaner')->unclean_html($job['requirements'])));
$template_content->set('compensation',$job['compensation']);
$template_content->set('benefits',$job['benefits']);
$template_content->set('type',$job['type']);
$template_content->set('keywords',$job['keywords']);
$template_content->set('contact_name',$job['contact_name']);

$PMDR->get('Fields_Groups')->addToTemplate($template_content,$job,'jobs');

include(PMDROOT.'/includes/template_setup.php');
?>