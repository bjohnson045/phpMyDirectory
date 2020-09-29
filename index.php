<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

if(defined('ADDON_DISCOUNT_CODES') AND ADDON_DISCOUNT_CODES) {
    $PMDR->get('Discount_Codes')->setURLCode();
}

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_index','public_browse_categories','public_browse_locations','email_templates'));

$categories = $PMDR->get('Categories');
$locations = $PMDR->get('Locations');

// If admin bot checking is enabled we try to check for search engine bots
if($PMDR->getConfig('traffic_bot_check')) {
    if(!is_null(BOT)) {
        $PMDR->get('Email_Templates')->send('admin_bot_detection',array('variables'=>array('bot'=>BOT,'url'=>URL,'date'=>$PMDR->get('Dates_Local')->formatDateTimeNow())));
    }
}

// Get the index page template
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/index.tpl');

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_index_title'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('meta_title_default'),$PMDR->getConfig('title')));
$PMDR->set('meta_description',$PMDR->getConfig('meta_description_default'));
$PMDR->set('canonical_url',BASE_URL.'/');
$PMDR->set('og:type','website');
if($logo = $PMDR->getConfig('logo')) {
    $PMDR->set('meta_image',get_file_url(TEMP_UPLOAD_PATH.$PMDR->getConfig('logo')));
}

// If a custom page is set for the index page content, load that page
if($PMDR->getConfig('browse_index_type') == 'categories' OR $PMDR->getConfig('browse_index_type') == 'locations' OR $PMDR->getConfig('browse_index_type') == 'categories_locations') {
    // If categories or locations and categories are set to display ont he index page, load the information
    if($PMDR->getConfig('browse_index_type') == 'categories' OR $PMDR->getConfig('browse_index_type') == 'categories_locations') {
        if(!$categories_parsed = $PMDR->get('Cache')->get('categories_parsed_1', 1800, 'categories_')) {
            $index_categories = $categories->getChildren(1,($PMDR->getConfig('show_subs_number') > 0) ? 2 : 1,$PMDR->getConfig('show_subs_number'));
            // Set the URLs for the categories
            foreach($index_categories as $key=>$category) {
                $index_categories[$key]['url'] = $categories->getURL($category['id'],$category['friendly_url_path']);
            }
            $categories_parsed = $categories->parseForBrowsing($index_categories);
            $PMDR->get('Cache')->write('categories_parsed_1',$categories_parsed,'categories_');
        }
        $template_content->set('category_columns',$categories_parsed);
    }
    // If locations or locations and categories are set to display ont he index page, load the information
    if($PMDR->getConfig('browse_index_type') == 'locations' OR $PMDR->getConfig('browse_index_type') == 'categories_locations') {
        if(!$locations_parsed = $PMDR->get('Cache')->get('locations_parsed_1', 1800, 'locations_')) {
            $index_locations = $locations->getChildren(1,($PMDR->getConfig('loc_show_subs_number') > 0) ? 2 : 1,$PMDR->getConfig('loc_show_subs_number'));
            // Set the URLs for the locations
            foreach($index_locations as $key=>$location) {
                $index_locations[$key]['url'] = $locations->getURL($location['id'],$location['friendly_url_path']);
            }
            $locations_parsed = $locations->parseForBrowsing($index_locations);
            $PMDR->get('Cache')->write('locations_parsed_1',$locations_parsed,'locations_');
        }
        $template_content->set('location_columns',$locations_parsed);
    } else {
        $template_content->set('location_columns',array());
    }

    // Set the template variables to control the display of data on the template
    $template_content->set('show_category_description',$PMDR->getConfig('show_category_description'));
    $template_content->set('show_location_description',$PMDR->getConfig('show_location_description'));
    $template_content->set('show_indexes',$PMDR->getConfig('show_indexes'));
    $template_content->set('loc_show_indexes',$PMDR->getConfig('loc_show_indexes'));
    $template_content->set('page', null);
} else {
    $page = $PMDR->get('CustomPage')->getRow($PMDR->getConfig('browse_index_type'));
    $template_content->set('page',$page['content_parsed']);
    if(!empty($page['meta_title'])) {
        $PMDR->set('meta_title',$page['meta_title']);
    }
    if(!empty($page['meta_description'])) {
        $PMDR->set('meta_description',$page['meta_description']);
    }
    if(!empty($page['meta_keywords'])) {
        $PMDR->set('meta_keywords',$page['meta_keywords']);
    }

    $PMDR->setAdd('page_title',$page['title']);

    // Set the page templates
    if(trim($page['header_template_file']) != '') {
        $PMDR->set('header_file',$page['header_template_file']);
    }
    if(trim($page['footer_template_file']) != '') {
        $PMDR->set('footer_file',$page['footer_template_file']);
    }
    if(trim($page['wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$page['wrapper_template_file']);
    }

    unset($page);
}

include(PMDROOT.'/includes/template_setup.php');
?>