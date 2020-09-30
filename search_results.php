<?php
define('PMD_SECTION', 'public');

include('./defaults.php' );

// Load the language variables required on this page
$PMDR->loadLanguage(array('public_search_results','public_listing'));

// Set the page title
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_search_results'));

// Set the breadcrumb links
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/search.php','text'=>$PMDR->getLanguage('public_search_results_search')));
$PMDR->setAddArray('breadcrumb',array('link'=>'','text'=>$PMDR->getLanguage('public_search_results')));

$PMDR->get('Plugins')->run_hook('search_results_start');

// Initialize paging object and default settings
$paging = $PMDR->get('Paging');
$paging->linksNumber = 5;
$paging->setResultsNumber($PMDR->getConfig('count_directory'));
$paging->modRewrite = false;

// Load the template used for this page
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'search_results.tpl');

unset($_GET['submit_search']);

//Check to see if search restrictions are enabled
if($paging->currentPage == 1 AND intval($PMDR->getConfig('search_restriction_time')) > 0 AND (!isset($_GET['limit_key']) OR $_GET['limit_key'] != md5(rebuild_url(array(),array('limit_key')).SECURITY_KEY))) {
    $search_restricted_seconds = $PMDR->get('IP_Limits')->getSecondsDifference('search',$PMDR->getConfig('search_restriction_time'));
    if($search_restricted_seconds) {
        $search_restricted_seconds = intval($PMDR->getConfig('search_restriction_time')) - $search_restricted_seconds;
        if($search_restricted_seconds > 0) {
            $PMDR->addMessage('error',$PMDR->getLanguage('public_search_results_restriction',array($search_restricted_seconds)));
            redirect_url(BASE_URL);
        }
    } else {
        $PMDR->get('IP_Limits')->insert(array('type'=>'search'));
    }
    unset($search_restricted_seconds);
}

