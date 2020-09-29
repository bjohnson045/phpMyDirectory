<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_index'));

$PMDR->get('Authentication')->authenticate();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_index.tpl');

$PMDR->get('Plugins')->run_hook('admin_index');

if(isset($_GET['debug_mode'])) {
    if(value($_GET,'debug_mode') == 'true') {
        $PMDR->get('Debug')->enable();
    } else {
        $PMDR->get('Debug')->disable();
    }
 }

if(isset($_GET['action']) AND $_GET['action'] == 'logout') {
    $PMDR->addMessage('success',sprintf($PMDR->getLanguage('messages_logout'),$_SESSION['admin_login']));
    $PMDR->get('Authentication')->logout();
    redirect('index.php');
}

if(MOD_REWRITE AND !file_exists(PMDROOT.'/.htaccess')) {
    if(!file_exists(PMDROOT.'/htaccess.txt') OR !rename(PMDROOT.'/htaccess.txt',PMDROOT.'/.htaccess')) {
        $PMDR->addMessage('notice','Friendly URLs are enabled, but the .htaccess file is not found.  Upload the .htaccess file or rename the htaccess.txt file to .htaccess');
    }
}

if($db->GetOne("SELECT COUNT(*) FROM ".T_LANGUAGE_PHRASES." WHERE updated=1")) {
    $PMDR->addMessage('warning','One or more language phrases has changed due to a software upgrade.  <a href="./admin_phrases.php?options[]=updated">Review and confirm these changes</a>. <br>Editing a phrase will unmark it as updated.  You may also accept all updated phrases in bulk by <a href="admin_phrases.php?action=clear_updated">clicking here</a>.');
}

// We do ABS(day) here in case not all CRON jobs run daily so it won't cause a false alarm
if($db->GetOne("SELECT COUNT(*) FROM ".T_CRON." WHERE last_run_date < DATE_SUB(NOW(), INTERVAL 4+ABS(day) DAY) AND last_run_date IS NOT NULL")) {
    $PMDR->addMessage('error','Scheduled tasks have not run in the last 5 days.  Please ensure the /files/temp/ folder is writable.  If it continues to fail,  please setup your cron job to run hourly using one of the following:<br /><br />
    <b>Using PHP:</b><br /> php -q '.PMDROOT.'/cron.php '.md5(SECURITY_KEY).'<br /><br />
    <b>Using GET:</b><br /> GET '.BASE_URL.'/cron.php?c='.md5(SECURITY_KEY));
}

$template_content->set('total_users',$db->GetOne("SELECT COUNT(*) FROM ".T_USERS));
$template_content->set('users_unconfirmed_email',$db->GetOne("SELECT COUNT(*) FROM ".T_USERS." u, ".T_USERS_GROUPS_LOOKUP." ugl WHERE u.id=ugl.user_id AND ugl.group_id=5"));
$template_content->set('users_without_order',$db->GetOne("SELECT COUNT(*) FROM ".T_USERS." u LEFT JOIN ".T_ORDERS." o ON u.id=o.user_id WHERE o.user_id IS NULL"));
$template_content->set('users_this_week',$db->GetOne("SELECT COUNT(*) FROM ".T_USERS." WHERE created > DATE_SUB(NOW(),INTERVAL 7 DAY)"));
$template_content->set('users_pending_contact_requests',$db->GetOne("SELECT COUNT(*) FROM ".T_CONTACT_REQUESTS." WHERE status='pending'"));
$order_statuses = array(
    'active'=>0,
    'pending'=>0,
    'completed'=>0,
    'suspended'=>0,
    'canceled'=>0,
    'fraud'=>0
);
$template_content->set('order_statuses',array_merge($order_statuses,$db->GetAssoc("SELECT status, COUNT(*) as count FROM ".T_ORDERS." GROUP BY status")));
$template_content->set('order_cancellations',$db->GetOne("SELECT COUNT(*) FROM ".T_CANCELLATIONS));

$template_content->set('invoices_due_today',$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE DATE(date_due)=CURDATE()"));
$template_content->set('invoices_overdue',$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE date_due < CURDATE() and status='overdue'"));
$invoice_statuses = array(
    'paid'=>0,
    'unpaid'=>0,
    'canceled'=>0
);
$template_content->set('invoice_statuses',array_merge($invoice_statuses,$db->GetAssoc("SELECT status, COUNT(*) as count FROM ".T_INVOICES." GROUP BY status")));
$template_content->set('transactions',$db->GetOne("SELECT COUNT(*) FROM ".T_TRANSACTIONS));

$template_content->set('total_listings',$db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS));
$template_content->set('listing_suggestions',$db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS_SUGGESTIONS));
$template_content->set('listing_claims',$db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS_CLAIMS));
$template_content->set('pending_updates',$db->GetOne("SELECT COUNT(*) FROM ".T_UPDATES));
$template_content->set('listings_without_coordinates',$db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." WHERE latitude = '0.0000000000'"));

$review_statuses = array(
    'active'=>0,
    'pending'=>0
);
$template_content->set('review_statuses',array_merge($review_statuses,$db->GetAssoc("SELECT status, COUNT(*) as count FROM ".T_REVIEWS." GROUP BY status")));
$template_content->set('total_ratings',$db->GetOne("SELECT COUNT(*) FROM ".T_RATINGS));
$template_content->set('pending_comments',$db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS_COMMENTS." WHERE status='pending'"));
$template_content->set('quality_votes',$db->GetOne("SELECT COUNT(*) FROM ".T_REVIEWS_QUALITY));

$template_content->set('total_categories',abs($PMDR->get('Categories')->getCount()));
$template_content->set('total_locations',abs($PMDR->get('Locations')->getCount()));
$template_content->set('email_queue',$PMDR->get('Email_Queue')->getCount());
$template_content->set('email_queue_moderate',$PMDR->get('Email_Queue')->getCountModerated());

$template_content->set('events',$db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS));
$template_content->set('events_pending',$db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS." WHERE status='pending'"));
$template_content->set('events_upcoming',$db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id WHERE ed.date_start BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)"));
$template_content->set('events_rsvps',$db->GetOne("SELECT COUNT(*) FROM ".T_EVENTS_RSVP));

$template_content->set('images',$db->GetOne("SELECT COUNT(*) FROM ".T_IMAGES));
$template_content->set('documents',$db->GetOne("SELECT COUNT(*) FROM ".T_DOCUMENTS));
$template_content->set('classifieds',$db->GetOne("SELECT COUNT(*) FROM ".T_CLASSIFIEDS));

$template_content->set('blog',$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG));
$template_content->set('blog_pending',$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG." WHERE status='pending'"));
$template_content->set('blog_comments_pending',$db->GetOne("SELECT COUNT(*) FROM ".T_BLOG_COMMENTS." WHERE status='pending'"));

$template_page_menu[0] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_index_menu.tpl'),'type'=>'content_raw');
$template_page_menu[1]['title'] = $PMDR->getLanguage('admin_index_information');
$template_page_menu[1]['content'] = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_index_information_menu.tpl');
$template_page_menu[1]['content']->set('date',$PMDR->get('Dates_Local')->dateNow('F jS, Y h:ia'));
$template_page_menu[1]['content']->set('version',$PMDR->getConfig('pmd_version'));
$template_page_menu[1]['type'] = 'content';

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>