<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

$PMDR->loadLanguage(array('public_classifieds_categories'));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/classifieds_categories.php','text'=>$PMDR->getLanguage('public_classifieds_categories')));

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

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'classifieds_categories.tpl');
$template_content->cache_id = 'classifieds_categories_'.md5(URL).$PMDR->getLanguage('languageid');

// Handle current category
if(!empty($_GET['id']) AND $_GET['id'] != 1) {
    // Category gets an impression added to the database
    $PMDR->get('Statistics')->insert('classifieds_category_impression',$_GET['id']);
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
        $related_categories = $PMDR->get('Categories')->getRelated($active_record['id']);
        foreach($related_categories AS &$related_category) {
            $related_category['url'] = $PMDR->get('Categories')->getURL($related_category['id'],$related_category['friendly_url_path']);
        }
        $template_content->set('related_categories',$related_categories);
        unset($related_categories,$related_category);

        $PMDR->get('Fields_Groups')->addToTemplate($template_content,$active_record,'classifieds_categories');
    }
    $title = coalesce($PMDR->getConfig('title_category_default'),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
    $meta_title = coalesce($active_record['meta_title'],$PMDR->getConfig('meta_title_category_default'),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
    $meta_description = coalesce($active_record['meta_description'],$PMDR->getConfig('meta_description_category_default'),$active_record['description_short'],strip_tags($active_record['description']),$PMDR->get('Categories')->getPathDisplay(array_reverse($path),' ',false));
    $meta_keywords = coalesce($active_record['meta_keywords'],$PMDR->getConfig('meta_keywords_category_default'),$active_record['keywords'],$PMDR->get('Categories')->getPathDisplay($path,', ',false));

    $depth = $PMDR->get('Categories')->getDepth();
    $title = $PMDR->get('Categories')->replaceVariable('category',$active_record['title'],$title);

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
            $categories[$key]['url'] = $PMDR->get('Categories')->getURL($category['id'],$category['friendly_url_path']);
        }
        $categories_parsed = $PMDR->get('Categories')->parseForBrowsing($categories,(isset($active_record) ? $active_record['display_columns'] : null));
        $PMDR->get('Cache')->write('categories_parsed_'.md5(URL).$PMDR->getLanguage('languageid'),$categories_parsed,'categories_');
    }
    $categories_parsed_count = count($categories_parsed);
    unset($categories);
    $template_content->set('category_columns',$categories_parsed);
    unset($categories_parsed);

    $template_content->set('show_indexes',$PMDR->getConfig('show_indexes'));
    $template_content->set('show_category_description',$PMDR->getConfig('show_category_description'));
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

$form_search_within = $PMDR->getNew('Form');
$form_search_within->action = BASE_URL.'/search_results.php';
$form_search_within->method = 'GET';
$form_search_within->addField('keyword','text',array('id'=>'search_within_keyword','label'=>$PMDR->getLanguage('public_contact_name'),'fieldset'=>'contact_us'));
$form_search_within->addField('category','hidden',array('id'=>'search_within_category','value'=>$active_record['id']));
$form_search_within->addField('location_id','hidden',array('id'=>'search_within_location_id','value'=>$location['id']));
$form_search_within->addField('submit','submit',array('id'=>'search_within_submit','label'=>$PMDR->getLanguage('public_general_search_search')));

$template_content->set('breadcrumb',array_slice($PMDR->get('breadcrumb'),1));
$template_content->set('listing_results',$template_results);
$template_content->set('form_search_within',$form_search_within);

include(PMDROOT.'/includes/template_setup.php');
?>