// If we have at least some search input, or we do not require any input
if(array_filter($_GET) OR !$PMDR->getConfig('search_require_values')) {
    // Get custom fields that are enabled as searchable
    $search_fields = $db->GetAll("SELECT * FROM ".T_FIELDS." WHERE search=1 ORDER BY ordering ASC");

    // Load all search terms into an array from the $_GET global variable
    $search_terms = array(
        'keyword'=>trim($_GET['keyword']),
        'location'=>trim($_GET['location']),
        'location_id'=>trim($_GET['location_id']),
        'category'=>trim($_GET['category']),
        'zip'=>trim($_GET['zip']),
        'zip_miles'=>trim($_GET['zip_miles']),
        'alpha'=>trim($_GET['alpha']),
        'sort_order'=>trim($_GET['sort_order']),
        'sort_direction'=>trim($_GET['sort_direction']),
    );

    // Load custom search terms into the $search_terms array from the $_GET global variable
    foreach((array) $search_fields as $field) {
        $search_terms['custom_'.$field['id']] = trim($_GET['custom_'.$field['id']]);
    }

    // Check Spelling
    if($PMDR->getConfig('spell_checker') AND !empty($search_terms['keyword']) AND ($spelling_suggestion = $PMDR->get('Spell_Checker')->getSuggested($search_terms['keyword']))) {
        $template_content->set('spelling_suggestion',$spelling_suggestion = $PMDR->get('Spell_Checker')->getSuggested($search_terms['keyword']));
        $template_content->set('spelling_suggestion_url',rebuild_url(array('keyword'=>$spelling_suggestion)));
    } else {
        $template_content->set('spelling_suggestion',false);
    }
    unset($spelling_suggestion);

    // Send keywords and other variables to template so we can paraphrase what was searched for
    $template_content->set('keyword',$search_terms['keyword']);
    // Add location data to template so we can paraphrase what was searched for
    if(!empty($search_terms['location_id']) AND $search_terms['location_id'] != 1 AND $location = $db->GetRow("SELECT id, title, friendly_url_path, description_short, description FROM ".T_LOCATIONS." WHERE id=?",array($search_terms['location_id']))) {
        $template_content->set('location',$location['title']);
        $template_content->set('location_description_short',$location['description_short']);
        $template_content->set('location_description',$location['description']);
        $location = ", ".$location['title'];
    }
    if(!empty($search_terms['location'])) {
        $template_content->set('location',$search_terms['location'].$location);
    }
    // Release $location since it is no longer used
    unset($location);
    // Add category data to template so we can paraphrase what was searched for
    if(isset($search_terms['category']) AND $search_terms['category'] != 1 AND is_numeric($search_terms['category']) AND $category = $PMDR->get('Categories')->getSearchResult($search_terms['category'])) {
        $template_content->set('category',$category['title']);
        $template_content->set('category_description_short',$category['description_short']);
        $template_content->set('category_description',$category['description']);
        $PMDR->set('active_category',array('id'=>$category['id'],'friendly_url_path'=>$category['friendly_url_path']));
    } else {
        $template_content->set('category',$search_terms['category']);
    }
    // Release $category since it is no longer used
    unset($category);

    // Add zip code information to the template so we can paraphrase what was searched for
    $template_content->set('zip',$search_terms['zip']);
    $template_content->set('zip_miles',$search_terms['zip_miles']);

    $template_content->set('search_results_order_url',rebuild_url(array(),array('sort_order','sort_direction'),true));

    $search_log_result = $db->GetRow("SELECT *, IF(date > DATE_SUB(NOW(), INTERVAL ".$PMDR->getConfig('cache_search_days')." DAY),1,0) AS cached FROM ".T_SEARCH_LOG." WHERE hash=?",array(md5(serialize(array_map('strtolower',$search_terms)))));

    // See if a search already exists based on hash, and making sure date isn't too old.
    if($PMDR->getConfig('cache') AND $PMDR->getConfig('cache_search_days') AND $search_log_result AND $search_log_result['cached']) {
        // If the cached search does have results, we set the result total and query for the listings
        if($search_log_result['results']) {
            $paging->setResultsNumber($PMDR->getConfig('count_search'));
            $paging->setTotalResults($listing_count = count(explode(',',$search_log_result['results'])));
            // Get the listing results
            $listings_results = $db->GetAll("SELECT * FROM ".T_LISTINGS." WHERE id IN(".$search_log_result['results'].") ORDER BY FIND_IN_SET(id,'".$search_log_result['results']."') LIMIT ".$paging->limit1.",".$paging->limit2);
        } else {
            $listing_count = 0;
        }
        $matching_categories = array();
        // If the cached search has category results we query to get the full results
        if($search_log_result['category_results']) {
            $matching_categories = $PMDR->get('Categories')->getByIDList($search_log_result['category_results']);
        }
        // If the cached search has location results we query to get the full results
        if($search_log_result['location_results']) {
            $matching_categories = $PMDR->get('Locations')->getByIDList($search_log_result['location_results']);
        }

        $template_content->set('search_results_order',$PMDR->get('Cleaner')->clean_output($search_terms['sort_order'].':'.urlencode($search_terms['sort_direction'])));
    } else {
        /** @var SearchListingFullText */
        $search = $PMDR->get('Search','ListingFullText');
        $search->listing_status = 'active';
        $search->findMatches = $PMDR->getConfig('search_category_matches');
        $search->categorySearchChildren = $PMDR->getConfig('search_category_children');
        $search->locationSearchChildren = $PMDR->getConfig('search_location_children');
        $search->zipAllowPartial = $PMDR->getConfig('search_allow_partial_zip');
        $search->likeShortWordMax = $PMDR->getConfig('search_short_word_max');
        $search->likeShortWordMin = $PMDR->getConfig('search_short_word_min');
        $search->booleanMode = $PMDR->getConfig('search_boolean_mode');
        $search->booleanMatchAll = $PMDR->getConfig('search_match_all');
        $search->likeShortWordRequireAll = $PMDR->getConfig('search_match_all');
        $search->booleanPartialMatch = $PMDR->getConfig('search_boolean_partial_match');
        $search->titleWeight = $PMDR->getConfig('search_title_weight');
        $search->filterStopWords = $PMDR->getConfig('search_filter_stop_words');
        $search->distanceType = $PMDR->getConfig('search_distance_type');
        $search->totalResultLimit = $PMDR->getConfig('search_total_limit');
        $search->stripList = explode("\n",$PMDR->getConfig('search_exclude_words'));
        if(isset($_GET['user_id'])) {
            $search->user_id = $_GET['user_id'];
        }
        if(isset($_GET['listing_id'])) {
            $search->listing_id = $_GET['listing_id'];
        }
        if(isset($_GET['pricing_id'])) {
            $search->pricing_id = array_filter(explode(',',$_GET['pricing_id']));
        }
        if($PMDR->getConfig('map_country_static') == 'United States' OR $PMDR->getConfig('map_country_static') == 'USA' OR $PMDR->getConfig('user_default_country') == 'United States') {
            $search->locationConvertCodes = true;
        }
        if($PMDR->getConfig('search_partial_zip_format')) {
            if($PMDR->getConfig('search_partial_zip_format') == 'uk') {
                $search->zipPartialRegex = '(^[A-Za-z]{1,2}[\d]{1,2}[A-Za-z]?)\s?[\d][A-Za-z]{2}?$';
            } elseif($PMDR->getConfig('search_partial_zip_format') == 'first') {
                $search->zipPartialRegex = '(^.*)?\s.*$';
            } else {
                $search->zipPartialRegex = '(^.{'.$PMDR->getConfig('search_partial_zip_format').'}).*$';
            }
        }
        $search->zip = $_GET['zip'];
        $search->zipMiles = $_GET['zip_miles'];
        $search->autoSortByDistance = $PMDR->getConfig('listing_search_radius_autosort');

        // Set the sort order based on the admin area settings
        if($PMDR->getConfig('listing_search_order_1') != '') {
            $sort_order1 = explode(':',$PMDR->getConfig('listing_search_order_1'));
            if($sort_order1[0] == 'random') {
                // We fill index 1 so it is not seen as blank
                $sort_order1[0] = 'RAND(\''.session_id().'\')';
                $sort_order1[1] = 'ASC';
            }
            $search->sortBy = array($sort_order1[0]=>$sort_order1[1]);
        } else {
            $search->sortBy = array();
        }
        if($PMDR->getConfig('listing_search_order_2') != '') {
            $sort_order2 = explode(':',$PMDR->getConfig('listing_search_order_2'));
            if($sort_order2[0] == 'random') {
                // We fill index 1 so it is not seen as blank
                $sort_order2[0] = 'RAND(\''.session_id().'\')';
                $sort_order2[1] = 'ASC';
            }
            $search->sortBy = array_merge($search->sortBy,array($sort_order2[0]=>$sort_order2[1]));
        }
        unset($sort_order1);
        unset($sort_order2);

        if(isset($_GET['sort_order']) AND !empty($_GET['sort_order'])) {
            $search->autoSortByDistance = false;
            if(isset($_GET['sort_direction']) AND $_GET['sort_direction'] == 'DESC') {
                $search->sortBy = array($_GET['sort_order']=>'DESC');
            } else {
                $search->sortBy = array($_GET['sort_order']=>'ASC');
            }
        }

        // These don't get set when results are cached.
        $template_content->set('search_results_order',$PMDR->get('Cleaner')->clean_output(key($search->sortBy).':'.urlencode(current($search->sortBy))));

        // If a zip code is not entered we use regex to try to determine format and then check the $_GET['location'] field for a zip code
        if(empty($_GET['zip'])) {
            $zip_regex = array (
                'usa'=>'^\d{3,5}$',
                'uk'=>'^[A-Za-z]{1,2}[\d]{1,2}([A-Za-z])?\s?([\d][A-Za-z]{2})?$',
                'canada'=>'^[ABCEGHJKLMNPRSTVXY]{1}\d{1}[A-Z]{1} *\d{1}[A-Z]{1}\d{1}$'
            );
            foreach($zip_regex as $regex) {
                // If we find a zip code in the $_GET['location'] variable then set it as the zip code for searching
                if($zip_regex_count = preg_match('/'.$regex.'/',$_GET['location'])) {
                    $search->zip = $_GET['location'];
                    break;
                }
            }
        }
        // If a location ID is set, set it as the location to search for, else we add the location string as the location to search for
        if(isset($_GET['location_id']) AND !empty($_GET['location_id'])) {
            $search->location_id = $_GET['location_id'];
            $PMDR->get('Statistics')->insert('location_impression_search',$_GET['location_id']);
        }
        if(!$zip_regex_count AND isset($_GET['location'])) {
            $search->location = $_GET['location'];
        }

        // Set the category from $_GET['category']
        $search->category = $_GET['category'];

        // Log a search impression for the category if one is specifically selected.
        if(isset($_GET['category']) AND is_numeric($_GET['category'])) {
            $PMDR->get('Statistics')->insert('category_impression_search',$_GET['category']);
        }

        // Set the keywords from $_GET['keywords'] and trim and whitespace from the keywords
        $search->keywords = trim($_GET['keyword']);

        // Add alpha search to query if a letter is passed in
        if(isset($_GET['alpha']) AND $_GET['alpha'] != '') {
            // Add this as a keyword to the template so it shows in the "search for" string
            $template_content->set('keyword',$_GET['alpha']);
            if($_GET['alpha'] == '0-9') {
                $search->addAdditionalField('l.title',$_GET['alpha'],1,'{search_field1} REGEXP \'^[[:digit:]].*$\'');
            } else {
                $search->addAdditionalField('l.title',$_GET['alpha'],1,'{search_field1} LIKE \'{search_value1}%\'');
            }
        }

        // Add any custom fields which are searchable to the search query
        foreach((array) $search_fields as $field) {
            $search->addAdditionalField('custom_'.$field['id'],(isset($_GET['custom_'.$field['id']]) ? $_GET['custom_'.$field['id']] : $_GET['keyword']),(isset($_GET['custom_'.$field['id']]) ? 1 : 0));
        }

        $PMDR->get('Plugins')->run_hook('search_results_before_search');

        // Get the search results
        $listings_results = $search->getResults();
        // Get the number of results
        $listing_count = count($listings_results);
        // Set the number of results to show per page
        $paging->setResultsNumber($PMDR->getConfig('count_search'));
        // Send the number of results to the paging class to properly show the page numbers
        $paging->setTotalResults($listing_count);
        // Get any matching categories from the search class
        $matching_categories = $search->matchingCategories;
        // Get any matching locations from the search class
        $matching_locations = $search->matchingLocations;
        // Set the active location because the search class may have found a location ID
        if(!is_null($search->location_id)) {
            if($location = $db->GetRow("SELECT id, friendly_url_path FROM ".T_LOCATIONS." WHERE id=?",array($search->location_id))) {
                $PMDR->set('active_location',$location);
            }
            unset($location);
        }

        // Get the found listing ids so we can put them in the DB
        $listing_ids = array();
        foreach($listings_results as $listing) {
            $listing_ids[] = $listing['id'];
        }
        unset($listing);

        // We have extraced all of the ID's for the database, now splice out only what we need to display for the first page
        $listings_results = array_splice($listings_results,$paging->limit1,$paging->limit2);

        // Get the category ids for the search cache
        $category_ids = array();
        foreach($search->matchingCategories as $category) {
            $category_ids[] = $category['id'];
        }

        // Get the location ids for the search cache
        $location_ids = array();
        foreach($search->matchingLocations as $location) {
            $location_ids[] = $location['id'];
        }

        // Collect search data for the cache, lower case the terms for the hash or it could lead to different results
        $search_data = array(
            'user_id'=>$PMDR->get('Session')->get('user_id'),
            'ip'=>get_ip_address(),
            'results'=>implode(',',$listing_ids),
            'category_results'=>implode(',',$category_ids),
            'location_results'=>implode(',',$location_ids),
            'keywords'=>trim($_GET['keyword'].(!is_numeric($_GET['location']) ? ' '.trim($_GET['location']) : '')),
            'execution_time'=>$search->executionTime,
            'terms'=>serialize($search_terms),
            'hash'=>md5(serialize(array_map('strtolower',$search_terms)))
        );

        unset($listing_ids);
        unset($category_ids);
        unset($location_ids);
        unset($search);
    }

    // If this searchers IP does not match the already existing one, we increment it as a new search and log the new IP
    if($paging->currentPage == 1) {
        $PMDR->get('Session')->set('last_search',URL);
        if(!$search_log_result) {
            $db->Execute("INSERT INTO ".T_SEARCH_LOG." (user_id,date,ip,results,category_results,location_results,count,keywords,execution_time,terms,hash) VALUES (?,NOW(),?,?,?,?,1,?,?,?,?)",$search_data);
        } elseif($search_log_result['ip'] != get_ip_address()) {
            $db->Execute("UPDATE ".T_SEARCH_LOG." SET count=count+1, ip=? WHERE id=?",array(get_ip_address(),$search_log_result['id']));
        }
    }

    $query_string = $_GET;
    unset($query_string['keyword']);
    unset($query_string['submit']);
    unset($query_string['category']);

    // For any of the matching categories set their URL
    if(is_array($matching_categories)) {
        foreach($matching_categories as $key=>$value) {
            $query_string['category'] = $value['id'];
            $query_string['limit_key'] = md5(BASE_URL.'/search_results.php?'.http_build_query($query_string).SECURITY_KEY);
            $matching_categories[$key]['url'] = 'search_results.php?'.http_build_query($query_string);
            $matching_categories[$key]['path'] = $this->PMDR->get('Categories')->getPath($value['id']);
            $matching_categories[$key]['path_display'] = $PMDR->get('Categories')->getPathDisplay($value,' ',false);
        }
        unset($query_string['category']);
    }
    // For any of the matching locations set their URL
    if(is_array($matching_locations)) {
        foreach($matching_locations as $key=>$value) {
            unset($query_string['location']);
            $query_string['location_id'] = $value['id'];
            $query_string['limit_key'] = md5(BASE_URL.'/search_results.php?'.http_build_query($query_string).SECURITY_KEY);
            $matching_locations[$key]['url'] = 'search_results.php?'.http_build_query($query_string);
            $matching_locations[$key]['path'] = $this->PMDR->get('Locations')->getPath($value['id']);
            $matching_locations[$key]['path_display'] = $PMDR->get('Locations')->getPathDisplay($value,' ',false);
        }
    }
    unset($query_string);

    // If we found listings continue, if not show a no results error message
    if($listing_count) {
        if($PMDR->getConfig('map_display_type') == 'dynamic' AND $PMDR->getConfig('map_type') == 'google') {
            $map = $PMDR->get('Map');
            $map->mapID = 'map_search';
            foreach($listings_results as $key=>$listing) {
                if(!$listing['map_allow']) {
                    continue;
                }
                $locations = $PMDR->get('Locations')->getPath($listing['location_id']);
                foreach($locations as $location_key=>$value) {
                    $listing['location_'.($location_key+1)] = $value['title'];
                }
                unset($locations,$location_key,$value);

                $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
                $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
                $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];

                $map_popup = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/search_map_popup.tpl');
                $map_popup->set('title',$listing['title']);
                $map_popup->set('url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
                $map_popup->set('address',$PMDR->get('Locations')->formatAddress($listing['listing_address1'],$listing['listing_address2'],$map_city,$map_state,$map_country,$listing['listing_zip']));

                if($listing['logo_allow'] AND file_exists(LOGO_THUMB_PATH.$listing['id'].'.'.$listing['logo_extension'])) {
                    $map_popup->set('logo_thumb_url',get_file_url_cdn(LOGO_THUMB_PATH.$listing['id'].'.'.$listing['logo_extension']));
                }

                if($listing['latitude'] != '0.0000000000' AND $listing['longitude'] != '0.0000000000') {
                    $listings_results[$key]['marker'] = true;
                    $map->addMarkerByCoords($listing['longitude'], $listing['latitude'], $listing['title'],$map_popup->render());
                }
            }
            unset($listing,$key);

            if(count($map->markers)) {
                $PMDR->loadJavascript($map->getHeaderJS());
                $PMDR->loadJavascript($map->getMapJS());
                $PMDR->setAdd('javascript_onload','mapOnLoad();');
                $map_output = $map->getMap();
            }
            $template_content->set('map',$map_output);
            unset($map,$map_output,$map_popup);
        }

        // Set up the paging template
        $pageArray = $paging->getPageArray();
        $template_page_navigation = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
        $template_page_navigation->set('page',$pageArray);

        // Get the listing results template
        $template_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/listing_results.tpl');
        if(!empty($listings_results[0]['score'])) $template_results->set('score',true);
        if(!empty($listings_results[0]['zip_distance'])) $template_results->set('zip_distance',true);
        $template_results->set('page',$pageArray);
        // Looop through results and capture the output to insert into the listing_results.tpl template
        ob_start();
        include(PMDROOT."/includes/template_listing_results.php");
        $listing_results = ob_get_contents();
        ob_end_clean();
        $template_results->set('listing_results',$listing_results);
        $template_results->set('page_navigation',$template_page_navigation);
    } else {
        $template_content->set('error_message',$PMDR->getLanguage('public_search_results_no_results'));
    }
} else {
    $template_content->set('error_message',$PMDR->getLanguage('public_search_results_blank_error'));
}

// Send remaining details to the template to control the data display
$template_content->set('search_display_all',$PMDR->getConfig('search_display_all'));
$template_content->set('listing_count', $listing_count);
$template_content->set('listing_results',$template_results);
$template_content->set('matching_categories',$matching_categories);
$template_content->set('matching_locations',$matching_locations);

$PMDR->get('Plugins')->run_hook('search_results_end');

include(PMDROOT.'/includes/template_setup.php');
?>