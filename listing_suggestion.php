<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_listing_suggestion','email_templates'));

$PMDR->get('Authentication')->authenticate();

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['suggestion_allow']) {
    $PMDR->get('Error',404);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_suggestion'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_suggestion'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_suggestion'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_suggestion'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_suggestion'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_suggestion')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/listing_suggestion.tpl');

$form = $PMDR->getNew('Form');
$form->addField('comments','textarea',array('label'=>$PMDR->getLanguage('public_listing_suggestion_comments')));
$added_fields = $PMDR->get('Fields')->addToForm($form,'listings_suggestion');
if(!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in')) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_listing_suggestion_security_code')));
    $form->addValidator('security_code',new Validate_Captcha());
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));

$form->addValidator('comments',new Validate_NonEmpty());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['listing_id'] = $listing['id'];
        $data['user_id'] = $PMDR->get('Session')->get('user_id');
        unset($data['security_code']);
        $PMDR->get('Listings_Suggestions')->insert($data);

        $PMDR->get('Email_Templates')->send('listings_suggestion',array('to'=>$data['user_id'],'variables'=>$data,'listing_id'=>$listing['id']));
        $PMDR->get('Email_Templates')->send('admin_listings_suggestion',array('variables'=>$data,'listing_id'=>$listing['id']));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_suggestion_submitted'));
        redirect($PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
    }
}

$template_content->set('form',$form);
$template_content->set('custom_fields',$added_fields);
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>