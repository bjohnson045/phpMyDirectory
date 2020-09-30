<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_password_reset','email_templates'));

$users = $PMDR->get('Users');

// If password verification code from email is set continue
if (isset($_GET['verify']) AND isset($_GET['id'])) {
    if($user = $users->getRow($_GET['id'])) {
        if($new_pass = $users->verifyPasswordResetCode($user,$_GET['verify'])) {
            $PMDR->get('Email_Templates')->send('admin_password_reset',array('to'=>$user['user_email'],'variables'=>array('admin_new_password'=>$new_pass),'user_id'=>$user['id']));
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_password_reset_processed'));
            redirect('index.php');
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_password_reset_failed'));
        }
     } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_password_reset_failed'));
     }
}

$form = $PMDR->get('Form');
$form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_password_reset_title')));
$form->addField('email','text',array('label'=>$PMDR->getLanguage('admin_password_reset_email'),'fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
$form->addValidator('email',new Validate_NonEmpty());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$db->GetRow("SELECT id FROM ".T_USERS." WHERE user_email=?",array($form->getFieldValue('email')))) {
        $form->addError($PMDR->getLanguage('admin_password_reset_invalid_email'));
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $user = $users->findByEmail($data['email']);
        if($user) {
            $users->setPasswordVerificationCode($user['id'], SECURITY_KEY);
            $PMDR->get('Email_Templates')->send('admin_password_reset_request',array('to'=>$user['user_email'],'variables'=>array('admin_password_reminder_url'=>BASE_URL_ADMIN."/admin_password_reset.php?id=$user[id]&verify=".md5(SECURITY_KEY.$user['user_email'])),'user_id'=>$user['id']));
        }
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_password_reset_request_sent'));

    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_password_reset_title'));
$template_content->set('content',$form->toHTML());

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>