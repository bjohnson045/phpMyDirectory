<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

if(isset($_GET['action']) AND $_GET['action'] == 'clear_cache') {
    $PMDR->get('Cache')->clear();
    $PMDR->addMessage('success','Cache cleared.');
    redirect();
}

function reset_all($db) {
    $db->Execute("TRUNCATE ".T_BANNERS);
    $db->Execute("TRUNCATE ".T_BANNERS_CATEGORIES);
    $db->Execute("TRUNCATE ".T_BANNERS_LOCATIONS);
    $db->Execute("TRUNCATE ".T_BANNER_TYPES);
    unlink_files(BANNERS_PATH);

    $db->Execute("TRUNCATE ".T_BLOG);
    $db->Execute("TRUNCATE ".T_BLOG_CATEGORIES);
    $db->Execute("TRUNCATE ".T_BLOG_CATEGORIES_LOOKUP);
    $db->Execute("TRUNCATE ".T_BLOG_COMMENTS);
    unlink_file(BLOG_PATH);

    $db->Execute("TRUNCATE ".T_CANCELLATIONS);

    $db->Execute("DELETE FROM ".T_CATEGORIES." WHERE id!=1");
    $db->Execute("ALTER TABLE ".T_CATEGORIES." AUTO_INCREMENT=2");
    $db->Execute("TRUNCATE ".T_CATEGORIES_FIELDS);
    $db->Execute("TRUNCATE ".T_CATEGORIES_RELATED);
    $db->Execute("UPDATE ".T_CATEGORIES." SET left_=0, right_=1 WHERE id=1");
    unlink_files(CATEGORY_IMAGE_PATH);

    $db->Execute('TRUNCATE '.T_CLASSIFIEDS);
    $db->Execute('TRUNCATE '.T_CLASSIFIEDS_IMAGES);
    unlink_files(CLASSIFIEDS_PATH);
    unlink_files(CLASSIFIEDS_THUMBNAILS_PATH);

    $db->Execute("TRUNCATE ".T_CONTACT_REQUESTS);
    $db->Execute("TRUNCATE ".T_CRON);
    $db->Execute("TRUNCATE ".T_CRON_LOG);
    $db->Execute("TRUNCATE ".T_DISCOUNT_CODES);
    $db->Execute('TRUNCATE '.T_DOCUMENTS);
    unlink_files(DOCUMENTS_PATH);

    $db->Execute("TRUNCATE ".T_EMAIL_CAMPAIGNS);
    $db->Execute("TRUNCATE ".T_EMAIL_LISTS);
    $db->Execute("TRUNCATE ".T_EMAIL_LISTS_LOOKUP);
    $db->Execute("TRUNCATE ".T_EMAIL_LOG);
    $db->Execute("TRUNCATE ".T_EMAIL_QUEUE);
    $db->Execute("TRUNCATE ".T_EMAIL_SCHEDULES);
    $db->Execute("TRUNCATE ".T_ERROR_LOG);
    $db->Execute("TRUNCATE ".T_EXPORTS);
    $db->Execute("TRUNCATE ".T_FAQ_CATEGORIES);
    $db->Execute("TRUNCATE ".T_FAQ_QUESTIONS);
    $db->Execute("TRUNCATE ".T_FAVORITES);
    $db->Execute("TRUNCATE ".T_FEEDS_EXTERNAL);
    $db->Execute("TRUNCATE ".T_FIELDS);
    $db->Execute("TRUNCATE ".T_FIELDS_GROUPS);
    $db->Execute("TRUNCATE ".T_GATEWAYS_LOG);

    $db->Execute("TRUNCATE ".T_IMAGES);
    unlink_files(IMAGES_PATH);
    unlink_files(IMAGES_THUMBNAILS_PATH);

    $db->Execute("TRUNCATE ".T_IMPORTS);
    $db->Execute('TRUNCATE '.T_INVOICES);
    $db->Execute('TRUNCATE '.T_IP_LIMIT);

    $db->Execute("DELETE FROM ".T_LANGUAGES." WHERE languageid!=1");
    $db->Execute("DELETE FROM ".T_LANGUAGE_PHRASES." WHERE languageid!=-1");
    $db->Execute('ALTER TABLE '.T_LANGUAGES.' AUTO_INCREMENT=2');

    $db->Execute('TRUNCATE '.T_LISTINGS);
    $db->Execute('TRUNCATE '.T_LISTINGS_CATEGORIES);
    $db->Execute('TRUNCATE '.T_LISTINGS_CLAIMS);
    $db->Execute('TRUNCATE '.T_LISTINGS_SUGGESTIONS);
    unlink_files(LOGO_PATH);
    unlink_files(LOGO_THUMB_PATH);

    $db->Execute('DELETE FROM '.T_LOCATIONS.' WHERE id!=1');
    $db->Execute('ALTER TABLE '.T_LOCATIONS.' AUTO_INCREMENT=2');
    $db->Execute("UPDATE ".T_LOCATIONS." SET left_=0, right_=1 WHERE id=1");
    unlink_files(LOCATION_IMAGE_PATH);

    $db->Execute("TRUNCATE ".T_LOG);
    $db->Execute("TRUNCATE ".T_LOG_SQL);
    $db->Execute("TRUNCATE ".T_MEMBERSHIPS);
    $db->Execute("TRUNCATE ".T_MESSAGES);
    $db->Execute("TRUNCATE ".T_MESSAGES_POSTS);
    $db->Execute("TRUNCATE ".T_ORDERS);

    $db->Execute("TRUNCATE ".T_PAGES);
    $db->Execute("TRUNCATE ".T_PLUGINS);
    $db->Execute("TRUNCATE ".T_PRODUCTS);
    $db->Execute("TRUNCATE ".T_PRODUCTS_GROUPS);
    $db->Execute("TRUNCATE ".T_PRODUCTS_PRICING);

    $db->Execute("TRUNCATE ".T_RATINGS);
    $db->Execute("TRUNCATE ".T_REDIRECTS);
    $db->Execute("TRUNCATE ".T_REVIEWS);
    $db->Execute("TRUNCATE ".T_REVIEWS_COMMENTS);
    $db->Execute("TRUNCATE ".T_REVIEWS_QUALITY);
    $db->Execute("TRUNCATE ".T_SEARCH_LOG);
    $db->Execute("TRUNCATE ".T_SITE_LINKS);
    unlink_files(SITE_LINKS_PATH);
    $db->Execute("TRUNCATE ".T_STATISTICS);
    $db->Execute("TRUNCATE ".T_STATISTICS_RAW);
    $db->Execute("TRUNCATE ".T_TAX);
    $db->Execute("TRUNCATE ".T_TEMPLATES);
    $db->Execute("TRUNCATE ".T_TEMPLATES_DATA);
    $db->Execute("TRUNCATE ".T_TRANSACTIONS);
    $db->Execute("TRUNCATE ".T_UPDATES);
    $db->Execute("TRUNCATE ".T_UPGRADES);

    $db->Execute('DELETE FROM '.T_USERS.' WHERE id!=1');
    $db->Execute('ALTER TABLE '.T_USERS.' AUTO_INCREMENT=2');
    unlink_files(PROFILE_IMAGES_PATH);

    $db->Execute("DELETE FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id!=1");
    $db->Execute("TRUNCATE ".T_USERS_LOGIN_FAILS);
    $db->Execute("TRUNCATE ".T_USERS_LOGIN_PROVIDERS);
    $db->Execute("TRUNCATE ".T_ZIP_DATA);
    unlink_files(SCREENSHOTS_PATH);

    unlink_files(TEMP_PATH);
    unlink_files(TEMP_UPLOAD_PATH);
}


