<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_email_log','admin_email_send','admin_email_templates','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_manager');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_page_menu[] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl'));

$template_content->set('title',$PMDR->getLanguage('admin_email_send'));

if($_GET['user_id']) {
    $emails = $db->GetAssoc("SELECT id, CONCAT(COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login),' &lt;',user_email,'&gt;') FROM ".T_USERS." WHERE id IN (".$PMDR->get('Cleaner')->clean_db($_GET['user_id'],false).")");
}

if(!isset($_GET['template'])) {
    $template_form = $PMDR->get('Form');
    $template_form->addFieldSet('Template',array('legend'=>$PMDR->getLanguage('admin_email_send_template')));
    $email_templates = array('new_email'=>$PMDR->getLanguage('admin_email_send_new_email'));
    if(!empty($_GET['user_id'])) {
        $email_templates = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type='user' AND id NOT LIKE 'admin_%'");
        foreach($email_templates AS $id) {
            $email_templates[$id] = $PMDR->getLanguage('email_templates_'.$id.'_name');
        }
    }
    $template_form->addField('template','select',array('options'=>$email_templates));
    $template_form->addField('submit','submit');

    if($template_form->wasSubmitted('submit')) {
        $data = $template_form->loadValues();
        if(!$template_form->validate()) {
            $PMDR->addMessage('error',$template_form->parseErrorsForTemplate());
        } else {
            redirect(rebuild_url(array('template'=>$data['template'])));
        }
    }
    $template_content->set('content',$template_form->toHTML());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('email',array('legend'=>'Email'));
    $form->enctype = 'multipart/form-data';
    $form->addField('from_name','text',array('value'=>$PMDR->getConfig('title')));
    $form->addField('from_address','text',array('value'=>$PMDR->getConfig('admin_email')));
    $form->addField('reply_name','text');
    $form->addField('reply_address','text');
    $form->addField('to','select_multiple',array('style'=>'width:400px','options'=>$emails,'disabled'=>'disabled'));
    $form->addField('recipients','textarea');
    $form->addField('subject','text');
    $form->addField('body_plain','textarea',array('style'=>'width: 600px','fullscreen'=>true));
    $form->addField('body_html','htmleditor');
    $form->addField('attachment','file',array('multiple'=>true));
    $form->addField('submit','submit');

    $form->addValidator('from_name',new Validate_NonEmpty());
    $form->addValidator('from_address',new Validate_Email());

    if($email_template = $PMDR->get('Email_Templates')->getRow($_GET['template'])) {
        if(empty($email_template['from_address'])) {
            $email_template['from_address'] = $PMDR->getConfig('email_from_address');
        }
        if(empty($email_template['from_name'])) {
            $email_template['from_name'] = $PMDR->getConfig('email_from_name');
        }
        $form->loadValues($email_template);
    }

    $template_page_menu[] = array('title'=>$PMDR->getLanguage('admin_email_templates_variables'),'content'=>$PMDR->get('Email_Templates')->getVariablesTemplate($_GET['template'],'user'));

    if($form->wasSubmitted('submit')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','Sending emails to users is disabled in the demo.');
        } else {
            $data = $form->loadValues();

            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                unset($data['to']);
                foreach($emails AS $user_id=>$email_text) {
                    $PMDR->get('Email_Templates')->send(NULL,array('user_id'=>$user_id,'template'=>$data,'attachment'=>$data['attachment']));
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_send_sent'));
                if($_GET['user_id'] AND count(explode(',',$_GET['user_id'])) == 1) {
                    redirect('admin_users_summary.php',array('id'=>$_GET['user_id']));
                } else {
                    redirect('admin_users.php');
                }
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>