<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_listing','public_listing_reviews','email_templates'));

// Load template file
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/listing_reviews.tpl');

$PMDR->set('page_header',null);

$PMDR->get('Sharing')->loadJavascript();

$PMDR->set('og:type','object');

if($ratings_categories = $db->GetAll("SELECT id, title FROM ".T_RATINGS_CATEGORIES)) {
    $categories_sql = '';
    $categories_sql_avg = '';
    foreach($ratings_categories AS &$category) {
        $categories_sql .= 'rt.category_'.$category['id'].',';
        $categories_sql_avg .= 'AVG(rt.category_'.$category['id'].') AS category_'.$category['id'].',';
    }
}
// If a specific review is being viewed retreive it from the database
if(isset($_GET['review_id'])) {
    // Get the review from the database
    $reviews = $db->GetAll("SELECT r.*, rt.rating, $categories_sql COALESCE(NULLIF(TRIM(u.user_first_name),''),u.login) AS user_name_formatted FROM ".T_REVIEWS." r LEFT JOIN ".T_RATINGS." rt ON r.rating_id=rt.id LEFT JOIN ".T_USERS." u ON u.id=r.user_id WHERE r.id=? AND r.status='active'",array($_GET['review_id']));
    // Get the listing the review belongs to from the database
    if(!$listing = $PMDR->get('Listings')->getListingChildPage($reviews[0]['listing_id'])) {
        $PMDR->get('Error',404);
    }
    $template_content->set('review_id',$reviews[0]['id']);
    // Get the total count of reviews for this listing
    $total_reviews = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_REVIEWS." WHERE listing_id=? AND status='active'",array($listing['id']));
} else {
    // Get the listing if an ID is passed to this file
    if(!$listing = $PMDR->get('Listings')->getListingChildPage($_GET['id'])) {
        $PMDR->get('Error',404);
    }
    // Get the total count of reviews for this listing
    $total_reviews = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_REVIEWS." WHERE listing_id=? AND status='active'",array($listing['id']));

    // Set up paging
    $paging = $PMDR->get('Paging');
    $paging->setTotalResults($total_reviews);

    // Get the reviews from the database
    $reviews = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS r.*, rt.rating, $categories_sql COALESCE(NULLIF(TRIM(u.user_first_name),''),u.login) AS user_name_formatted FROM ".T_REVIEWS." r LEFT JOIN ".T_RATINGS." rt ON r.rating_id=rt.id LEFT JOIN ".T_USERS." u ON u.id=r.user_id WHERE r.listing_id=? AND r.status='active' GROUP BY r.id ORDER BY id DESC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    // Set the total number of reviews found for paging purposes
    $paging->setTotalResults($db->FoundRows());
    // Get the page array
    $page_array = $paging->getPageArray();
    // Set up the paging template
    $template_page_navigation = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'blocks/page_navigation.tpl');
    $template_page_navigation->set('page',$page_array);
    $template_content->set('page_navigation',$template_page_navigation);
}

if(!$listing['reviews_allow']) {
    $PMDR->get('Error',404);
}

// If no reviews are found, return a 404 error.
if(!$reviews) {
    $PMDR->get('Error',404);
}

if($listing['logo_url'] = get_file_url_cdn(LOGO_PATH.$listing['id'].'.'.$listing['logo_extension'])) {
    $PMDR->set('meta_image',$listing['logo_url']);
}

