<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

$PMDR->loadLanguage(array('public_browse_locations','public_listing','public_search_results'));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/browse_locations.php','text'=>$PMDR->getLanguage('public_browse_locations')));

$search = $PMDR->get('Search','ListingFullText');
$search->listing_status = 'active';
$search->locationSearchChildren = $PMDR->getConfig('location_browse_children');

$sort_order1 = explode(':',$PMDR->getConfig('listing_browse_order_1'));
if($sort_order1[0] == 'random') {
    $sort_order1[0] = 'RAND(\''.session_id().'\')';
    $sort_order1[1] = 'ASC';
}
$search->sortBy = array($sort_order1[0]=>$sort_order1[1]);
if($PMDR->getConfig('listing_search_order_2') != '') {
    $sort_order2 = explode(':',$PMDR->getConfig('listing_browse_order_2'));
    if($sort_order2[0] == 'random') {
        $sort_order2[0] = 'RAND(\''.session_id().'\')';
        $sort_order2[1] = 'ASC';
    }
    $search->sortBy = array_merge($search->sortBy,array($sort_order2[0]=>$sort_order2[1]));
}
unset($sort_order1);
unset($sort_order2);

$path = array();

if(empty($_GET['id'])) {
    $PMDR->setAdd('page_title',$PMDR->getLanguage('public_browse_locations'));
    $PMDR->set('meta_title',coalesce($PMDR->getConfig('browse_locations_meta_title'),$PMDR->getLanguage('public_browse_locations')));
    $PMDR->set('meta_description',coalesce($PMDR->getConfig('browse_locations_meta_description'),$PMDR->getLanguage('public_browse_locations')));
    $_GET['id'] = 1;
} else {
    if(MOD_REWRITE) {
        if(!$location_id = $PMDR->get('Locations')->getIDByURL($_GET['id'])) {
            if($url = $PMDR->get('Redirects')->getURL(md5($_GET['id']),'location')) {
                $PMDR->get('Error',301);
                redirect_url($PMDR->get('Locations')->getURL(null,$url));
            }
            $PMDR->get('Error',404);
        } else {
            $_GET['id'] = $location_id;
            unset($location_id);
        }
    } else {
        if(!$PMDR->get('Locations')->getNode($_GET['id'])) {
            $PMDR->get('Error',404);
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'browse_locations.tpl');
$template_content->cache_id = 'locations_'.md5(URL).$PMDR->getLanguage('languageid');

// Handle current location
if(!empty($_GET['id']) AND $_GET['id'] != 1) {
    // Location gets an impression added to the database
    $PMDR->get('Statistics')->insert('location_impression',$_GET['id']);
    // Get path array of current location being viewed
    $path = $PMDR->get('Locations')->getPath($_GET['id']);
    $active_record = $PMDR->get('Locations')->get($_GET['id']);

    if(trim($active_record['header_template_file']) != '') {
        $PMDR->set('header_file',$active_record['header_template_file']);
    }
    if(trim($active_record['footer_template_file']) != '') {
        $PMDR->set('footer_file',$active_record['footer_template_file']);
    }
    if(trim($active_record['wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$active_record['wrapper_template_file']);
    }

    foreach($path as $crumb) {
        $PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Locations')->getURL($crumb['id'],$crumb['friendly_url_path']),'text'=>$crumb['title']));
    }
    $title = coalesce($PMDR->getConfig('title_location_default'),$PMDR->get('Locations')->getPathDisplay(array_reverse($path),' ',false));
    $meta_title = coalesce($active_record['meta_title'],$PMDR->getConfig('meta_title_location_default'),$PMDR->get('Locations')->getPathDisplay(array_reverse($path),', ',false));
    $meta_description = coalesce($active_record['meta_description'],$PMDR->getConfig('meta_description_location_default'),$active_record['description_short'],strip_tags($active_record['description']),$PMDR->get('Locations')->getPathDisplay(array_reverse($path),' ',false));
    $meta_keywords = coalesce($active_record['meta_keywords'],$PMDR->getConfig('meta_keywords_location_default'),$active_record['keywords'],$PMDR->get('Locations')->getPathDisplay($path,', ',false));
    $depth = $PMDR->get('Locations')->getDepth();
    $title = preg_replace('/(\[([^\]]*))?\*location\*(([^\]]*)\])?/','${2}'.$active_record['title'].'${4}',$title);
    $meta_title = preg_replace('/(\[([^\]]*))?\*location\*(([^\]]*)\])?/','${2}'.$active_record['title'].'${4}',$meta_title);
    for($x = 1; $x <= $depth; $x++) {
        $replace = '';
        if(!empty($path[($x-1)]['title'])) {
            $replace = '${2}'.$path[($x-1)]['title'].'${4}';
        }
        $title = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$title);
        $meta_title = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_title);
        $meta_description = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_description);
        $meta_keywords = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_keywords);
    }
    $PMDR->set('page_title',$title);
    $PMDR->set('meta_title',$meta_title);
    $PMDR->set('meta_description',$meta_description);
    $PMDR->set('meta_keywords',$meta_keywords);
    unset($meta_title,$meta_description,$meta_keywords,$depth,$replace,$depth);

    $PMDR->set('canonical_url',$PMDR->get('Locations')->getURL($active_record['id'],$active_record['friendly_url_path']));
    $PMDR->set('active_location',array('id'=>$active_record['id'],'friendly_url_path'=>$active_record['friendly_url_path']));

    // Setup current location display data and get any category information
    if(!($template_content->isCached())) {
        if(!empty($active_record['large_image_url'])) {
            $template_content->set('location_image',$active_record['large_image_url']);
        } else {
            $template_content->set('location_image',get_file_url_cdn(LOCATION_IMAGE_PATH.$active_record['id'].'.*'));
        }
        $template_content->set('location_id',$active_record['id']);
        $template_content->set('location_url',$active_record['url']);
        if($PMDR->getConfig('show_location_title')) {
            $template_content->set('location_title',$active_record['title']);
            $template_content->set('location_meta_title',$active_record['meta_title']);
        }
        if($PMDR->getConfig('show_location_description')) {
            $template_content->set('location_description',$active_record['description']);
            $template_content->set('location_description_short',$active_record['description_short']);
        }

        $template_content->set('add_listing',$PMDR->get('Locations')->isLeaf($active_record));

        $PMDR->get('Fields_Groups')->addToTemplate($template_content,$active_record,'locations');
    }

    // Get only results within this location ID
    $search->location_id = $_GET['id'];

    // Get final listing results
    $paging = $PMDR->get('Paging');
    $paging->linksNumber = 5;
    $paging->setResultsNumber($PMDR->getConfig('count_directory'));
    $listings_results = $search->getResults($paging->limit1,$paging->limit2);
    $listings_count = $search->resultsCount;
    $paging->setTotalResults($search->resultsCount);
    $template_content->set('results_amount',$listings_count);
    $pageArray = $paging->getPageArray();
}