function findBadFiles($file='',&$file_list = array(), $current_folder='') {
    $handle = ($file !='') ? opendir($file) : opendir('../files');
    $bad_extensions = array('php','asp','cgi','pl','js');
    while(false != ($file = readdir($handle))) {
        if($file != '.' AND $file != '..') {
            if(is_dir('../files/'.$file)) {
                findBadFiles('../files/'.$file, $file_list, $file);
            } else {
                $file2 = explode('.',$file);
                if(count($file2) == 2 AND in_array($file2[1],$bad_extensions)) {
                    $file_list[] = PMDROOT.'/files/'.$current_folder.'/'.$file;
                }
            }
        }
    }
    return $file_list;
}


function findOrphans() {
    global $db;
    $orphans = array();
    // Check for orphan database rows
    $orphans['database']['documents'] = $db->GetCol("SELECT d.id, l.id AS listing_id FROM ".T_DOCUMENTS." d LEFT JOIN ".T_LISTINGS." l ON d.listing_id = l.id WHERE l.id IS NULL");
    $orphans['database']['images'] = $db->GetCol("SELECT i.id, l.id AS listing_id FROM ".T_IMAGES." i LEFT JOIN ".T_LISTINGS." l ON i.listing_id = l.id WHERE l.id IS NULL");
    $orphans['database']['classifieds'] = $db->GetCol("SELECT o.id, l.id AS listing_id FROM ".T_CLASSIFIEDS." o LEFT JOIN ".T_LISTINGS." l ON o.listing_id = l.id WHERE l.id IS NULL");
    $orphans['database']['ratings'] = $db->GetCol("SELECT r.id, l.id AS listing_id FROM ".T_RATINGS." r LEFT JOIN ".T_LISTINGS." l ON r.listing_id = l.id WHERE l.id IS NULL");
    $orphans['database']['reviews'] = $db->GetCol("SELECT r.id, l.id AS listing_id FROM ".T_REVIEWS." r LEFT JOIN ".T_LISTINGS." l ON r.listing_id = l.id WHERE l.id IS NULL");

    $bad_files = array('.','..','_vti_cnf','index.html','thumbnails','background');
    $file_checks = array(
        'logos'=>array(
            'path'=>array(LOGO_THUMB_PATH,LOGO_PATH,SCREENSHOTS_PATH),
            'table'=>T_LISTINGS
        ),
        'documents'=>array(
            'path'=>DOCUMENTS_PATH,
            'table'=>T_DOCUMENTS
        ),
        'images'=>array(
            'path'=>array(IMAGES_PATH,IMAGES_THUMBNAILS_PATH),
            'table'=>T_IMAGES
        ),
        'classifieds'=>array(
            'path'=>array(CLASSIFIEDS_PATH,CLASSIFIEDS_THUMBNAILS_PATH),
            'table'=>T_CLASSIFIEDS
        ),
        'banners'=>array(
            'path'=>BANNERS_PATH,
            'table'=>T_BANNERS
        ),
        'blog'=>array(
            'path'=>BLOG_PATH,
            'table'=>T_BLOG
        ),
        'profiles'=>array(
            'path'=>PROFILE_IMAGES_PATH,
            'table'=>T_USERS
        ),
        'events'=>array(
            'path'=>array(EVENT_IMAGES_PATH,EVENT_IMAGES_THUMB_PATH),
            'table'=>T_EVENTS
        )
    );

    foreach($file_checks AS $type=>$check) {
        if(!is_array($check['path'])) {
            $paths = array($check['path']);
        } else {
            $paths = $check['path'];
        }
        foreach($paths AS $path) {
            $handle = opendir($path);
            while(false != ($file = readdir($handle))) {
                if(!in_array($file,$bad_files)) {
                    $record = $db->GetRow("SELECT id FROM ".$check['table']." WHERE id=?",pathinfo($file,PATHINFO_FILENAME));
                    if(!$record) {
                        $orphans['files'][$type][] = $path.$file;
                    }
                }
            }
            closedir($handle);
        }
    }
    return $orphans;
}

