<?php
if(!defined('IN_PMD')) exit();

$PMDR->get('Plugins')->run_hook('template_listing_results_begin');

$map_marker = 1;

foreach($listings_results as $key=>$listing) {
    $PMDR->get('Plugins')->run_hook('template_listing_results_loop_start');

    if(trim($listing['template_file_results']) != '' AND $PMDR->get('Templates')->path('blocks/'.$listing['template_file_results'])) {
        $template_content_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/'.$listing['template_file_results']);
    } else {
        $template_content_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/listing_results_default.tpl');
    }

    $template_content_results->set('key',$key);
    $template_content_results->set('odd',($key % 2));
    if(value($listing,'marker')) {
        $template_content_results->set('map_marker',$map_marker);
        $map_marker++;
    }
    $template_content_results->set('link',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
    $template_content_results->set('id', $listing['id']);
    $template_content_results->set('user_id', $listing['user_id']);
    $template_content_results->set('title', $listing['title']);
    $template_content_results->set('description', Strings::limit_words($listing['description'],$listing['description_size']));

    $description = Strings::limit_words($listing['description'],$listing['description_size']);
    // Make sure the description is truncated to the allowed length.  nl2br is done afterwards or else <br> would get counted
    if($listing['short_description_size']) {
        if(!empty($listing['description_short'])) {
            $template_content_results->set('short_description', nl2br(Strings::limit_words($listing['description_short'],min($listing['short_description_size'],$PMDR->getConfig('search_short_desc_size')),'...')));
        } elseif($listing['description_size'] AND !empty($listing['description'])) {
            $template_content_results->set('short_description', nl2br(Strings::limit_words(strip_tags($description,'<br><br/>'),min($listing['short_description_size'],$PMDR->getConfig('search_short_desc_size')),'...')));
        }
    } else {
         $template_content_results->set('short_description',null);
    }
    $template_content_results->set('description',$description);
    unset($description);

    $template_content_results->set('new',$PMDR->get('Listings')->ifNew($listing['date']));
    $template_content_results->set('updated',$PMDR->get('Listings')->ifUpdated($listing['date_update']));
    $template_content_results->set('hot',$PMDR->get('Listings')->ifHot($listing['rating']));
    $template_content_results->set('featured',$listing['featured']);

    if($listing['ratings_allow']) {
        $template_content_results->set('rating_count',$listing['votes']);
        $template_content_results->set('rating', $PMDR->get('Ratings')->printRatingStatic($listing['rating']));
    }
    $template_content_results->set('date', $PMDR->get('Dates_Local')->formatDateTime($listing['date']));
    $template_content_results->set('date_update', $PMDR->get('Dates_Local')->formatDateTime($listing['date_update']));
    $template_content_results->set('zip_distance', value($listing,'zip_distance'));

    if(!empty($listing['score']) AND $listings_results[0]['score']) {
        // Turn into percentage
        $template_content_results->set('score', round(($listing['score'] / $listings_results[0]['score']) * 100,0));
    }

    if($listing['phone_allow']) {
        $template_content_results->set('phone', $listing['phone']);
    }

    if($listing['fax_allow']) {
        $template_content_results->set('fax', $listing['fax']);
    }

    $locations = $PMDR->get('Locations')->getPath($listing['location_id']);
    foreach($locations as $location_key=>$value) {
        $listing['location_'.($location_key+1)] = $value['title'];
        $template_content_results->set('location_'.($location_key+1),$value['title']);
    }
    unset($locations,$location_key,$value);

    $map_country = $PMDR->getConfig('map_country_static') != '' ? $PMDR->getConfig('map_country_static') : $listing[$PMDR->getConfig('map_country')];
    $map_state = $PMDR->getConfig('map_state_static') != '' ? $PMDR->getConfig('map_state_static') :  $listing[$PMDR->getConfig('map_state')];
    $map_city = $PMDR->getConfig('map_city_static') != '' ? $PMDR->getConfig('map_city_static') : $listing[$PMDR->getConfig('map_city')];

    if($listing['address_allow']) {
        $template_content_results->set("location_text_1", $listing['location_text_1']);
        $template_content_results->set("location_text_2", $listing['location_text_2']);
        $template_content_results->set("location_text_3", $listing['location_text_3']);
        $template_content_results->set('address', $listing['listing_address1']."\n".($listing['listing_address2'] != '' ? $listing['listing_address2']."\n" : '').($map_city != '' ? $map_city.', ' : '').($map_state != '' ? $map_state.' ' : '').($listing['zip_allow'] ? ' '.$listing['listing_zip'] : '')."\n".$map_country);
        $template_content_results->set('address_line1', $listing['listing_address1']);
        $template_content_results->set('address_line2', $listing['listing_address2']);
        $template_content_results->set('latitude',$listing['latitude']);
        $template_content_results->set('longitude',$listing['longitude']);
    }
    unset($map_country,$map_state,$map_city);

    if($listing['zip_allow']) {
        $template_content_results->set('zip', $listing['listing_zip']);
    }

    $template_content_results->set('user_email', value($listing,'user_email'));
    $template_content_results->set('login', value($listing,'login'));
    $template_content_results->set('email', "<a href=mailto:$listing[mail]>$listing[mail]</a>");

    if($PMDR->getConfig('js_click_counting')) {
        $template_content_results->set('www_url',$listing['www']);
        $template_content_results->set('www_javascript','onclick="$.ajax({async: false, cache: false, timeout: 30000, data: ({action: \'add_click\', id: '.$listing['id'].' })});"');
    } else {
        if(MOD_REWRITE) {
            $template_content_results->set('www_url',BASE_URL.'/out-'.$listing['id']);
        } else {
            $template_content_results->set('www_url',BASE_URL.'/out.php?listing_id='.$listing['id']);
        }
    }

    $template_content_results->set('www', $listing['www']);

    if($listing['logo_allow'] AND file_exists(LOGO_THUMB_PATH.$listing['id'].'.'.$listing['logo_extension'])) {
        $template_content_results->set('logo_url',get_file_url_cdn(LOGO_THUMB_PATH.$listing['id'].'.'.$listing['logo_extension']));
    } elseif($listing['www_screenshot_allow'] AND file_exists(SCREENSHOTS_PATH.$listing['id'].'-small.jpg')) {
        $template_content_results->set('logo_url',get_file_url_cdn(SCREENSHOTS_PATH.$listing['id'].'-small.jpg'));
    } elseif($PMDR->get('Templates')->path('images/noimage.png')) {
        $template_content_results->set('logo_url',$PMDR->get('Templates')->urlCDN('images/noimage.png'));
    }

    $PMDR->get('Fields_Groups')->addToTemplate($template_content_results,$listing,'listings',$listing['primary_category_id']);

    $PMDR->get('Plugins')->run_hook('template_listing_results_loop_end');

    $template_content_results->set('categories',$PMDR->get('Listings')->getCategories($listing['id'],true));

    echo $template_content_results->render();

    if($PMDR->getConfig('search_ad_code') AND $PMDR->getConfig('search_ad_code_frequency') > 0 AND ($key+1) % $PMDR->getConfig('search_ad_code_frequency') == 0) {
        $template_content_results = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/listing_results_ad.tpl');
        $template_content_results->set('ad_code',$PMDR->getConfig('search_ad_code'));
        echo $template_content_results->render();
    }

    $PMDR->get('Statistics')->insert('listing_search_impression',$listing['id']);
}

$PMDR->get('Plugins')->run_hook('template_listing_results_end');

unset($listings_results,$key,$listing);
?>