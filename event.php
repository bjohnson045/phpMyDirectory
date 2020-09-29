<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_events'));

// Get event from database
$event = $PMDR->get('Events')->getRow($_GET['id']);

// Generate the current event URL
$event['url'] = $PMDR->get('Events')->getURL($event['id'],$event['friendly_url']);

// If the event URL does not match the current URL, give a 404 error
if(!$event OR $event['url'] != URL_NOQUERY OR ($event['status'] != 'active' AND $event['user_id'] != $PMDR->get('Session')->get('user_id') AND @!in_array('admin_login',$_SESSION['admin_permissions']))) {
    $PMDR->get('Error',404);
}

if($_GET['action'] == 'ical') {
    $PMDR->get('Events')->downloadiCal($event['id']);
    exit();
}

$PMDR->set('page_header',null);

// Load the events template file
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'event.tpl');

$template_content->set('share',$PMDR->get('Sharing')->getHTML());

$title = coalesce($PMDR->getConfig('title_event_default'),$event['title']);
$meta_title = coalesce($event['meta_title'],$PMDR->getConfig('meta_title_event_default'),$event['title']);
$meta_description = coalesce($event['meta_description'],$PMDR->getConfig('meta_description_event_default'),$event['description'],$PMDR->getConfig('meta_description_default').' '.$event['title']);
$meta_keywords = coalesce($event['meta_keywords'],$PMDR->getConfig('meta_keywords_event_default'),$event['keywords'],$PMDR->getConfig('meta_keywords_default').' '.$event['title']);

if(!is_null($event['listing_id'])) {
    $meta_replace = array('listing_title'=>$event['listing_title']);
    foreach($meta_replace AS $find=>$replace) {
        $title = str_replace('*'.$find.'*',$replace,$title);
        $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
        $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
        $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
    }
}
$event_fields = $PMDR->get('Fields')->getFields('events');
foreach($event_fields as $key=>$field) {
    $title = str_replace('*custom_'.$field['id'].'*',$event['custom_'.$field['id']],$title);
    $meta_title = str_replace('*custom_'.$field['id'].'*',$event['custom_'.$field['id']],$meta_title);
    $meta_description = str_replace('*custom_'.$field['id'].'*',$event['custom_'.$field['id']],$meta_description);
    $meta_keywords = str_replace('*custom_'.$field['id'].'*',$event['custom_'.$field['id']],$meta_keywords);
}
$PMDR->set('page_title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

// If the event belongs to a listing load the listing specific data
if(!is_null($event['listing_id'])) {
    // If the listing has a custom header template file defined and that file exists, load it
    if(trim($event['listing_header_template_file']) != '') {
        $PMDR->set('header_file',$event['listing_header_template_file']);
    }
    // If the listing has a custom footer template file defined and that file exists, load it
    if(trim($event['listing_footer_template_file']) != '') {
        $PMDR->set('footer_file',$event['listing_footer_template_file']);
    }
    // If the listing has a custom wrapper template file defined and that file exists, load it
    if(trim($event['listing_wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$event['listing_wrapper_template_file']);
    }

    // Generate the listing URL and set in the template file
    $template_content->set('listing_url',$PMDR->get('Listings')->getURL($event['listing_id'],$event['listing_friendly_url']));
    // Set the listing title in the template file
    $template_content->set('listing_title',$event['listing_title']);

    // Look for other events from this same listing.
    if($other_events = $PMDR->get('Events')->getOtherListingEvents($event['id'],$event['listing_id'])) {
        // Generate the URLs for each of the found events
        foreach($other_events AS $key=>$other_event) {
            $other_events[$key]['url'] = $PMDR->get('Events')->getURL($other_event['id'],$other_event['friendly_url']);
        }
        unset($other_event,$key);
        $template_content->set('other_events',$other_events);
    }
}

// Set the breakcrump link text and URLs
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/events_list.php','text'=>$PMDR->getLanguage('public_events')));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$event['title']));

// Set event variables in the template
$template_content->set('id',$event['id']);
$template_content->set('url',$event['url']);
$template_content->set('title',$event['title']);
$template_content->set('date',$PMDR->get('Dates_Local')->formatDateTime($event['date']));
$template_content->set('date_update',$PMDR->get('Dates_Local')->formatDateTime($event['date_update']));
$template_content->set('description',nl2br($PMDR->get('Cleaner')->unclean_html($event['description'])));
$template_content->set('description_short',nl2br($PMDR->get('Cleaner')->unclean_html($event['description_short'])));
$template_content->set('website',$event['website']);
$template_content->set('phone',$event['phone']);
$template_content->set('email',$event['email']);
if(!LOGGED_IN) {
    $template_content->set('rsvp_url',BASE_URL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL.'?rsvp=true'));
} else {
    $template_content->set('rsvp_url','#');
}
$template_content->set('rsvp',$event['allow_rsvp']);
$template_content->set('location',$event['location']);
$template_content->set('venue',$event['venue']);
$template_content->set('keywords',$event['keywords']);
$template_content->set('admission',$event['admission']);
$template_content->set('contact_name',$event['contact_name']);

if($event_dates = $PMDR->get('Events')->getCurrentDates($event['id'])) {
    $template_content->set('date_start',$PMDR->get('Dates_Local')->formatDateTime($event_dates[0]['date_start']));
    $template_content->set('date_start_google',str_replace(array('+00:00',':','-'),'',$PMDR->get('Dates_Local')->formatDate($event_dates[0]['date_start'],'c')));
    if(!$PMDR->get('Dates')->isZero($event_dates[0]['date_end'])) {
        $template_content->set('date_end',$PMDR->get('Dates_Local')->formatDateTime($event_dates[0]['date_end']));
        $template_content->set('date_end_google',str_replace(array('+00:00',':','-'),'',$PMDR->get('Dates_Local')->formatDate($event_dates[0]['date_end'],'c')));
    }
    foreach($event_dates AS &$event_date) {
        $event_date['date_start'] = $PMDR->get('Dates_Local')->formatDateTime($event_date['date_start']);
        $event_date['date_end'] = $PMDR->get('Dates_Local')->formatDateTime($event_date['date_end']);
    }
    $template_content->set('dates',$event_dates);
} else {
    $template_content->set('expired',true);
}

$template_content->set('past_dates',$PMDR->get('Events')->getPastDates($event['id']));

$map_output = false;
$map = $PMDR->get('Map');
$map_variables = $map->getOutputVariables(Strings::strip_new_lines($event['location']),'','','','',$event['latitude'],$event['longitude'],$event['title']);
$template_content->setArray($map_variables);

$template_content->set('image_url',get_file_url_cdn(EVENT_IMAGES_PATH.$event['id'].'.'.$event['image_extension']));

if(!LOGGED_IN) {
    $template_content->set('rsvped',0);
} else {
    $template_content->set('rsvped',$PMDR->get('Events')->getUserRSVP($event['id'],$PMDR->get('Session')->get('user_id')));
}

$PMDR->get('Fields_Groups')->addToTemplate($template_content,$event,'events');

include(PMDROOT.'/includes/template_setup.php');
?>