if($_GET['action'] == 'reset') {
    $PMDR->get('Authentication')->checkPermission('admin_maintenance_reset');
    $PMDR->addMessage('error','<a href="admin_maintenance.php?action=reset_confirm">'.$PMDR->getLanguage('admin_maintenance_click_reset').'</a>');
}

if($_GET['action'] == 'reset_confirm') {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Reset database disabled in the demo.');
    } else {
        $PMDR->get('Authentication')->checkPermission('admin_maintenance_reset');
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_maintenance_reset_success'));
        reset_all($db);
    }
}

if($_GET['action'] == 'integrity_check' OR $_GET['action'] == 'integrity_fix') {
    $PMDR->get('Authentication')->checkPermission('admin_maintenance_integrity');
    $message_header = '<h4>Orphan File and Database Check</h4>';
    $orphans = findOrphans();

    $database_count = 0;
    foreach($orphans['database'] as $type) {
        $database_count += count($type);
    }

    $files_count = 0;
    foreach((array) $orphans['files'] as $type) {
        $files_count += count($type);
    }

    if($database_count + $files_count > 0 AND $_GET['action'] != 'integrity_fix') {
        $message = $files_count.' orphan files found:<br />';
        foreach($orphans['files'] AS $orphan_set) {
            foreach($orphan_set AS $orphan) {
                $message .= $orphan.'<br />';
            }
        }
        $PMDR->addMessage('error',$message_header.$message.'<br />'.$database_count.' database entries found!<br /><br /><a class="btn btn-default" href="admin_maintenance.php?action=integrity_fix">Fix Now</a>');
        unset($message);
    } elseif($_GET['action'] == 'integrity_fix') {
        if(count($orphans['database']['documents']) > 0) $db->Execute("DELETE FROM ".T_DOCUMENTS." WHERE id IN(".implode(',',$orphans['database']['documents']).")");
        if(count($orphans['database']['images']) > 0) $db->Execute("DELETE FROM ".T_IMAGES." WHERE id IN(".implode(',',$orphans['database']['images']).")");
        if(count($orphans['database']['classifieds']) > 0) $db->Execute("DELETE FROM ".T_CLASSIFIEDS." WHERE id IN(".implode(',',$orphans['database']['classifieds']).")");
        if(count($orphans['database']['ratings']) > 0) $db->Execute("DELETE FROM ".T_RATINGS." WHERE id IN(".implode(',',$orphans['database']['ratings']).")");
        if(count($orphans['database']['reviews']) > 0) $db->Execute("DELETE FROM ".T_REVIEWS." WHERE id IN(".implode(',',$orphans['database']['reviews']).")");
        if(count($orphans['database']['banners']) > 0) $db->Execute("DELETE FROM ".T_BANNERS." WHERE id IN(".implode(',',$orphans['database']['banners']).")");

        if(isset($orphans['files']) AND is_array($orphans['files'])) {
            foreach($orphans['files'] as $type_array) {
                foreach($type_array as $file) {
                    if(!$delete_status = unlink($file)) {
                        $PMDR->addMessage('error',$message_header.'Failed to fix an orphan file.  Makes sure all folders in /files/ are writable, including thumbnail folders.');
                        break;
                    }
                }
            }
        }
        if($delete_status) {
            $PMDR->addMessage('success',$message_header.'Integrity issues fixed!');
        }
    } else {
        $PMDR->addMessage('success',$message_header.'No issues found!');
    }

    $message_header = '<h4>Malicious File Check</h4>';
    if(isset($_GET['delete']) AND strstr($_GET['delete'], '/files/')) {
        @unlink($_GET['delete']);
    }
    $files = findBadFiles();
    if(!empty($files)) {
        foreach($files as $value) {
            $message .= $value." [<a href=\"./admin_maintenance.php?action=integrity&delete=$value\">".$PMDR->getLanguage('admin_maintenance_delete')."</a>]<br />";
        }
        $PMDR->addMessage('error',$message_header.$message);
    } else {
        $PMDR->addMessage('success',$message_header.$PMDR->getLanguage('admin_maintenance_no_files'));
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_maintenance.tpl');
$template_content->set('mysql_version',$db->version());
$template_content->set('server_time',$PMDR->get('Dates')->formatDateTimeNow());
$template_content->set('local_time',$PMDR->get('Dates_Local')->formatDateTimeNow());
$template_content->set('php',version_compare(PHP_VERSION,'5.6','>='));
$template_content->set('mysql',(version_compare($db->version(),'5.3','>=') >= 0) ? 1 : 0);

$curl = function_exists('curl_version');
$gd_info = @gd_info();
$gd = (get_extension_funcs('gd') AND extension_loaded('gd') AND strstr($gd_info['GD Version'],'2.'));
$json = function_exists('json_encode');
$allow_url_fopen = ini_get('allow_url_fopen');
if($ioncube = function_exists('ioncube_loader_version')) {
    if(function_exists('ioncube_loader_version')) {
        $ioncube_version = ioncube_loader_version();
    }
}

$template_content->set('allow_url_fopen',$allow_url_fopen);

if($allow_url_fopen) {
    $template_content->set('php_ini',true);
}

$output = '';
$output .= '<span class="label label-lg label-'.(intval($curl) ? 'success' : 'important').'">CURL</span> ';
$output .= '<span class="label label-lg label-'.(intval($gd) ? 'success' : 'important').'">GD2</span> ';
$output .= '<span class="label label-lg label-'.(intval($json) ? 'success' : 'important').'">JSON Support</span> ';
$output .= '<span class="label label-lg label-'.(intval($ioncube) ? 'success' : 'important').'">ionCube ';
if(!empty($ioncube_version)) {
    $output .= '('.$ioncube_version.') ';
}
$output .= '<span class="label label-lg label-'.(intval($json) ? 'success' : 'important').'">JSON Support</span> ';
$output .= '</span>';

$template_content->set('requirements',$output);

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>