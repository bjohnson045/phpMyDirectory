<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_email_test');

$form = $PMDR->get('Form');
$form->addFieldSet('email_test',array('legend'=>$PMDR->getLanguage('admin_maintenance_email_test')));

if(isset($_GET['connection'])) {
    $_POST['connection'] = $_GET['connection'];
}

if(!isset($_POST['connection'])) {
    $form->addField('connection','radio',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_connection'),'fieldset'=>'email_test','options'=>array('mail'=>'PHP mail() function','smtp'=>'SMTP','sendmail'=>'sendmail')));
    $form->addField('submit_connection','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
} else {
    $form->addField('email','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_email'),'fieldset'=>'email_test','help'=>$PMDR->getLanguage('admin_maintenance_email_test_email_help')));
    switch($_POST['connection']) {
        case 'mail':
            break;
        case 'smtp':
            $form->addField('smtp_host','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_host'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_smtp_host')));
            $form->addField('smtp_user','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_user'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_smtp_user')));
            $form->addField('smtp_pass','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_pass'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_smtp_pass')));
            $form->addField('smtp_port','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_port'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_smtp_port')));
            $form->addField('smtp_require_auth','checkbox',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_require_auth'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_smtp_require_auth')));
            $form->addField('smtp_encryption','select',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_encryption'),'fieldset'=>'email_test','options'=>array('none'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_none'),'ssl'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_ssl'),'tls'=>$PMDR->getLanguage('admin_maintenance_email_test_smtp_tls')),'value'=>$PMDR->getConfig('email_smtp_encryption')));
            break;
        case 'sendmail':
            $form->addField('sendmail_path','text',array('label'=>$PMDR->getLanguage('admin_maintenance_email_test_sendmail_path'),'fieldset'=>'email_test','value'=>$PMDR->getConfig('email_sendmail_path')));
            break;
    }
    $form->addField('connection','hidden',array('fieldset'=>'email_test','value'=>$_POST['connection']));
    $form->addValidator('email',new Validate_Email(true));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Email testing has been disabled for the demo.');
    } elseif(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $PMDR->config['email_preferred_connection'] = $data['connection'];

        switch($data['connection']) {
            case 'smtp':
                $PMDR->config['email_smtp_host'] = $data['smtp_host'];
                $PMDR->config['email_smtp_require_auth'] = $data['smtp_require_auth'];
                $PMDR->config['email_smtp_user'] = $data['smtp_user'];
                $PMDR->config['email_smtp_pass'] = $data['smtp_pass'];
                $PMDR->config['email_smtp_port'] = $data['smtp_port'];
                $PMDR->config['email_smtp_encryption'] = $data['smtp_encryption'];
                break;
            case 'sendmail':
                $PMDR->config['email_sendmail_path'] = $data['sendmail_path'];
                break;
        }

        $mailer = $PMDR->get('Email_Handler',array('force_connection'=>true));
        $mailer->flush();
        $mailer->addRecipient($data['email']);
        $mailer->from_email = $PMDR->getConfig('admin_email');
        $mailer->from_name = 'Email Tester';
        $mailer->subject = 'Email Tester';
        $mailer->addMessagePart('Email Test Successful');
        $mailer->addMessagePart('Email Test Successful','text/html');
        if(($sent = $mailer->send()) > 0) {
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_maintenance_email_test_success'));
        } else {
            if($sent == 0 AND $data['connection'] == 'smtp') {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_maintenance_email_test_smtp_error'));
            } else {
                $PMDR->addMessage('error',$mailer->error);
            }
        }
        redirect();
    }
}

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_email_test'));
$template_content->set('content',$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>