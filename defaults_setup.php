<?php
// Redirect to installation if no base URL exists
if($BASE_URL=='') {
    if(file_exists('./install/index.php')) {
        header('Location: ./install/index.php');
    } elseif(file_exists('../install/index.php')) {
        header('Location: ../install/index.php');
    }
    exit('Installation incomplete.');
}

// If defaults.php is included we know we are in PMD and is safe
define('IN_PMD',true);

// If turned on, we output all queries, turn on SQL logging, log errors to an error_log file
define('DEBUG_MODE',false);

// Set the security key
define('SECURITY_KEY',!empty($SECURITY_KEY) ? $SECURITY_KEY : NULL);

// Define the section if not defined
if(!defined('PMD_SECTION')) {
    define('PMD_SECTION',null);
}

// If turned on, we disable any features which may pose security risks
define('DEMO_MODE',false);

// Set CLI flag
define('CONSOLE',(defined('STDIN') OR php_sapi_name() == 'cli' OR (empty($_SERVER['REMOTE_ADDR']) and !isset($_SERVER['HTTP_USER_AGENT']) and count($_SERVER['argv']) > 0)));

// Set initial error handling, will be changed later
error_reporting(E_ALL ^ E_NOTICE);

// Try to force the server to display errors if its turned off
@ini_set('display_errors','On');

// Prevent prepend/append files
@ini_set('auto_prepend_file','Off');
@ini_set('auto_append_file','Off');

// Try to manually set the session save handler to files
@ini_set('session.save_handler', 'files');

// Set because sometimes basedir restrictions can be fixed by doing this
set_include_path('.');

// Turn off zend ze1 compatibility mode
@ini_set('zend.ze1_compatibility_mode','Off');

// Try to set PHP's default separator
@ini_set('arg_separator.output','&');

// Define necesarry paths
define('PMDROOT', rtrim(!empty($PMDROOT) ? $PMDROOT : dirname(str_replace('\\','/',__FILE__)),'/'));
define('PMDROOT_ADMIN', PMDROOT.'/'.trim($ADMIN_DIRECTORY,'/'));

