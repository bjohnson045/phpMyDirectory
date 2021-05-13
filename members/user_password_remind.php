<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_account','user_password_remind','email_templates'));

$users = $PMDR->get('Users');

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_password_remind'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_password_remind.php','text'=>$PMDR->getLanguage('user_password_remind')));

// If password verification code from email is set continue
if(isset($_GET['verify']) AND isset($_GET['id'])) {
    if($user = $users->getRow($_GET['id'])) {
        if($new_pass = $users->verifyPasswordResetCode($user,$_GET['verify'])) {
            $PMDR->get('Email_Templates')->send('password_reset',array('to'=>$user['user_email'],'variables'=>array('user_new_password'=>$new_pass),'user_id'=>$user['id']));
            $PMDR->addMessage('success',$PMDR->getLanguage('user_password_remind_reset_processed'));
            redirect('index.php');
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('user_password_remind_reset_failed'));
        }
     } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('user_password_remind_reset_failed'));
     }
}

$form = $PMDR->getNew('Form');
$form->addField('login','text',array('label'=>$PMDR->getLanguage('user_password_remind_email_username'),'fieldset'=>'input_default'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit')));

$form->addValidator('login',new Validate_NonEmpty());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $user = $users->getRow(array('user_email'=>$data['login']));
        if(!$user) {
            $user = $users->getRow(array('login'=>$data['login']));
        }
        if($user) {
            if($db->GetOne("SELECT group_id FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id=?",array($user['id'])) == 5) {
                $PMDR->addMessage('success',$PMDR->getLanguage('user_account_email_confirm'));
                $PMDR->get('Email_Templates')->send('user_registration',array('to'=>$user['user_email'],'user_id'=>$user['id']));
            } else {
                $users->setPasswordVerificationCode($user['id'], LICENSE);
                $PMDR->get('Email_Templates')->send('password_reset_request',array('to'=>$user['user_email'],'variables'=>array('user_password_reminder_url'=>BASE_URL.MEMBERS_FOLDER."user_password_remind.php?id=$user[id]&verify=".md5($user['user_email'])),'user_id'=>$user['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('user_password_remind_request_sent'));
            }
            redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('user_password_remind_invalid'));
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_password_remind.tpl');
$template_content->set('form',$form);

include(PMDROOT.'/includes/template_setup.php');
?>