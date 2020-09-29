<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

if($PMDR->get('Session')->get('admin_id')) {
    redirect(BASE_URL_ADMIN.'/admin_index.php');
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_login.tpl');

$PMDR->loadLanguage(array('admin_login'));

$form = $PMDR->get('Form');
$form->setName('login_form');
$form->addFieldSet('login_form',array('legend'=>$PMDR->getLanguage('admin_login')));
$form->addField('admin_login','text',array('label'=>$PMDR->getLanguage('admin_login_email'),'fieldset'=>'login_form','value'=>value($_GET,'admin_login')));
$form->addField('admin_pass','password',array('label'=>$PMDR->getLanguage('admin_login_password'),'fieldset'=>'login_form','value'=>value($_GET,'admin_pass')));
$form->addField('remember','checkbox',array('label'=>'&nbsp;','fieldset'=>'login_form','value'=>0,'options'=>array(1=>$PMDR->getLanguage('admin_login_remember_me'))));
$form->addField('submit_login','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$form->addValidator('admin_login',new Validate_NonEmpty());
$form->addValidator('admin_pass',new Validate_NonEmpty());

$template_content->set('form',$form);

if($form->wasSubmitted('submit_login')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $PMDR->get('Authentication')->authenticate();
        redirect(BASE_URL_ADMIN.'/admin_index.php');
    }
} else {
    if (file_exists(PMDROOT.'/install/index.php') ) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_login_install_warning'));
    }
    if (substr(sprintf('%o',fileperms(PMDROOT.'/defaults.php')),-3) == '777') {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_login_config_writable'));
    }
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>