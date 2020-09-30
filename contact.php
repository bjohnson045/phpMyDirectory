<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_contact','email_templates'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_contact'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('contact_meta_title'),$PMDR->getLanguage('public_contact')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('contact_meta_description'),$PMDR->getLanguage('public_contact')));

$PMDR->set('canonical_url',BASE_URL.'/contact.php');

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/contact.php','text'=>$PMDR->getLanguage('public_contact')));

if(LOGGED_IN) {
    $user = $db->GetRow("SELECT user_email, user_first_name, user_last_name FROM ".T_USERS." WHERE id=?",array($_SESSION['user_id']));
}

/** @var Fields */
$fields = $PMDR->get('Fields');

$form = $PMDR->getNew('Form');
$form->addFieldSet('contact_us',array('legend'=>$PMDR->getLanguage('public_contact')));
$form->addField('name','text',array('label'=>$PMDR->getLanguage('public_contact_name'),'fieldset'=>'contact_us'));
$form->addField('email','text',array('label'=>$PMDR->getLanguage('public_contact_email'),'fieldset'=>'contact_us'));
$form->addField('confirm_email','text',array('label'=>$PMDR->getLanguage('public_contact_email_confirm'),'fieldset'=>'contact_us'));
if(LOGGED_IN) {
    $form->setFieldAttribute('name','value',trim($user['user_first_name'].' '.$user['user_last_name']));
    $form->setFieldAttribute('email','value',$user['user_email']);
    $form->setFieldAttribute('confirm_email','value',$user['user_email']);
}
$form->addField('comments','textarea',array('label'=>$PMDR->getLanguage('public_contact_comments'),'fieldset'=>'contact_us'));
$custom_fields = $fields->addToForm($form,'contact',array('fieldset'=>'contact_us'));
if(!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in')) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_contact_security_code'),'fieldset'=>'contact_us'));
    $form->addValidator('security_code',new Validate_Captcha());
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));

$form->addValidator('name',new Validate_NonEmpty());
$form->addValidator('email',new Validate_Email());
$form->addValidator('confirm_email',new Validate_Email());
$form->addValidator('comments',new Validate_NonEmpty());

if(isset($_GET['id'])) {
    $abuse_listing = $db->GetRow("SELECT id, title, friendly_url FROM ".T_LISTINGS." WHERE id=?",array($_GET['id']));
    $abuse_listing['url'] = $PMDR->get('Listings')->getURL($abuse_listing['id'],$abuse_listing['friendly_url']);
    $form->setFieldAttribute('comments','value',$PMDR->getLanguage('public_contact_abuse',array($abuse_listing['title'],$abuse_listing['id']))."\n".$abuse_listing['url']);
}

$PMDR->get('Plugins')->run_hook('contact_form');

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    $PMDR->get('Plugins')->run_hook('contact_submit');

    if($form->getFieldValue('email') != $form->getFieldValue('confirm_email')) {
        $form->addError($PMDR->getLanguage('public_contact_email_nonmatch'));
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['ip_address'] = get_ip_address();

        foreach($custom_fields as $field) {
            $data['custom_'.$field['id']] = implode("\n", (array)$data['custom_'.$field['id']]);
        }

        $PMDR->get('Plugins')->run_hook('contact_submit_success');

        $PMDR->get('Email_Templates')->send('admin_contact_submission',array('variables'=>$data));
        $PMDR->get('Email_Templates')->send('contact_response',array('to'=>$data['email'],'variables'=>$data));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_contact_submitted'),'insert');
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'contact.tpl');
$template_content->set('abuse_listing_url',(isset($abuse_listing) ? value($abuse_listing,'url') : false));
$template_content->set('form',$form);
$template_content->set('custom_fields',$custom_fields);

include(PMDROOT.'/includes/template_setup.php');
?>