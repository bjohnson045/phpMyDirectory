<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_classified','email_templates'));

// Get classified from database
$classified = $db->GetRow("
    SELECT c.*,
    l.id AS listing_id,
    l.title AS listing_title,
    l.friendly_url AS listing_friendly_url,
    l.header_template_file AS listing_header_template_file,
    l.footer_template_file AS listing_footer_template_file,
    l.wrapper_template_file AS listing_wrapper_template_file,
    l.classifieds_images_allow,
    l.mail AS listing_mail,
    ci.id AS image_id,
    ci.extension AS image_extension,
    IF(expire_date < NOW() AND expire_date IS NOT NULL, 1, 0) AS expired
    FROM ".T_CLASSIFIEDS." c
    LEFT JOIN ".T_LISTINGS." l ON c.listing_id=l.id
    LEFT JOIN ".T_CLASSIFIEDS_IMAGES." ci ON c.id=ci.classified_id
    WHERE c.id=?",array($_GET['id'])
);

// If the classified URL does not match the current URL, give a 404 error
if(!$classified['id']) {
    // Check for a redirect
    if($new_url = $PMDR->get('Redirects')->getURLByID('classified',$_GET['id'])) {
        $PMDR->get('Error',301);
        redirect_url($new_url);
    } else {
        $PMDR->get('Error',404);
    }
}

if($classified['expired']) {
    $PMDR->get('Error',404);
}

$classified['url'] = $PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url']);

// If the classified URL does not match the current URL, give a 404 error
if($classified['url'] != URL_NOQUERY) {
    redirect($classified['url']);
}

if($_GET['action'] == 'pdf') {
    $PMDR->get('Classifieds')->generatePDF($classified);
    exit();
}

$PMDR->set('page_header',null);

if($_GET['action'] == 'print') {
    $PMDR->set('footer_file','print_footer.tpl');
    $PMDR->set('header_file','print_header.tpl');
    $PMDR->set('wrapper_file','wrapper_blank.tpl');
    $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'classified_print.tpl');
} else {
    // Load the needed CSS and javascript for displaying classifieds
    $PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
    $PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
    $PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', gallery:{enabled:true}, closeOnContentClick: true });});</script>',20);

    // Load the classifieds template file
    $template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'classified.tpl');

    $template_content->set('share',$PMDR->get('Sharing')->getHTML());

    if(!is_null($classified['listing_id'])) {
        // If the listing has a custom header template file defined and that file exists, load it
        if(trim($classified['listing_header_template_file']) != '') {
            $PMDR->set('header_file',$classified['listing_header_template_file']);
        }
        // If the listing has a custom footer template file defined and that file exists, load it
        if(trim($classified['listing_footer_template_file']) != '') {
            $PMDR->set('footer_file',$classified['listing_footer_template_file']);
        }
        // If the listing has a custom wrapper template file defined and that file exists, load it
        if(trim($classified['listing_wrapper_template_file']) != '') {
            $PMDR->set('wrapper_file',$classified['listing_wrapper_template_file']);
        }
    }
}

$title = coalesce($PMDR->getConfig('title_classified_default'),$classified['title']);
$meta_title = coalesce($classified['meta_title'],$PMDR->getConfig('meta_title_classified_default'),$classified['title']);
$meta_description = coalesce($classified['meta_description'],$PMDR->getConfig('meta_description_classified_default'),$classified['description'],$PMDR->getConfig('meta_description_default').' '.$classified['title']);
$meta_keywords = coalesce($classified['meta_keywords'],$PMDR->getConfig('meta_keywords_classified_default'),$classified['keywords'],$PMDR->getConfig('meta_keywords_default').' '.$classified['title']);

