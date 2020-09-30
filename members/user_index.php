<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_index','user_listings','user_account'));

$PMDR->get('Plugins')->run_hook('user_index_begin');

$PMDR->get('Authentication')->authenticate();

// Get the user information based on the session ID
$user = $PMDR->get('User',$PMDR->get('Session')->get('user_id'));

$PMDR->get('Plugins')->run_hook('user_index_authenticated');

// If the action is to log out, log the user out, and add a log out message, then redirect
if($_GET['action'] == 'logout') {
    $PMDR->get('Authentication')->logout();
    $PMDR->addMessage('success', $PMDR->getLanguage('user_index_logged_out'));
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

if($user->requiredDataMissing() AND !isset($_SESSION['admin_id'])) {
    $_SESSION['user_account_update_required'] = true;
    redirect(BASE_URL.MEMBERS_FOLDER.'user_account.php?update_required=true');
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_general_my_account'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));

$user['profile_image_url'] = $user->getProfileImageURL();

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_index.tpl');
$template_content->set('user',$user);

$listings = $db->GetAll("SELECT id, friendly_url, title, status, impressions, search_impressions, impressions_weekly, website_clicks, emails, phone_views, email_views, shares FROM ".T_LISTINGS." l WHERE l.user_id=? ORDER BY date DESC LIMIT 5",array($user['id']));
foreach($listings AS &$listing) {
    $listing['url'] = $PMDR->get('Listings')->getURL($listing['id'],$listing['friendly_url']);
}
$template_content->set('listings',$listings);

$favorites = $db->GetAll("SELECT f.id AS favorite_id, l.id, l.title, l.friendly_url FROM ".T_FAVORITES." f INNER JOIN ".T_LISTINGS." l ON f.listing_id=l.id WHERE f.user_id=? LIMIT 5",array($user['id']));
foreach($favorites AS &$favorite) {
    $favorite['url'] = $PMDR->get('Listings')->getURL($favorite['id'],$favorite['friendly_url']);
}
$template_content->set('favorites',$favorites);

$invoices = $db->GetAll("SELECT i.id, i.date_due, i.total-IFNULL(SUM(t.amount),0.00) AS balance FROM ".T_INVOICES." i LEFT JOIN ".T_TRANSACTIONS." t ON i.id=t.invoice_id WHERE i.user_id=? AND i.status='unpaid' GROUP BY i.id ORDER BY date_due ASC LIMIT 5",array($user['id']));
foreach($invoices AS &$invoice) {
    $invoice['date_due']= $PMDR->get('Dates_Local')->formatDate($invoice['date_due']);
    $invoice['balance'] = format_number_currency($invoice['balance']);
}
$template_content->set('invoices_due',$invoices);

$messages = $db->GetAll("SELECT m.*,
IF(uto.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(uto.user_first_name,' ',uto.user_last_name)),''),uto.login))) AS user_to,
IF(ufrom.id = ?,'You',CONCAT(COALESCE(NULLIF(TRIM(CONCAT(ufrom.user_first_name,' ',ufrom.user_last_name)),''),ufrom.login))) AS user_from
FROM (SELECT * FROM ".T_MESSAGES." WHERE user_id_to=? OR user_id_from=? ORDER BY date_sent DESC LIMIT ?) AS m
LEFT JOIN ".T_USERS." uto ON uto.id=m.user_id_to
LEFT JOIN ".T_USERS." ufrom ON ufrom.id=m.user_id_from ORDER BY date_sent DESC",array($user['id'],$user['id'],$user['id'],$user['id'],5));
foreach($messages AS &$message) {
    $message['date_sent'] = $PMDR->get('Dates_Local')->formatDate($message['date_sent']);
}
$template_content->set('messages',$messages);

$reviews = $db->GetAll("SELECT r.id, r.listing_id, r.date, r.title, l.friendly_url, l.title AS listing_title, ra.rating FROM ".T_REVIEWS." r INNER JOIN ".T_LISTINGS." l ON r.listing_id=l.id INNER JOIN ".T_RATINGS." ra ON r.rating_id=ra.id WHERE r.user_id=? LIMIT 5",array($user['id']));
foreach($reviews AS &$review) {
    $review['date'] = $PMDR->get('Dates_Local')->formatDate($review['date']);
    $review['listing_url'] = $PMDR->get('Listings')->getURL($review['listing_id'],$review['friendly_url']);
    $review['rating_static'] = $PMDR->get('Ratings')->printRatingStatic($review['rating']);
}
$template_content->set('reviews',$reviews);

$searches = $db->GetAll("SELECT * FROM ".T_SEARCH_LOG." WHERE user_id=? AND keywords!=''",array($user['id']));
foreach($searches AS &$search) {
    $search['url'] = BASE_URL.'/search_results.php?'.http_build_query(unserialize($search['terms']));
    $search['date'] = $PMDR->get('Dates_Local')->formatDate($search['date']);
}
$template_content->set('searches',$searches);

$PMDR->get('Plugins')->run_hook('user_index_end');

include(PMDROOT.'/includes/template_setup.php');
?>