if(!CONSOLE) {
    // Detect any spiders/bots and set global which can be used throughout script if needed
    $bots = array('alexa'=>'Alexa','yahoo'=>'Yahoo!','live'=>'Microsoft Live','Ask Jeeves'=>'Ask Jeeves','googlebot'=>'Google','slurp'=>'Slurp','bot'=>'Unknown Bot','spider'=>'Unknown Spider','crawl'=>'Unknown Crawler','archiver'=>'Unknown Archiver');
    foreach($bots as $bot=>$name) {
        if(strstr(strtolower($_SERVER['HTTP_USER_AGENT']),$bot)) {
            define('BOT',$name);
            break;
        }
    }
    if(!defined('BOT')) {
        define('BOT',null);
    }
    unset($bots,$bot,$name);

    // Forcefully set the REQUEST_URI server global if not set (not set on some servers)
    if(!isset($_SERVER['REQUEST_URI']) AND isset($_SERVER['SCRIPT_NAME'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
        if(isset($_SERVER['QUERY_STRING']) AND !empty($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }
    define('SSL_ON',(array_key_exists('HTTPS',$_SERVER) AND strtolower($_SERVER['HTTPS']) == 'on'));
    if(SSL_ON AND !empty($BASE_URL_SSL)) {
        define('BASE_URL', rtrim($BASE_URL_SSL,'/'));
    } else {
        define('BASE_URL', rtrim($BASE_URL,'/'));
    }
    define('BASE_URL_SSL', (!empty($BASE_URL_SSL) ? rtrim($BASE_URL_SSL,'/') : rtrim($BASE_URL,'/')));
    define('BASE_URL_NOSSL', rtrim($BASE_URL,'/'));
    define('BASE_URL_ADMIN',BASE_URL.'/'.trim($ADMIN_DIRECTORY,'/'));
    define('CDN_URL', (!empty($CDN_URL) ? rtrim(((SSL_ON) ? $CDN_URL_SSL : $CDN_URL),'/') : BASE_URL));
    define('URL_SCHEME',(SSL_ON ? 'https' : 'http'));
    define('URL',URL_SCHEME.'://'.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'].(!in_array($_SERVER['SERVER_PORT'],array(80,443)) ? ':'.$_SERVER['SERVER_PORT'] : '')).urldecode(!empty($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REQUEST_URI']));
    define('URL_NOQUERY',preg_replace('/\?.*/','',URL));
} else {
    define('BASE_URL','');
    define('SSL_ON',false);
}

define('BASE_FOLDER',parse_url(BASE_URL.'/',PHP_URL_PATH));
define('MEMBERS_FOLDER','/members/');
define('COOKIE_PREFIX','pmd_');

// Define relative path used in some AJAX calls
if(@PMD_SECTION == 'admin' OR @PMD_SECTION == 'members') {
    define('PMDROOT_RELATIVE','..');
} else {
    define('PMDROOT_RELATIVE','.');
}

// Cookie Settings
if(is_null($COOKIE_DOMAIN)) {
    $COOKIE_DOMAIN = strtolower(@parse_url(BASE_URL,PHP_URL_HOST));
}
define('COOKIE_PATH',(is_null($COOKIE_PATH) ? BASE_FOLDER : $COOKIE_PATH));
define('COOKIE_DOMAIN',($COOKIE_DOMAIN == 'localhost' ? false : $COOKIE_DOMAIN));
unset($COOKIE_PATH,$COOKIE_DOMAIN);

// Define files path
define('FILES_PATH',(!isset($FILES_PATH) OR is_null($FILES_PATH)) ? PMDROOT : $FILES_PATH);
unset($FILES_PATH);

// Define a files URL
define('FILES_URL',(!isset($FILES_URL) OR is_null($FILES_URL)) ? BASE_URL : $FILES_URL);
unset($FILES_URL);

// Define specific file paths
define('LOGO_PATH',FILES_PATH.'/files/logo/');
define('LOGO_BACKGROUND_PATH',FILES_PATH.'/files/logo/background/');
define('LOGO_THUMB_PATH',FILES_PATH.'/files/logo/thumbnails/');
define('PROFILE_IMAGES_PATH',FILES_PATH.'/files/profiles/');
define('BANNERS_PATH',FILES_PATH.'/files/banner/');
define('IMAGES_PATH',FILES_PATH.'/files/images/');
define('IMAGES_THUMBNAILS_PATH',FILES_PATH.'/files/images/thumbnails/');
define('BLOG_PATH',FILES_PATH.'/files/blog/');
define('DOCUMENTS_PATH',FILES_PATH.'/files/documents/');
define('CLASSIFIEDS_PATH',FILES_PATH.'/files/classifieds/');
define('CLASSIFIEDS_THUMBNAILS_PATH',FILES_PATH.'/files/classifieds/thumbnails/');
define('CLASSIFIEDS_CATEGORY_IMAGE_PATH',FILES_PATH.'/files/classifieds/categories/');
define('CATEGORY_IMAGE_PATH',FILES_PATH.'/files/categories/');
define('LOCATION_IMAGE_PATH',FILES_PATH.'/files/locations/');
define('SITE_LINKS_PATH',FILES_PATH.'/files/site_links/');
define('SCREENSHOTS_PATH',FILES_PATH.'/files/screenshots/');
define('TEMP_PATH',FILES_PATH.'/files/temp/');
define('TEMP_UPLOAD_PATH',FILES_PATH.'/files/upload/');
define('CACHE_PATH',PMDROOT.'/cache/');
define('HTMLEDITOR_PATH',FILES_PATH.'/files/upload/htmleditor/');
define('PLUGINS_PATH',FILES_PATH.'/modules/plugins/');
define('EVENT_IMAGES_PATH',FILES_PATH.'/files/events/images/');
define('EVENT_IMAGES_THUMB_PATH',FILES_PATH.'/files/events/images/thumbnails/');

// If we want to include path information without database/cookies
if(defined('DEFAULTS_PATHS') AND DEFAULTS_PATHS == true) {
    return true;
}

// Define table names
define('T_BANNERS',DB_TABLE_PREFIX.'banners');
define('T_BANNERS_CATEGORIES',DB_TABLE_PREFIX.'banners_categories');
define('T_BANNERS_LOCATIONS',DB_TABLE_PREFIX.'banners_locations');
define('T_BANNER_TYPES',DB_TABLE_PREFIX.'banner_types');
define('T_BLOCKS', DB_TABLE_PREFIX.'blocks');
define('T_BLOCKS_DATA', DB_TABLE_PREFIX.'blocks_data');
define('T_BLOG', DB_TABLE_PREFIX.'blog');
define('T_BLOG_CATEGORIES', DB_TABLE_PREFIX.'blog_categories');
define('T_BLOG_CATEGORIES_LOOKUP', DB_TABLE_PREFIX.'blog_categories_lookup');
define('T_BLOG_COMMENTS', DB_TABLE_PREFIX.'blog_comments');
define('T_BLOG_FOLLOWERS', DB_TABLE_PREFIX.'blog_followers');
define('T_CANCELLATIONS', DB_TABLE_PREFIX.'cancellations');
define('T_CAPTCHAS',DB_TABLE_PREFIX.'captchas');
define('T_CATEGORIES', DB_TABLE_PREFIX.'categories');
define('T_CATEGORIES_FIELDS', DB_TABLE_PREFIX.'categories_fields');
define('T_CATEGORIES_RELATED', DB_TABLE_PREFIX.'categories_related');
define('T_CLASSIFIEDS', DB_TABLE_PREFIX.'classifieds');
define('T_CLASSIFIEDS_CATEGORIES', DB_TABLE_PREFIX.'classifieds_categories');
define('T_CLASSIFIEDS_CATEGORIES_FIELDS', DB_TABLE_PREFIX.'classifieds_categories_fields');
define('T_CLASSIFIEDS_CATEGORIES_RELATED', DB_TABLE_PREFIX.'classifieds_categories_related');
define('T_CLASSIFIEDS_CATEGORIES_LOOKUP', DB_TABLE_PREFIX.'classifieds_categories_lookup');
define('T_CLASSIFIEDS_IMAGES', DB_TABLE_PREFIX.'classifieds_images');
define('T_CONTACT_REQUESTS',DB_TABLE_PREFIX.'contact_requests');
define('T_CREDIT',DB_TABLE_PREFIX.'credit');
define('T_CRON',DB_TABLE_PREFIX.'cron');
define('T_CRON_LOG',DB_TABLE_PREFIX.'cron_log');
define('T_DISCOUNT_CODES', DB_TABLE_PREFIX.'discount_codes');
define('T_DOCUMENTS', DB_TABLE_PREFIX.'documents');
define('T_EMAIL_CAMPAIGNS', DB_TABLE_PREFIX.'email_campaigns');
define('T_EMAIL_LISTS', DB_TABLE_PREFIX.'email_lists');
define('T_EMAIL_LISTS_LOOKUP', DB_TABLE_PREFIX.'email_lists_lookup');
define('T_EMAIL_LOG', DB_TABLE_PREFIX.'email_log');
define('T_EMAIL_MARKETING', DB_TABLE_PREFIX.'email_marketing');
define('T_EMAIL_MARKETING_QUEUE', DB_TABLE_PREFIX.'email_marketing_queue');
define('T_EMAIL_SCHEDULES', DB_TABLE_PREFIX.'email_schedules');
define('T_EMAIL_TEMPLATES', DB_TABLE_PREFIX.'email_templates');
define('T_EMAIL_QUEUE',DB_TABLE_PREFIX.'email_queue');
define('T_ERROR_LOG',DB_TABLE_PREFIX.'error_log');
define('T_EVENTS',DB_TABLE_PREFIX.'events');
define('T_EVENTS_CATEGORIES',DB_TABLE_PREFIX.'events_categories');
define('T_EVENTS_CATEGORIES_LOOKUP',DB_TABLE_PREFIX.'events_categories_lookup');
define('T_EVENTS_DATES',DB_TABLE_PREFIX.'events_dates');
define('T_EVENTS_RSVP',DB_TABLE_PREFIX.'events_rsvp');
define('T_EXPORTS',DB_TABLE_PREFIX.'exports');
define('T_FAQ_CATEGORIES',DB_TABLE_PREFIX.'faq_categories');
define('T_FAQ_QUESTIONS',DB_TABLE_PREFIX.'faq_questions');
define('T_FAVORITES',DB_TABLE_PREFIX.'favorites');
define('T_FEEDS_EXTERNAL',DB_TABLE_PREFIX.'feeds_external');
define('T_FIELDS', DB_TABLE_PREFIX.'fields');
define('T_FIELDS_GROUPS', DB_TABLE_PREFIX.'fields_groups');
define('T_GATEWAYS', DB_TABLE_PREFIX.'gateways');
define('T_GATEWAYS_LOG',DB_TABLE_PREFIX.'gateways_log');
define('T_INVOICES', DB_TABLE_PREFIX.'invoices');
define('T_IMAGES', DB_TABLE_PREFIX.'images');
define('T_IMPORTS', DB_TABLE_PREFIX.'imports');
define('T_IP_LIMIT', DB_TABLE_PREFIX.'ip_limits');
define('T_IP_TABLE', DB_TABLE_PREFIX.'ip_table');
define('T_JOBS', DB_TABLE_PREFIX.'jobs');
define('T_JOBS_CATEGORIES', DB_TABLE_PREFIX.'jobs_categories');
define('T_JOBS_CATEGORIES_LOOKUP', DB_TABLE_PREFIX.'jobs_categories_lookup');
define('T_LANGUAGES', DB_TABLE_PREFIX.'languages');
define('T_LANGUAGE_PHRASES', DB_TABLE_PREFIX.'language_phrases');
define('T_LISTINGS_CATEGORIES', DB_TABLE_PREFIX.'listings_categories');
define('T_LISTINGS', DB_TABLE_PREFIX.'listings');
define('T_LISTINGS_CLAIMS', DB_TABLE_PREFIX.'listings_claims');
define('T_LISTINGS_LINKED', DB_TABLE_PREFIX.'listings_linked');
define('T_LISTINGS_LOCATIONS', DB_TABLE_PREFIX.'listings_locations');
define('T_LISTINGS_SUGGESTIONS', DB_TABLE_PREFIX.'listings_suggestions');
define('T_LOCATIONS', DB_TABLE_PREFIX.'locations');
define('T_LOG', DB_TABLE_PREFIX.'log');
define('T_LOG_SQL', DB_TABLE_PREFIX.'log_sql');
define('T_MAXMIND_BLOCKS',DB_TABLE_PREFIX.'maxmind_blocks');
define('T_MAXMIND_LOCATION',DB_TABLE_PREFIX.'maxmind_location');
define('T_MEMBERSHIPS', DB_TABLE_PREFIX.'memberships');
define('T_MENU_LINKS', DB_TABLE_PREFIX.'menu_links');
define('T_MESSAGES',DB_TABLE_PREFIX.'messages');
define('T_MESSAGES_POSTS',DB_TABLE_PREFIX.'messages_posts');
define('T_ORDERS', DB_TABLE_PREFIX.'orders');
define('T_PAGES', DB_TABLE_PREFIX.'pages');
define('T_PLUGINS', DB_TABLE_PREFIX.'plugins');
define('T_PRODUCTS', DB_TABLE_PREFIX.'products');
define('T_PRODUCTS_GROUPS',DB_TABLE_PREFIX.'products_groups');
define('T_PRODUCTS_PRICING',DB_TABLE_PREFIX.'products_pricing');
define('T_RATINGS', DB_TABLE_PREFIX.'ratings');
define('T_RATINGS_CATEGORIES', DB_TABLE_PREFIX.'ratings_categories');
define('T_REDIRECTS', DB_TABLE_PREFIX.'redirects');
define('T_REVIEWS', DB_TABLE_PREFIX.'reviews');
define('T_REVIEWS_COMMENTS', DB_TABLE_PREFIX.'reviews_comments');
define('T_REVIEWS_QUALITY', DB_TABLE_PREFIX.'reviews_quality');
define('T_SEARCH_LOG', DB_TABLE_PREFIX.'search_log');
define('T_SESSIONS', DB_TABLE_PREFIX.'sessions');
define('T_SETTINGS', DB_TABLE_PREFIX.'settings');
define('T_SITE_LINKS', DB_TABLE_PREFIX.'site_links');
define('T_SITEMAP_XML', DB_TABLE_PREFIX.'sitemap_xml');
define('T_SMS_GATEWAYS',DB_TABLE_PREFIX.'sms_gateways');
define('T_STATISTICS', DB_TABLE_PREFIX.'statistics');
define('T_STATISTICS_RAW', DB_TABLE_PREFIX.'statistics_raw');
define('T_TAX',DB_TABLE_PREFIX.'tax');
define('T_TEMPLATES',DB_TABLE_PREFIX.'templates');
define('T_TEMPLATES_DATA',DB_TABLE_PREFIX.'templates_data');
define('T_TRANSACTIONS',DB_TABLE_PREFIX.'transactions');
define('T_UPDATES', DB_TABLE_PREFIX.'updates');
define('T_UPGRADES', DB_TABLE_PREFIX.'upgrades');
define('T_USERS', DB_TABLE_PREFIX.'users');
define('T_USERS_API_KEYS',DB_TABLE_PREFIX.'users_api_keys');
define('T_USERS_CARDS', DB_TABLE_PREFIX.'users_cards');
define('T_USERS_GROUPS', DB_TABLE_PREFIX.'users_groups');
define('T_USERS_GROUPS_LOOKUP', DB_TABLE_PREFIX.'users_groups_lookup');
define('T_USERS_LOGIN_FAILS', DB_TABLE_PREFIX.'users_login_fails');
define('T_USERS_LOGIN_PROVIDERS', DB_TABLE_PREFIX.'users_login_providers');
define('T_USERS_PERMISSIONS', DB_TABLE_PREFIX.'users_permissions');
define('T_USERS_GROUPS_PERMISSIONS_LOOKUP', DB_TABLE_PREFIX.'users_groups_permissions_lookup');
define('T_ZIP_DATA',DB_TABLE_PREFIX.'zip_data');
define('T_ZONES',DB_TABLE_PREFIX.'zones');
define('T_ZONES_CONTENT',DB_TABLE_PREFIX.'zones_content');

// Set request token used for form and AJAX calls for form security
if(!isset($_COOKIE[COOKIE_PREFIX.'from'])) {
    // The from_pmd value must be remembered if this is the first page load since the cookie will not
    // be available for ajax requests on the first page load.
    define(COOKIE_PREFIX.'from',md5(uniqid(rand(),true)));
    setcookie(COOKIE_PREFIX.'from',constant(COOKIE_PREFIX.'from'),time()+60*60*12,COOKIE_PATH,COOKIE_DOMAIN);
} else {
    define(COOKIE_PREFIX.'from',$_COOKIE[COOKIE_PREFIX.'from']);
}

// Setup Registry/Factory
include(PMDROOT.'/includes/class_factory.php');
include(PMDROOT.'/includes/class_registry.php');
include(PMDROOT.'/includes/class_table_gateway.php');

// Set the time zone in PHP to UTC
date_default_timezone_set('UTC');

// Set the registry in $PMDR
$PMDR = Registry::getInstance();

// Initialize the error handler
$PMDR->get('ErrorHandler');

// Initialize the database object, if it fails exit with error
$db = $PMDR->get('DB');
if(!$db) exit('Database connection failed.');

// Set the time zone in MySQL to UTC
$db->Execute("SET SESSION time_zone = '+0:00'");

// If a large amount of categories/locations uncomment the following line
//$db->Execute("SET SQL_BIG_SELECTS=1");

// Initialize the session
if(defined('UPGRADE')) {
    $PMDR->get('Session','files');
} else {
    $PMDR->get('Session');
}

// Setup debugging
$PMDR->get('Debug');

// If demo mode, email any errors
if(DEMO_MODE) {
    $PMDR->get('ErrorHandler')->setFlags(true, true, false, true, true, false, true, true, false);
    $PMDR->get('ErrorHandler')->setMailRecipient($PMDR->getConfig('admin_email'));
}

if(defined('UPGRADE') AND UPGRADE) {
    $PMDR->get('ErrorHandler')->setFlags(true, true, false, false, false, false, false, false, false);
}

// Define mod rewrite global variable for use throughout script
define('MOD_REWRITE',$PMDR->getConfig('mod_rewrite'));

// Include function files
include(PMDROOT.'/includes/functions.php');
include(PMDROOT.'/includes/functions_numbers.php');
include(PMDROOT.'/includes/functions_ajax.php');

// Redirect to the base URL if the host name does not match the current URL (to prevent AJAX URL permission issues)
// Supress errors for parse_url due to CRON possibly not loading a URL
if(defined('URL') AND strcasecmp(@parse_url(BASE_URL,PHP_URL_HOST),strtolower(@parse_url(URL,PHP_URL_HOST))) != 0) {
    $PMDR->get('Error',301);
    redirect_url(str_ireplace(@parse_url(URL,PHP_URL_HOST),@parse_url(BASE_URL,PHP_URL_HOST),URL));
}

// If we have SSL on and we are at a http:// address, switch it over to https:// automatically
// We do this after the above redirect because if done before it fails if a host match error exists
if(!empty($BASE_URL_SSL) AND (@PMD_SECTION == 'members' OR @PMD_SECTION == 'admin') AND @$_SERVER['HTTPS'] != 'on') {
    if($BASE_URL !== $BASE_URL_SSL) {
        redirect_url(str_replace($BASE_URL,$BASE_URL_SSL,URL));
    } else {
        redirect_url($BASE_URL_SSL);
    }
}

// Include all plugin files to intialize all plugin code
foreach($PMDR->get('Plugins')->plugins AS $plugin) {
    include(PLUGINS_PATH.$plugin.'/'.$plugin.'.php');
}
unset($plugin);

// Sanitize all variables in $_GET
foreach($_GET as $key=>$value) {
    if($value != '') {
        $_GET[$key] = $PMDR->get('Cleaner')->clean_input($value, array());
    }
}
unset($key,$value);

$PMDR->get('Plugins')->run_hook('defaults_setup');

// Setup Addon Flags
foreach((array) unserialize($PMDR->getConfig('addons')) as $key=>$value) {
    define($key,$value);
}
unset($value,$key);

// Check for cookies to set the session
if(!$PMDR->get('Authentication')->confirmAuth()) {
    $PMDR->get('Authentication')->setSessionFromCookies();
}

define('LOGGED_IN',$PMDR->get('Authentication')->confirmAuth());

// Check for maintenance option and redirect if necesarry
if($PMDR->getConfig('maintenance') AND !on_page(array('maintenance.php','/install/','cron.php','ajax.php'),true) AND PMD_SECTION != 'admin' AND @!in_array('admin_login',$_SESSION['admin_permissions']) AND !DEMO_MODE AND (!defined('MAINTENANCE_MODE') OR MAINTENANCE_MODE == true)) {
    redirect_url(BASE_URL.'/maintenance.php');
}

// Allow site wide SSL

// Has a mobile device been detected already?
if(!isset($_COOKIE[COOKIE_PREFIX.'mobile'])) {
    // Check if we have a mobile device and set a session variable so we do not check every page load
    $PMDR->loadJavascript('
    <script type="text/javascript">
    $(document).ready(function(){
        if($.cookie !== undefined) {
            if($(window).width() <= 640) {
                $.cookie(\''.COOKIE_PREFIX.'mobile\',1,{path: \''.COOKIE_PATH.'\',domain: \''.COOKIE_DOMAIN.'\',secure: '.(SSL_ON ? 'true' : 'false').'});
                location.reload(true);
            } else {
                $.cookie(\''.COOKIE_PREFIX.'mobile\',0,{path: \''.COOKIE_PATH.'\',domain: \''.COOKIE_DOMAIN.'\',secure: '.(SSL_ON ? 'true' : 'false').'});
            }
        }
    });
    </script>',100);
} else {
    // True or false global to determine if the user agent is a mobile device
    define('MOBILE',($_COOKIE[COOKIE_PREFIX.'mobile'] == 1));
}


// Setup template based on GET/POST/Cookie/Mobile
if(!empty($_GET['template'])) {
    $template = $_GET['template'];
} elseif(!empty($_POST['template'])) {
    $template = $_POST['template'];
} elseif(!empty($_COOKIE[COOKIE_PREFIX.'template'])) {
    $template = $_COOKIE[COOKIE_PREFIX.'template'];
} elseif(defined('MOBILE') AND MOBILE AND $PMDR->getConfig('mobile_template') != '') {
    $template = $PMDR->getConfig('mobile_template');
} else {
    $template = null;
}

// Do a preg_match here for directory traversal security
// COOKIE_PATH and COOKIE_DOMAIN for compatibility with multiple PMD installations on one domain and if cookies
// are set when browsing a friendly URL such as a category path.
if(!empty($template)) {
    if(file_exists(PMDROOT.'/template/'.$template.'/') AND preg_match('/^[a-z0-9_-]+$/i',$template)) {
        $PMDR->config['template'] = $template;
        setcookie(COOKIE_PREFIX.'template','',time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
        setcookie(COOKIE_PREFIX.'template',$template,time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
    }
}
unset($template);

define('TEMPLATE_PATH','/template/'.$PMDR->getConfig('template').'/');
define('TEMPLATE_PATH_ADMIN','/template/default/');

if(file_exists(PMDROOT.TEMPLATE_PATH.'config.ini') AND $template_config = parse_ini_file(PMDROOT.TEMPLATE_PATH.'config.ini')) {
    if(isset($template_config['parent']) AND !empty($template_config['parent'])) {
        define('TEMPLATE_PATH_PARENT','/template/'.$template_config['parent'].'/');
    }
}
unset($template_config);

// Setup language based on cookie
if(!empty($_POST['lang']) OR !empty($_GET['lang'])) {
    setcookie(COOKIE_PREFIX.'lang','',time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
    if(!empty($_POST['lang']) AND preg_match('/^[0-9]+$/',$_POST['lang'])) {
        setcookie(COOKIE_PREFIX.'lang',$_POST['lang'],time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
        $PMDR->config['language'] = $_POST['lang'];
    } elseif(preg_match('/^[0-9]+$/',$_GET['lang'])) {
        setcookie(COOKIE_PREFIX.'lang',$_GET['lang'],time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
        $PMDR->config['language'] = $_GET['lang'];
    }
} elseif(!empty($_COOKIE[COOKIE_PREFIX.'lang'])) {
    $PMDR->config['language'] = $_COOKIE[COOKIE_PREFIX.'lang'];
} elseif(!empty($_SERVER['HTTP_ACCEPT_LANGUAGE']) AND $language_id = $PMDR->get('Languages')->getByLanguageCode(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2))) {
    setcookie(COOKIE_PREFIX.'lang',$language_id,time()+(60*60*24*30),COOKIE_PATH,COOKIE_DOMAIN);
    $PMDR->config['language'] = $language_id;
}
unset($language_id);

$PMDR->get('Plugins')->run_hook('defaults_setup_end');
?>