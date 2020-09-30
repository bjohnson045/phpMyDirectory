<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

// Load the language variables required on this page
$PMDR->loadLanguage();

// Get the page data from the database by the page ID
if(isset($_GET['id'])) {
    $page = $PMDR->get('CustomPage')->getRow($_GET['id']);
}

// If the page does not exist we return a 404 error
if(!$page OR !$page['active']) {
    $PMDR->get('Error',404);
}
// We want to ignore custom query parameters. Ensure the
// friendly_url_full is entirely included as the first part of the URL.
if(strpos(URL,$page['friendly_url_full']) !== 0) {
    $PMDR->get('Error',301);
    redirect($page['friendly_url_full']);
}

if($page['id'] == $PMDR->getConfig('browse_index_type')) {
    $PMDR->get('Error',301);
    redirect(BASE_URL);
}

// Set the content of the page to the template content
$template_content = $page['content_parsed'];

// Set the page meta details based on the custom page meta data
$PMDR->set('meta_description',$page['meta_description']);
$PMDR->set('meta_keywords',$page['meta_keywords']);

// Set the page title according to the page title data
if(!empty($page['meta_title'])) {
    $PMDR->set('meta_title',$page['meta_title']);
}
$PMDR->setAdd('page_title',$page['title']);

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>$page['friendly_url_full'],'text'=>$page['title']));

// Set the page templates
if(trim($page['header_template_file']) != '') {
    $PMDR->set('header_file',$page['header_template_file']);
}
if(trim($page['footer_template_file']) != '') {
    $PMDR->set('footer_file',$page['footer_template_file']);
}
if(trim($page['wrapper_template_file']) != '') {
    $PMDR->set('wrapper_file',$page['wrapper_template_file']);
}

include(PMDROOT.'/includes/template_setup.php');
?>