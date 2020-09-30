<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

$PMDR->loadLanguage(array('public_browse_categories','public_listing','public_search_results'));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/browse_categories.php','text'=>$PMDR->getLanguage('public_browse_categories')));

$search = $PMDR->get('Search','ListingFullText');
$search->listing_status = 'active';
$search->categorySearchChildren = $PMDR->getConfig('category_browse_children');
$search->locationSearchChildren = $PMDR->getConfig('location_browse_children');

$sort_order1 = explode(':',$PMDR->getConfig('listing_browse_order_1'));
if($sort_order1[0] == 'random') {
    $sort_order1[0] = 'RAND(\''.session_id().'\')';
    $sort_order1[1] = 'ASC';
}
$search->sortBy = array(value($sort_order1,0)=>value($sort_order1,1));
if($PMDR->getConfig('listing_search_order_2') != '') {
    $sort_order2 = explode(':',$PMDR->getConfig('listing_browse_order_2'));
    if($sort_order2[0] == 'random') {
        $sort_order2[0] = 'RAND(\''.session_id().'\')';
        $sort_order2[1] = 'ASC';
    }
    $search->sortBy = array_merge($search->sortBy,array(value($sort_order2,0)=>value($sort_order2,1)));
}
unset($sort_order1);
unset($sort_order2);

$path = array();

