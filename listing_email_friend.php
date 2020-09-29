<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_listing_email_friend','email_templates'));

$PMDR->get('Authentication')->authenticateIP();

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['email_friend_allow']) {
    $PMDR->get('Error',404);
}

if(LOGGED_IN) {
    $user = $db->GetRow("SELECT user_email, user_first_name, user_last_name FROM ".T_USERS." WHERE id=?",array($_SESSION['user_id']));
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email_friend'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email_friend'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email_friend'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_email_friend'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_email_friend'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_email_friend')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/listing_email_friend.tpl');

$form = $PMDR->getNew('Form');
$form->addField('from_name','text',array('label'=>$PMDR->getLanguage('public_listing_email_friend_from_name')));
$form->addField('from_email','text',array('label'=>$PMDR->getLanguage('public_listing_email_friend_from_email')));
if(LOGGED_IN) {
    $form->setFieldAttribute('from_name','value',trim($user['user_first_name'].' '.$user['user_last_name']));
    $form->setFieldAttribute('from_email','value',$user['user_email']);
}
$form->addField('email','text',array('label'=>$PMDR->getLanguage('public_listing_email_friend_to_email')));
$form->addField('message','textarea',array('label'=>$PMDR->getLanguage('public_listing_email_friend_message'),'counter'=>$PMDR->getConfig('send_message_size')));
$added_fields = $PMDR->get('Fields')->addToForm($form,'send_message_friend',array('filter'=>$listing));
if($PMDR->getConfig('GD_security_send_message') AND (!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in'))) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_listing_email_friend_security_code')));
    $form->addValidator('security_code',new Validate_Captcha());
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));

$form->addValidator('from_name',new Validate_NonEmpty());
$form->addValidator('from_email',new Validate_Email(true));
$form->addValidator('email',new Validate_Email(true));
$form->addValidator('message',new Validate_NonEmpty());
$form->addValidator('message',new Validate_Banned_Words());
$form->addValidator('message',new Validate_Banned_URL());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['message'] = Strings::limit_characters($data['message'],$PMDR->getConfig('send_message_size'));

        $PMDR->get('Email_Templates')->send('listings_send_email_friend',array('to'=>$data['email'],'variables'=>$data,'listing_id'=>$listing['id']));
        $PMDR->get('Email_Templates')->send('admin_listings_send_email_friend',array('variables'=>$data,'listing_id'=>$listing['id']));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_email_friend_sent'));
        redirect($PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
    }
}

$template_content->set('form',$form);
$template_content->set('custom_fields',$added_fields);
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>