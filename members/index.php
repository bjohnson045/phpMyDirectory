<?php
define('PMD_SECTION','members');

include ('../defaults.php');

if(ADDON_DISCOUNT_CODES) {
    $PMDR->get('Discount_Codes')->setURLCode();
}

$PMDR->get('Authentication')->authenticateIP();

$PMDR->loadLanguage(array('user_index'));

if(isset($_GET['user_login']) AND $PMDR->get('Authentication')->checkPermission('admin_login') AND $PMDR->get('Authentication')->checkPermission('admin_users_view')) {
    $PMDR->get('Authentication')->logout();
    if($PMDR->get('Authentication')->forceLogin($_GET['user_login'],$_GET['user_login_field'])) {
        redirect(BASE_URL.MEMBERS_FOLDER.'user_index.php');
    }
}

if(value($_GET,'action') == 'unsubscribe' AND isset($_GET['id']) AND isset($_GET['token'])) {
    if($PMDR->get('Users')->unsubscribeAll($_GET['id'],$_GET['token'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('user_index_unsubscribe_success'));
    } else {
        $PMDR->addMessage('success',$PMDR->getLanguage('user_index_unsubscribe_failed'));
    }
    redirect();
}

if($PMDR->get('Session')->get('user_id')) {
    redirect('user_index.php');
}

$PMDR->loadLanguage();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_general_my_account'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/index.tpl');

if(isset($_GET['from'])) {
    $template_content->set('create_account_url', BASE_URL.MEMBERS_FOLDER.'user_account_add.php?from='.urlencode_url($_GET['from']));
} else {
    $template_content->set('create_account_url', BASE_URL.MEMBERS_FOLDER.'user_account_add.php');
}

if($PMDR->getConfig('login_module_password_reminder_url')) {
    $template_content->set('password_reminder_url',$PMDR->getConfig('login_module_password_reminder_url'));
} else {
    $template_content->set('password_reminder_url',BASE_URL.MEMBERS_FOLDER.'user_password_remind.php');
}

$form = $PMDR->getNew('Form');
$form->setName('login_form');
$form->addFieldSet('login_form',array('legend'=>$PMDR->getLanguage('user_index_login')));
$form->addField('user_login','text',array('label'=>$PMDR->getLanguage('user_index_username'),'fieldset'=>'login_form','value'=>$_GET['user_login']));
$form->addField('user_pass','password',array('label'=>$PMDR->getLanguage('user_index_password'),'fieldset'=>'login_form','value'=>$_GET['user_pass']));
$form->addField('remember','checkbox',array('label'=>'','fieldset'=>'login_form','html'=>$PMDR->getLanguage('user_index_keep_signed_in')));
$form->addField('submit_login','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'button'));

$form->addValidator('user_login',new Validate_NonEmpty());
$form->addValidator('user_pass',new Validate_NonEmpty());

if($form->wasSubmitted('submit_login')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $PMDR->get('Authentication')->authenticate();
        redirect(BASE_URL.MEMBERS_FOLDER.'user_index.php');
    }
}

$template_content->set('form',$form);

if($PMDR->get('Authentication_'.$PMDR->getConfig('login_module'))->remote == true) {
    $template_content->set('remote_login',true);
    $PMDR->get('Authentication_'.$PMDR->getConfig('login_module'))->loadJavascript();
}

include(PMDROOT.'/includes/template_setup.php');
?>