if(empty($_GET['id'])) {
    $PMDR->setAdd('page_title',$PMDR->getLanguage('public_browse_categories'));
    $PMDR->set('meta_title',coalesce($PMDR->getConfig('browse_categories_meta_title'),$PMDR->getLanguage('public_browse_categories')));
    $PMDR->set('meta_description',coalesce($PMDR->getConfig('browse_categories_meta_description'),$PMDR->getLanguage('public_browse_categories')));
    $_GET['id'] = 1;
} else {
    if(MOD_REWRITE) {
        if(!$category_id = $PMDR->get('Categories')->getIDByURL($_GET['id'])) {
            if($url = $PMDR->get('Redirects')->getURL(md5($_GET['id']),'category')) {
                $PMDR->get('Error',301);
                redirect_url($PMDR->get('Categories')->getURL(null,$url));
            }
            if(is_numeric($_GET['id']) AND $category = $PMDR->get('Categories')->getNode($_GET['id'])) {
                $PMDR->get('Error',301);
                redirect_url($PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']));
            }
            $PMDR->get('Error',404);
        } else {
            $_GET['id'] = $category_id;
            unset($category_id);
        }
    } else {
        if(!$PMDR->get('Categories')->getNode($_GET['id'])) {
            $PMDR->get('Error',404);
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'browse_categories.tpl');
if(isset($_GET['location'])) {
    $template_content->cache_id = 'categories_location_'.md5(URL).$PMDR->getLanguage('languageid');
} else {
    $template_content->cache_id = 'categories_'.md5(URL).$PMDR->getLanguage('languageid');
}

// Handle current category
if(!empty($_GET['id']) AND $_GET['id'] != 1) {
    // Category gets an impression added to the database
    $PMDR->get('Statistics')->insert('category_impression',$_GET['id']);
    // Get path array of current category being viewed
    $path = $PMDR->get('Categories')->getPath($_GET['id']);
    $active_record = $PMDR->get('Categories')->get($_GET['id']);

    if(trim($active_record['header_template_file']) != '') {
        $PMDR->set('header_file',$active_record['header_template_file']);
    }
    if(trim($active_record['footer_template_file']) != '') {
        $PMDR->set('footer_file',$active_record['footer_template_file']);
    }
    if(trim($active_record['wrapper_template_file']) != '') {
        $PMDR->set('wrapper_file',$active_record['wrapper_template_file']);
    }
    $active_record['url'] = $PMDR->get('Categories')->getURL($active_record['id'],$active_record['friendly_url_path']);

    $PMDR->set('active_category',array('id'=>$active_record['id'],'friendly_url_path'=>$active_record['friendly_url_path']));
    $PMDR->set('canonical_url',$active_record['url']);

    if(!($template_content->isCached())) {
        if(!empty($active_record['large_image_url'])) {
            $template_content->set('category_image',$active_record['large_image_url']);
        } else {
            $template_content->set('category_image',get_file_url_cdn(CATEGORY_IMAGE_PATH.$active_record['id'].'.*'));
        }
        $template_content->set('category_id',$active_record['id']);
        $template_content->set('category_url',$active_record['url']);
        if($PMDR->getConfig('show_category_title')) {
            $template_content->set('category_title',$active_record['title']);
            $template_content->set('category_meta_title',$active_record['meta_title']);
        }
        if($PMDR->getConfig('show_category_description')) {
            $template_content->set('category_description',$active_record['description']);
            $template_content->set('category_description_short',$active_record['description_short']);
        }
        $template_content->set('related_categories',$PMDR->get('Categories')->getRelated($active_record['id']));
        $template_content->set('add_listing',($PMDR->getConfig('category_setup') == 1 OR $PMDR->get('Categories')->isLeaf($active_record)));

        $PMDR->get('Fields_Groups')->addToTemplate($template_content,$active_record,'categories');
    }

    // If we are far enough down to view a location, get its details
    if(isset($_GET['location'])) {
        if(!$location_path = $PMDR->get('Locations')->getPath($PMDR->get('Locations')->getIDByURL($_GET['location']))) {
            $PMDR->get('Error',404);
        }
        $location = $location_path[(count($location_path)-1)];
        foreach($location_path as $crumb) {
            $PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Categories')->getURL($active_record['id'],$active_record['friendly_url_path'],$crumb['id'],$crumb['friendly_url_path']),'text'=>$crumb['title']));
        }
        $PMDR->set('active_location',array('id'=>$location['id'],'friendly_url_path'=>$location['friendly_url_path']));
        $PMDR->set('canonical_url',$PMDR->get('Categories')->getURL($active_record['id'],$active_record['friendly_url_path'],$location['id'],$location['friendly_url_path']));
        $search->location_id = $location['id'];
        if(!($template_content->isCached())) {
            $template_content->set('location_title', $location['title']);
            $template_content->set('location_description', $location['description_short']);
            $template_content->set('location_description_short', $location['description']);
            $template_content->set('location_id', $location['id']);
            $template_content->set('show_location_description',$PMDR->getConfig('show_location_description'));
        }
        $title = coalesce($PMDR->getConfig('title_category_location_default'),implode(', ',array_reverse(array_map(function($location_path){ return $location_path['title']; },$location_path))).' '.$active_record['title']);
        $meta_title = coalesce($PMDR->getConfig('meta_title_category_location_default'),$active_record['title'].' '.$location['title']);
        if($PMDR->getConfig('meta_description_category_location_default') != '') {
            $meta_description = $PMDR->getConfig('meta_description_category_location_default');
        } else {
            $meta_description = coalesce($active_record['meta_description'],$active_record['description_short'],strip_tags($active_record['description']),$active_record['title']);
            $meta_description .= ' '.$PMDR->get('Locations')->getPathDisplay($location_path,' ',false);
        }
        if($PMDR->getConfig('meta_keywords_category_location_default') != '') {
            $meta_keywords = $PMDR->getConfig('meta_keywords_category_location_default');
        } else {
            $meta_keywords = coalesce($active_record['meta_keywords'],$active_record['keywords'],$PMDR->get('Categories')->getPathDisplay($path,' ',false));
            $meta_keywords .= ' '.coalesce($location['meta_keywords'],$location['keywords'],$PMDR->get('Locations')->getPathDisplay($location_path,' ',false));
        }

        $location_depth = $PMDR->get('Locations')->getDepth();
        $title = preg_replace('/(\[([^\]]*))?\*location\*(([^\]]*)\])?/','${2}'.$location['title'].'${4}',$title);
        $meta_title = preg_replace('/(\[([^\]]*))?\*location\*(([^\]]*)\])?/','${2}'.$location['title'].'${4}',$meta_title);
        $meta_description = preg_replace('/(\[([^\]]*))?\*location\*(([^\]]*)\])?/','${2}'.$location['title'].'${4}',$meta_description);
        for($x = 1; $x <= $location_depth; $x++) {
            $replace = '';
            if(!empty($location_path[($x-1)]['title'])) {
                $replace = '${2}'.$location_path[($x-1)]['title'].'${4}';
            }
            $title = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$title);
            $meta_title = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_title);
            $meta_description = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_description);
            $meta_keywords = preg_replace('/(\[([^\]]*))?\*location_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_keywords);
        }
    } else {
        $location['id'] = 1;
        $title = coalesce($PMDR->getConfig('title_category_default'),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
        $meta_title = coalesce($active_record['meta_title'],$PMDR->getConfig('meta_title_category_default'),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
        $meta_description = coalesce($active_record['meta_description'],$PMDR->getConfig('meta_description_category_default'),$active_record['description_short'],strip_tags($active_record['description']),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
        $meta_keywords = coalesce($active_record['meta_keywords'],$PMDR->getConfig('meta_keywords_category_default'),$active_record['keywords'],$PMDR->get('Categories')->getPathDisplay($path,', ',false));
    }

    $depth = $PMDR->get('Categories')->getDepth();
    $title = preg_replace('/(\[([^\]]*))?\*category\*(([^\]]*)\])?/','${2}'.$active_record['title'].'${4}',$title);
    $meta_title = preg_replace('/(\[([^\]]*))?\*category\*(([^\]]*)\])?/','${2}'.$active_record['title'].'${4}',$meta_title);
    $meta_description = preg_replace('/(\[([^\]]*))?\*category\*(([^\]]*)\])?/','${2}'.$active_record['title'].'${4}',$meta_description);
    for($x = 1; $x <= $depth; $x++) {
        $replace = '';
        if(!empty($path[($x-1)]['title'])) {
            $replace = '${2}'.$path[($x-1)]['title'].'${4}';
        }
        $title = preg_replace('/(\[([^\]]*))?\*category_'.$x.'\*(([^\]]*)\])?/',$replace,$title);
        $meta_title = preg_replace('/(\[([^\]]*))?\*category_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_title);
        $meta_description = preg_replace('/(\[([^\]]*))?\*category_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_description);
        $meta_keywords = preg_replace('/(\[([^\]]*))?\*category_'.$x.'\*(([^\]]*)\])?/',$replace,$meta_keywords);
    }
    $PMDR->set('page_title',$title);
    $PMDR->set('meta_title',$meta_title);
    $PMDR->set('meta_description',$meta_description);
    $PMDR->set('meta_keywords',$meta_keywords);
    unset($meta_title,$meta_description,$meta_keywords,$depth,$replace,$location_depth);

    // Setup current category display data and get any category information
    foreach($path as $crumb) {
        $PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Categories')->getURL($crumb['id'],$crumb['friendly_url_path']),'text'=>$crumb['title']));
    }

    // Get only results within this category ID
    $search->category = $_GET['id'];

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
    if(!$categories_parsed = $PMDR->get('Cache')->get('categories_parsed_'.md5(URL).$PMDR->getLanguage('languageid'), 1800, 'categories_')) {
        $categories = $PMDR->get('Categories')->getChildren($_GET['id'], ($PMDR->getConfig('show_subs_number') > 0) ? 2 : 1, $PMDR->getConfig('show_subs_number'));
        foreach($categories as $key=>$category) {
            if(isset($_GET['location'])) {
                $categories[$key]['url'] = $PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path'],$location['id'],$location['friendly_url_path']);
            } else {
                $categories[$key]['url'] = $PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
            }
        }
        $categories_parsed = $PMDR->get('Categories')->parseForBrowsing($categories,(isset($active_record) ? $active_record['display_columns'] : null));
        $PMDR->get('Cache')->write('categories_parsed_'.md5(URL).$PMDR->getLanguage('languageid'),$categories_parsed,'categories_');
    }
    $categories_parsed_count = count($categories_parsed);
    unset($categories);
    $template_content->set('category_columns',$categories_parsed);
    unset($categories_parsed);

    if(!isset($_GET['location'])) {
        $template_content->set('show_indexes',$PMDR->getConfig('show_indexes'));
    }
    $template_content->set('show_category_description',$PMDR->getConfig('show_category_description'));

    // If we have no categories left, then we show the locations
    if($categories_parsed_count < 1) {
        if(!$locations_parsed = $PMDR->get('Cache')->get('locations_categories_parsed_'.md5(URL).$PMDR->getLanguage('languageid'), 1800, 'locations_')) {
            $locations = $PMDR->get('Locations')->getChildren($location['id'], ($PMDR->getConfig('loc_show_subs_number') > 0) ? 2 : 1,$PMDR->getConfig('loc_show_subs_number'));
            foreach($locations as $key=>$value) {
                $locations[$key]['url'] = $PMDR->get('Categories')->getURL($active_record['id'],$active_record['friendly_url_path'],$value['id'],$value['friendly_url_path']);
            }
            $locations_parsed = $PMDR->get('Locations')->parseForBrowsing($locations,((isset($location) AND $location['id'] != 1) ? $location['display_columns'] : null));
            $PMDR->get('Cache')->write('locations_categories_parsed_'.md5(URL).$PMDR->getLanguage('languageid'),$locations_parsed,'locations_');
        }
        unset($locations,$key,$value);
        if(count($locations_parsed) > 0 AND ($listings_count > 0 OR !$PMDR->getConfig('location_browse_children'))) {
            $template_content->set('location_columns',$locations_parsed);
        }
        unset($locations_parsed);
    }
}

if($listings_count > 0) {
    // If no listings, add nofollow/noindex META tag.
    if(trim($active_record['results_template_file']) != '' AND file_exists(PMDROOT.TEMPLATE_PATH.'blocks/'.$active_record['results_template_file'])) {
        $template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/'.$active_record['results_template_file']);
    } else {
        $template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/listing_results.tpl');
    }
    $template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
    $template_page_navigation->set('page',$pageArray);
    if(!empty($listings_results[0]['score'])) $template_results->set('score',true);
    if(!empty($listings_results[0]['zip_distance'])) $template_results->set('zip_distance',true);
    $template_results->set('search_within_link','<a href="'.BASE_URL.'/search.php?category='.$active_record['id'].'&location='.$location['id'].'">Search within '.$active_record['title'].'</a>');
    $template_results->set('page',$pageArray);
    ob_start();
    include(PMDROOT."/includes/template_listing_results.php");
    $listing_results = ob_get_contents();
    ob_end_clean();
    $template_results->set('listing_results',$listing_results);
    $template_results->set('page_navigation',$template_page_navigation);
}

if(!$categories_parsed_count AND !$listings_count AND !$locations_parsed) {
    $PMDR->set('meta_robots','noindex,follow');
}

$form_search_within = $PMDR->getNew('Form');
$form_search_within->action = BASE_URL.'/search_results.php';
$form_search_within->method = 'GET';
$form_search_within->addField('keyword','text',array('id'=>'search_within_keyword','label'=>$PMDR->getLanguage('public_contact_name'),'fieldset'=>'contact_us'));
if($active_record['id'] != 1) {
    $form_search_within->addField('category','hidden',array('id'=>'search_within_category','value'=>$active_record['id']));
}
if($location['id'] != 1) {
    $form_search_within->addField('location_id','hidden',array('id'=>'search_within_location_id','value'=>$location['id']));
}
$form_search_within->addField('submit','submit',array('id'=>'search_within_submit','label'=>$PMDR->getLanguage('public_general_search_search')));

$template_content->set('breadcrumb',array_slice($PMDR->get('breadcrumb'),1));
$template_content->set('listing_results',$template_results);
$template_content->set('form_search_within',$form_search_within);

include(PMDROOT.'/includes/template_setup.php');
?>