$title = coalesce($PMDR->getConfig('title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));
$meta_keywords = coalesce($PMDR->getConfig('meta_keywords_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));

if(isset($_GET['review_id'])) {
    $meta_title = coalesce($PMDR->getConfig('meta_title_listing_review_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));
    $meta_description = coalesce($PMDR->getConfig('meta_description_listing_review_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));
    $meta_replace = array('title'=>$PMDR->getLanguage('public_listing_reviews'),'listing_title'=>$listing['title'],'review_title'=>$reviews[0]['title'],'review'=>$reviews[0]['review']);
    foreach($meta_replace AS $find=>$replace) {
        $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
        $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
    }
} else {
    $meta_title = coalesce($PMDR->getConfig('meta_title_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));
    $meta_description = coalesce($PMDR->getConfig('meta_description_listing_subpage_default'),$listing['title'].' '.$PMDR->getLanguage('public_listing_reviews'));
    $meta_replace = array('title'=>$PMDR->getLanguage('public_listing_reviews'),'listing_title'=>$listing['title']);
    foreach($meta_replace AS $find=>$replace) {
        $title = str_replace('*'.$find.'*',$replace,$title);
        $meta_title = str_replace('*'.$find.'*',$replace,$meta_title);
        $meta_description = str_replace('*'.$find.'*',$replace,$meta_description);
        $meta_keywords = str_replace('*'.$find.'*',$replace,$meta_keywords);
    }
}
$PMDR->set('page_title',$title);
$template_content->set('title',$title);
$PMDR->set('meta_title',$meta_title);
$PMDR->set('meta_description',$meta_description);
$PMDR->set('meta_keywords',$meta_keywords);

// Set the breakcrumb text and URLs
$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']),'text'=>$listing['title']));
$PMDR->setAddArray('breadcrumb',array('link'=>$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/reviews.html','listing_reviews.php'),'text'=>$PMDR->getLanguage('public_listing_reviews')));

// Loop through the reviews to format and set up the data
$template_content_reviews = array();
foreach($reviews as $key=>$review) {
    $template_content_reviews[$key] = $PMDR->get('Reviews')->getReviewTemplate($review,$ratings_categories,isset($_GET['review_id']),$listing['user_id']);
    $PMDR->get('Fields_Groups')->addToTemplate($template_content_reviews[$key],$review,'reviews',$listing['primary_category_id']);
    if($key == 0) {
        $template_content_reviews[$key]->set('javascript',true);
    }
    $template_content_reviews[$key] = $template_content_reviews[$key]->render();
}

// Get the count calculations for 1-5 star reviews
$reviews_counts = $db->GetAssoc("SELECT rating, COUNT(rt.id) AS count, ((100*COUNT(rt.id))/".$total_reviews.") AS meter_width FROM ".T_RATINGS." rt INNER JOIN ".T_REVIEWS." r ON r.rating_id=rt.id WHERE r.listing_id=? AND r.status='active' GROUP BY rating",array($listing['id']));
$review_averages = $db->GetRow("SELECT $categories_sql_avg AVG(rt.rating) AS rating FROM ".T_RATINGS." rt INNER JOIN ".T_REVIEWS." r ON r.rating_id=rt.id WHERE rt.listing_id=? AND r.status='active' GROUP BY rt.listing_id",array($listing['id']));
foreach($ratings_categories AS &$category) {
    $category['average'] = $review_averages['category_'.$category['id']];
    $category['average_static'] = $PMDR->get('Ratings')->printRatingStatic($review_averages['category_'.$category['id']]);
}
$template_content->set('categories',$ratings_categories);
unset($ratings_categories,$category);

// Process the count calculations
$total = 0;
for($x=1; $x < 6; $x++) {
    // Set to 0 if none were found
    $reviews_counts[$x]['count'] = ($reviews_counts[$x]['count']) ? $reviews_counts[$x]['count'] : 0;
    // Calculate the total
    $total += $x*$reviews_counts[$x]['count'];
    // Set the meter width
    $reviews_counts[$x]['meter_width'] = ($reviews_counts[$x]['meter_width']) ? $reviews_counts[$x]['meter_width'] : 0;
}

// Set the variables in the template file
$template_content->set('login_url',BASE_URL.MEMBERS_FOLDER.'index.php?from='.urlencode_url(URL));
$template_content->set('logged_in',$PMDR->get('Session')->get('user_id'));
$template_content->set('reviews',$template_content_reviews);
$template_content->set('reviews_counts',$reviews_counts);
$template_content->set('average',($total/$total_reviews));
$template_content->set('total_reviews',$total_reviews);
$template_content->set('average_stars',$PMDR->get('Ratings')->printRatingStatic($total/$total_reviews));
$template_content->set('listing_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']));
$template_content->set('reviews_add_url',$PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url'],'','/add-review.html','listing_reviews_add.php'));
$template_content->set('listing',$listing);

include(PMDROOT.'/includes/template_setup.php');
?>