<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_listing_email','email_templates'));

$PMDR->get('Authentication')->authenticateIP();

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['email_allow']) {
    $PMDR->get('Error',404);
}

if(LOGGED_IN) {
    $user = $db->GetRow("SELECT user_email, user_first_name, user_last_name FROM ".T_USERS." WHERE id=?",array($_SESSION['user_id']));
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_email'),'listing_title'=>$listing['title']);
foreach($meta_replace AS $find=>$replace) {
    $title = str_replace('*'.$find.'*',$replace,$title);
    $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
    $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
    $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),'text'=>$listing['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_email')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/listing_email.tpl');

$form = $PMDR->getNew('Form');
$form->enctype = 'multipart/form-data';
$form->addField('from_name','text',array('label'=>$PMDR->getLanguage('public_listing_email_from_name'),'fieldset'=>'input_default'));
$form->addField('from_email','text',array('label'=>$PMDR->getLanguage('public_listing_email_from_email'),'fieldset'=>'input_default'));
if(LOGGED_IN) {
    $form->setFieldAttribute('from_name','value',trim($user['user_first_name'].' '.$user['user_last_name']));
    $form->setFieldAttribute('from_email','value',$user['user_email']);
}
$form->addField('message','textarea',array('label'=>$PMDR->getLanguage('public_listing_email_message'),'fieldset'=>'input_default','counter'=>$PMDR->getConfig('send_message_size')));
$added_fields = $PMDR->get('Fields')->addToForm($form,'send_message',array('fieldset'=>'input_default','filter'=>$listing));
if($PMDR->getConfig('email_attach_size')) {
    $form->addField('attachment','file',array('label'=>$PMDR->getLanguage('public_listing_email_attachment'),'fieldset'=>'input_default'));
    $template_content->set('email_attach_size',$PMDR->getConfig('email_attach_size'));
}
$form->addField('copy','checkbox',array('label'=>'','fieldset'=>'input_default','html'=>$PMDR->getLanguage('public_listing_email_copy')));
if($PMDR->getConfig('GD_security_send_message') AND (!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in'))) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_listing_email_security_code'),'fieldset'=>'input_default'));
    $form->addValidator('security_code',new Validate_Captcha());
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));

$form->addValidator('from_name',new Validate_NonEmpty());
$form->addValidator('from_email',new Validate_Email(true));
$form->addValidator('message',new Validate_NonEmpty());
$form->addValidator('message',new Validate_Banned_Words());
$form->addValidator('message',new Validate_Banned_URL());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if($PMDR->getConfig('email_attach_size') AND !empty($data['attachment']) AND $data['attachment']['tmp_name'] != '') {
        if(filesize($data['attachment']['tmp_name']) / 1024 > $PMDR->getConfig('email_attach_size')) {
            $form->addError($PMDR->getLanguage('public_listing_email_attachment_error'),'attachment');
        }
    }
    if($PMDR->get('IP_Limits')->isOverHourLimit('listing_email',$PMDR->getConfig('listing_email_ip_limit'),$PMDR->getConfig('listing_email_ip_limit_hours'))) {
        $form->addError($PMDR->getLanguage('public_listing_email_ip_limit_error',array($PMDR->getConfig('listing_email_ip_limit'),$PMDR->getConfig('listing_email_ip_limit_hours'))));
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['message'] = Strings::limit_characters($data['message'],$PMDR->getConfig('send_message_size'));

        $PMDR->get('Email_Templates')->send('listings_send_email',array('to'=>$listing['mail'],'variables'=>$data,'attachment'=>$data['attachment'],'listing_id'=>$listing['id']));
        $PMDR->get('Email_Templates')->send('admin_listings_send_email',array('variables'=>$data,'attachment'=>$data['attachment'],'listing_id'=>$listing['id']));

        $db->Execute("UPDATE ".T_LISTINGS." SET emails = emails+1 WHERE id=?",array($listing['id']));

        if($data['copy']) {
            $PMDR->get('Email_Templates')->send('listings_send_email_copy',array('to'=>$data['from_email'],'variables'=>$data,'attachment'=>$data['attachment'],'listing_id'=>$listing['id']));
        }

        $PMDR->get('IP_Limits')->insert(array('type'=>'listing_email'));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_email_sent'));
        redirect($PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
    }
}

$template_content->set('form',$form);
$template_content->set('custom_fields',$added_fields);
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>