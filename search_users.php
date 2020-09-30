<?php
define('PMD_SECTION', 'public');

include('./defaults.php' );

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_search_users'));

if(!$PMDR->getConfig('user_search')) {
    $PMDR->addMessage('error',$PMDR->getLanguage('public_search_users_disabled'));
    redirect_url(BASE_URL);
}

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search_users'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('search_users_meta_title'),$PMDR->getLanguage('public_search_users')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('search_users_meta_description'),$PMDR->getLanguage('public_search_users')));

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search_users.php','text'=>$PMDR->getLanguage('public_search_users')));

// Initialize paging object and default settings
$paging = $PMDR->get('Paging');
$paging->linksNumber = 5;
$paging->setResultsNumber($PMDR->getConfig('count_directory'));
$paging->modRewrite = false;

// Load the template used for this page
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search_users_results.tpl');

$users = $PMDR->get('Users')->search($_GET['keyword'],$paging->limit1,$paging->limit2);
$users_count = $db->FoundRows();
$paging->setTotalResults($users_count);

unset($_GET['submit_search']);

// Set up the paging template
$pageArray = $paging->getPageArray();
$template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
$template_page_navigation->set('page',$pageArray);

// Get the listing results template
$template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/user_results.tpl');
$template_results->set('page',$pageArray);

if($users) {
    $user_results = '';
    foreach($users AS $user) {
        $user['profile_image'] = $PMDR->get('Users')->getProfileImage($user['id'],$user['email_address']);
        $user_results_template = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/user_results_default.tpl');
        $user_results_template->set('user',$user);
        $user_results .= $user_results_template->render();
    }
    $template_results->set('user_results',$user_results);
    $template_results->set('page_navigation',$template_page_navigation);
} else {
    $template_content->set('error_message',$PMDR->getLanguage('public_search_users_no_results'));
}

$form = $PMDR->getNew('Form');
$form->method = 'GET';
$form->addField('keyword','text',array('label'=>'keyword','placeholder'=>$PMDR->getLanguage('public_search_users_keyword'),'value'=>$_GET['keyword']));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('search')));
$template_content->set('form',$form);

// Send remaining details to the template to control the data display
$template_content->set('user_results',$template_results);
$template_content->set('user_count', $users_count);

include(PMDROOT.'/includes/template_setup.php');
?>