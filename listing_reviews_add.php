<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_listing','public_listing_reviews','email_templates'));

if($PMDR->getConfig('reviews_require_login')) {
    $PMDR->get('Authentication')->authenticate(array('error_message'=>$PMDR->getLanguage('public_listing_reviews_login_error')));
}

if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
    $PMDR->get('Error',404);
}

if(!$listing['reviews_allow']) {
    $PMDR->get('Error',404);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews_add'));
$meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews_add'));
$meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews_add'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews_add'));

$meta_replace = array('title'=>$PMDR->getLanguage('public_listing_reviews_add'),'listing_title'=>$listing['title']);
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
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_listing_reviews_add')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/listing_reviews_add.tpl');

$form = $PMDR->getNew('Form');
if(!LOGGED_IN) {
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('public_listing_reviews_name')));
}
$form->addField('title','text',array('label'=>$PMDR->getLanguage('public_listing_reviews_title'),'counter'=>75));
$form->addField('rating','stars',array('label'=>$PMDR->getLanguage('public_listing_reviews_rating'),'value'=>0));
$ratings_categories = $db->GetAll("SELECT id, title FROM ".T_RATINGS_CATEGORIES." ORDER BY ordering, title");
foreach($ratings_categories AS $category) {
    $form->addField('category_'.$category['id'],'stars',array('label'=>$category['title'].' '.$PMDR->getLanguage('public_listing_reviews_rating'),'value'=>0));
}
$form->addField('review','textarea',array('label'=>$PMDR->getLanguage('public_listing_reviews_review'),'counter'=>$PMDR->getConfig('review_size')));
$custom_fields = $PMDR->get('Fields')->addToForm($form,'reviews',array('admin_only'=>false,'category'=>$listing['primary_category_id']));
if($PMDR->getConfig('reviews_captcha') AND (!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in'))) {
    $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_listing_reviews_security_code')));
    $form->addValidator('security_code',new Validate_Captcha());
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));

$form->addValidator('title',new Validate_NonEmpty());
$form->addValidator('title',new Validate_Banned_Words());
$form->addValidator('review',new Validate_NonEmpty());
$form->addValidator('review',new Validate_Banned_Words());
$form->addValidator('rating',new Validate_NonEmpty(false));

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if($PMDR->get('Session')->get('user_id')) {
        if($db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS." WHERE user_id=? AND listing_id=?",array($PMDR->get('Session')->get('user_id'),$listing['id']))) {
            $form->addError($PMDR->getLanguage('public_listing_reviews_add_error_user'));
        }
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $data['review'] = Strings::limit_characters($data['review'],$PMDR->getConfig('review_size'));
        $data['listing_id'] = $listing['id'];

        if($data['user_id'] = $PMDR->get('Session')->get('user_id')) {
            $user = $PMDR->get('Users')->getRow($data['user_id']);
        } else {
            $data['user_id'] = NULL;
        }

        $data['status'] = $PMDR->getConfig('reviews_status');

        if($PMDR->get('Session')->get('user_id') AND $data['rating_id'] = $PMDR->get('DB')->GetOne("SELECT id FROM ".T_RATINGS." WHERE listing_id=? AND user_id=? LIMIT 1",array($data['listing_id'], $PMDR->get('Session')->get('user_id')))) {
            // We found an existing rating for the user, update it to the new value.
            $PMDR->get('Ratings')->update(array('rating'=>$data['rating'],'listing_id'=>$data['listing_id'],'user_id'=>$PMDR->get('Session')->get('user_id'),'ip_address'=>get_ip_address()), $data['rating_id']);
        } else {
            // Create a new rating (attach user if we have one)
            $data['user_id'] = ($PMDR->get('Session')->get('user_id') === false ? null : $PMDR->get('Session')->get('user_id'));
            $data['ip_address'] = get_ip_address();
            $data['rating_id'] = $PMDR->get('Ratings')->insert($data);
        }

        $data['review_id'] = $PMDR->get('Reviews')->insert($data);

        if($user) {
            $PMDR->get('Email_Templates')->send('listing_review_submitted',array('to'=>$user['user_email'],'user_id'=>$user['id'],'review_id'=>$data['review_id']));
        }

        if($PMDR->getConfig('reviews_status') == 'active') {
            $PMDR->get('Email_Templates')->send('listing_review_submitted_notification',array('to'=>$listing['user_id'],'review_id'=>$data['review_id']));
        }

        $PMDR->get('Email_Templates')->send('admin_listing_review_submitted',array('review_id'=>$data['review_id']));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_listing_reviews_submitted'),'insert');
        redirect($PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
    }
}

$template_content->set('form',$form);
$template_content->set('categories',$ratings_categories);
$template_content->set('custom_fields',$custom_fields);
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('listing',$listing);
if(!LOGGED_IN) {
    $template_content->set('log_in_url',BASE_URL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL));
}

include(PMDROOT.'/includes/template_setup.php');
?>