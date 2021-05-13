<?php
/* Administrator area folder name. Default: cp */
$ADMIN_DIRECTORY = '{admin_directory}';

/*******************************************************************
* URL Path - NO TRAILING SLASH
* This is the full URL web path to the script
* Example: http://www.domain.com
*******************************************************************/
$BASE_URL = '{url}';

/*******************************************************************
* SSL URL Path - NO TRAILING SLASH
* This is the full URL web path to the script for use with a SSL certificate.
* Example: https://www.domain.com (Notice: https://)
*******************************************************************/
$BASE_URL_SSL = '{url_ssl}';

/*******************************************************************
* Content Delivery Network (CDN) URL Path / SSL URL Path
* This is the full URL web path to the files on a CDN
* Example: http://cdn.domain.com
*******************************************************************/
$CDN_URL = '{url_cdn}';
$CDN_URL_SSL = '{url_cdn_ssl}';

/*******************************************************************
* Database Settings
********************************************************************/
define('DB_HOST', '{db_host}');
define('DB_USER', '{db_user}');
define('DB_PASS', '{db_pass}');
define('DB_NAME', '{db_name}');
define('DB_PORT', '{db_port}');
define('DB_TABLE_PREFIX', '{db_table_prefix}');
define('DB_CHARSET', '{db_charset}');
define('DB_COLLATE', '{db_collate}');

/*******************************************************************
* Encryption/Security Key
********************************************************************/
$SECURITY_KEY = '{security_key}';

/*******************************************************************
* Root Path (absolute path to script) - NO TRAILING SLASH
* This is the full server path to the script install directory.
* Example: /home/username/public_html/directory
* NOTE: This path is usually automatically set by the script.
*******************************************************************/
$PMDROOT = '{pmdroot}';

/******************************************************************
* Files Path (absolute path) and URL
* This is used to store static files in an alternate location
*******************************************************************/
$FILES_PATH = {files_path};
$FILES_URL = {files_url};

/******************************************************************
* Session and Cookie Settings
*******************************************************************/
$COOKIE_PATH = {cookie_path};
$COOKIE_DOMAIN = {cookie_domain};

$PMDROOT = !empty($PMDROOT) ? $PMDROOT : dirname(str_replace('\\','/',__FILE__));
include($PMDROOT.'/defaults_setup.php');
?>