if(!is_null($classified['listing_id'])) {
    $meta_replace = array('listing_title'=>$classified['listing_title']);
    foreach($meta_replace AS $find=>$replace) {
        $title = str_replace('*'.$find.'*',$replace,$title);
        $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
        $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
        $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
    }
}
$classified_fields = $PMDR->get('Fields')->getFields('classifieds');
foreach($classified_fields as $key=>$field) {
    $title = str_replace('*custom_'.$field['id'].'*',$classified['custom_'.$field['id']],$title);
    $meta_title = str_replace('*custom_'.$field['id'].'*',$classified['custom_'.$field['id']],$meta_title);
    $meta_description = str_replace('*custom_'.$field['id'].'*',$classified['custom_'.$field['id']],$meta_description);
    $meta_keywords = str_replace('*custom_'.$field['id'].'*',$classified['custom_'.$field['id']],$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

// If the classified belongs to a listing load the listing specific data
if(!is_null($classified['listing_id'])) {
    // Generate the listing URL and set in the template file
    $classified['listing_url'] = $PMDR->get('Listings')->getURL($classified['listing_id'],$classified['listing_friendly_url']);
    $template_content->set('listing_url',$classified['listing_url']);
    // Set the listing title in the template file
    $template_content->set('listing_title',$classified['listing_title']);

    // Look for other classifieds from this same listing.
    if($other_classifieds = $db->GetAll("
        SELECT c.id, c.title, c.friendly_url, ci.id AS image_id, ci.extension AS image_extension 
        FROM ".T_CLASSIFIEDS." c         
        LEFT JOIN (SELECT classified_id, MIN(id) id FROM ".T_CLASSIFIEDS_IMAGES." GROUP BY classified_id) cii ON cii.classified_id=c.id
        LEFT JOIN ".T_CLASSIFIEDS_IMAGES." ci on ci.id=cii.id
        WHERE listing_id=? AND (expire_date > NOW() OR expire_date IS NULL) 
        ORDER BY c.id LIMIT 3",array($classified['listing_id']))
    ) {
        // Generate the URLs for each of the found classifieds
        foreach($other_classifieds AS $key=>$other_classified) {
            if($other_classified['id'] == $classified['id']) {
                $template_content->set('other_classified_index',($key+1));
                if($key != 0) {
                    $template_content->set('previous_url',$other_classifieds[($key-1)]['url']);
                }
                if($key+1 != count($other_classifieds)) {
                    $template_content->set('next_url',$PMDR->get('Classifieds')->getURL($other_classifieds[($key+1)]['id'],$other_classifieds[($key+1)]['friendly_url']));
                }
                unset($other_classifieds[$key]);
            } else {
                $other_classifieds[$key]['url'] = $PMDR->get('Classifieds')->getURL($other_classified['id'],$other_classified['friendly_url']);
                $other_classifieds[$key]['thumbnail_url'] = $PMDR->get('Classifieds')->getImageThumbnailURL($other_classified);
            }
        }
        unset($other_classified,$key);
        // Set the other classifieds in the template file
        $template_content->set('other_classifieds',$other_classifieds);
    }
}

// Set the breakcrump link text and URLs
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('public_classified')));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$classified['title']));

// Set classified variables in the template
$template_content->set('classified_url',$classified['url']);
$template_content->set('id',$classified['id']);
$template_content->set('title',$classified['title']);
$template_content->set('date',$PMDR->get('Dates_Local')->formatDateTime($classified['date']));
$template_content->set('date_iso',$PMDR->get('Dates')->formatDate($classified['date_iso'],'c'));
$template_content->set('description',nl2br($PMDR->get('Cleaner')->unclean_html($classified['description'])));
$template_content->set('price',format_number_currency($classified['price']));
$template_content->set('www',$classified['www']);
$template_content->set('buy_url',$classified['buttoncode']);
$template_content->set('print', 'JavaScript:newWindow(\''.$classified['url'].'?action=print\',\'popup\','.$PMDR->getConfig('print_window_width').','.$PMDR->getConfig('print_window_height').',\'\')');
$template_content->set('pdf', $classified['url'].'?action=pdf');
$template_content->set('email', $PMDR->get('Classifieds')->getURL($classified['id'],$classified['friendly_url'],'','/contact.html','classified_email.php'));

// If the expiration date is not set, change it to a dash
if(!$PMDR->get('Dates')->isZero($classified['expire_date'])) {
    $template_content->set('expire_date',$PMDR->get('Dates_Local')->formatDateTime($classified['expire_date']));
    $template_content->set('expire_date_iso',$PMDR->get('Dates')->formatDate($classified['expire_date'],'c'));
}