// Setup and get the categories to display
if(!($template_content->isCached())) {
    if(!$locations_parsed = $PMDR->get('Cache')->get('locations_parsed_'.md5(URL).$PMDR->getLanguage('languageid'), 1800, 'locations_')) {
        $locations = $PMDR->get('Locations')->getChildren($_GET['id'], ($PMDR->getConfig('loc_show_subs_number') > 0) ? 2 : 1,$PMDR->getConfig('loc_show_subs_number'));
        foreach($locations as $key=>$location) {
            $locations[$key]['url'] = $PMDR->get('Locations')->getURL($location['id'],$location['friendly_url_path']);
        }
        $locations_parsed = $PMDR->get('Locations')->parseForBrowsing($locations,(isset($active_record) ? $active_record['display_columns'] : null));
        $PMDR->get('Cache')->write('locations_parsed_'.md5(URL).$PMDR->getLanguage('languageid'),$locations_parsed,'locations_');
    }
    $locations_parsed_count = count($locations_parsed);
    unset($locations);
    $template_content->set('location_columns',$locations_parsed);
    unset($locations_parsed);

    $template_content->set('show_indexes',$PMDR->getConfig('show_indexes'));
    $template_content->set('loc_show_indexes',$PMDR->getConfig('loc_show_indexes'));
    $template_content->set('show_location_description',$PMDR->getConfig('show_location_description'));

    // If we have no locations left, then we show the categories
    if($locations_parsed_count < 1) {
        if(!$categories_parsed = $PMDR->get('Cache')->get('categories_parsed_location_'.md5(URL).$PMDR->getLanguage('languageid'), 1800, 'categories_')) {
            $categories = $PMDR->get('Categories')->getChildren(1, ($PMDR->getConfig('show_subs_number') > 0) ? 2 : 1,$PMDR->getConfig('show_subs_number'));
            foreach($categories as $key=>$category) {
                $categories[$key]['url'] = $PMDR->get('Locations')->getURL($active_record['id'],$active_record['friendly_url_path'],$category['id'],$category['friendly_url_path']);
            }
            $categories_parsed = $PMDR->get('Categories')->parseForBrowsing($categories);
            $PMDR->get('Cache')->write('categories_parsed_location_'.md5(URL).$PMDR->getLanguage('languageid'),$categories_parsed,'categories_');
        }
        unset($categories);
        if(count($categories_parsed) > 0 AND ($listings_count > 0 OR !$PMDR->getConfig('category_browse_children'))) {
            $template_content->set('category_columns',$categories_parsed);
        }
        unset($categories_parsed);
    }
}

if($listings_count > 0) {
    if(trim($active_record['header_template_file']) != '' AND file_exists(PMDROOT.TEMPLATE_PATH.'blocks/'.$active_record['header_template_file'])) {
        $template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/'.$active_record['header_template_file']);
    } else {
        $template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/listing_results.tpl');
    }
    $template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
    $template_page_navigation->set('page',$pageArray);
    if(!empty($listings_results[0]['score'])) $template_results->set('score',true);
    if(!empty($listings_results[0]['zip_distance'])) $template_results->set('zip_distance',true);
    $template_results->set('search_within_link','<a href="'.BASE_URL.'/search.php?location='.$active_record['id'].'">Search within '.$active_record['title'].$location_title_string.'</a>');
    $template_results->set('page',$pageArray);
    ob_start();
    include(PMDROOT . "/includes/template_listing_results.php");
    $listing_results = ob_get_contents();
    ob_end_clean();
    $template_results->set('listing_results',$listing_results);
    $template_results->set('page_navigation',$template_page_navigation);
}

$form_search_within = $PMDR->getNew('Form');
$form_search_within->action = BASE_URL.'/search_results.php';
$form_search_within->method = 'GET';
$form_search_within->addField('keyword','text',array('id'=>'search_within_keyword','label'=>$PMDR->getLanguage('public_contact_name'),'fieldset'=>'contact_us'));
$form_search_within->addField('location_id','hidden',array('id'=>'search_within_location_id','value'=>$active_record['id']));
$form_search_within->addField('submit','submit',array('id'=>'search_within_submit','label'=>$PMDR->getLanguage('public_general_search_search')));

$template_content->set('breadcrumb',array_slice($PMDR->get('breadcrumb'),1));
$template_content->set('listing_results',$template_results);
$template_content->set('form_search_within',$form_search_within);

include(PMDROOT.'/includes/template_setup.php');
?>