if($classified['classifieds_images_allow']) {
    $classified_images = $PMDR->get('Classifieds')->getImages($classified['id']);
    // Set the classified images in the template
    $template_content->set('classified_images',$classified_images);
    // Set the first/default classified image for display, if one does not exist we display the "noimage" image from the template folder.
    $template_content->set('classified_image',$PMDR->get('Classifieds')->getImageURL($classified,true));
}

$email_form = $PMDR->getNew('Form');
$email_form->addField('from_name','text',array('label'=>$PMDR->getLanguage('public_classified_email_from_name'),'fieldset'=>'input_default'));
$email_form->addField('from_email','text',array('label'=>$PMDR->getLanguage('public_classified_email_from_email'),'fieldset'=>'input_default'));
if(LOGGED_IN) {
    $email_form->setFieldAttribute('from_name','value',trim($user['user_first_name'].' '.$user['user_last_name']));
    $email_form->setFieldAttribute('from_email','value',$user['user_email']);
}
$email_form->addField('message','textarea',array('label'=>$PMDR->getLanguage('public_classified_email_message'),'fieldset'=>'input_default','counter'=>$PMDR->getConfig('send_message_size')));
$email_form_fields = $PMDR->get('Fields')->addToForm($email_form,'classifieds_email',array('fieldset'=>'input_default'));
$email_form->addField('copy','checkbox',array('label'=>'','fieldset'=>'input_default','html'=>$PMDR->getLanguage('public_classified_email_copy')));
if($PMDR->getConfig('GD_security_send_message') AND (!LOGGED_IN OR $PMDR->getConfig('captcha_logged_in'))) {
    $email_form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('public_classified_email_security_code'),'fieldset'=>'input_default'));
    $email_form->addValidator('security_code',new Validate_Captcha());
}
$email_form->addField('submit','submit',array('label'=>$PMDR->getLanguage('public_submit')));
$email_form->addValidator('from_name',new Validate_NonEmpty());
$email_form->addValidator('from_email',new Validate_Email(true));
$email_form->addValidator('message',new Validate_NonEmpty());
$email_form->addValidator('message',new Validate_Banned_Words());
$email_form->addValidator('message',new Validate_Banned_URL());

if($email_form->wasSubmitted('submit')) {
    $data = $email_form->loadValues();
    if($PMDR->get('IP_Limits')->isOverHourLimit('classified_email',$PMDR->getConfig('listing_email_ip_limit'),$PMDR->getConfig('listing_email_ip_limit_hours'))) {
        $email_form->addError($PMDR->getLanguage('public_classified_email_ip_limit_error',array($PMDR->getConfig('listing_email_ip_limit'),$PMDR->getConfig('listing_email_ip_limit_hours'))));
    }
    if(!$email_form->validate()) {
        $PMDR->addMessage('error',$email_form->parseErrorsForTemplate());
        $template_message = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/message.tpl');
        $template_message->set('message_types',$PMDR->getMessages());
        $template_content->set('message',$template_message);
        $template_content->set('email_form_show',true);
    } else {
        $data['message'] = Strings::limit_characters($data['message'],$PMDR->getConfig('send_message_size'));

        if($listing AND !empty($listing['mail'])) {
            $to_email = $listing['mail'];
        } else {
            $to_email = $user['user_email'];
        }

        $PMDR->get('Email_Templates')->send('classifieds_email',array('to'=>$to_email,'variables'=>$data,'classified_id'=>$classified['id']));
        $PMDR->get('Email_Templates')->send('admin_classifieds_email',array('variables'=>$data,'classified_id'=>$classified['id']));

        if($data['copy']) {
            $PMDR->get('Email_Templates')->send('classifieds_email_copy',array('to'=>$data['from_email'],'variables'=>$data,'classified_id'=>$classified['id']));
        }

        $PMDR->get('IP_Limits')->insert(array('type'=>'classified_email'));

        $PMDR->addMessage('success',$PMDR->getLanguage('public_classified_email_sent'));
        redirect_url($classified['url'].'#');
    }
}

$template_content->set('email_form',$email_form);
$template_content->set('email_form_fields',$email_form_fields);

$PMDR->get('Fields_Groups')->addToTemplate($template_content,$classified,'classifieds');

include(PMDROOT.'/includes/template_setup.php');
?>