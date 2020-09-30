<?php
/* Administrator area folder name. Default: cp */
$ADMIN_DIRECTORY = 'cp';

/*******************************************************************
* URL Path
* This is the full URL web path to the script
* Example: http://www.domain.com
*******************************************************************/
$BASE_URL = '';

/*******************************************************************
* SSL URL Path
* This is the full URL web path to the script for use with a SSL certificate.
* Example: https://www.domain.com (Notice: https://)
*******************************************************************/
$BASE_URL_SSL = '';

/*******************************************************************
* Content Delivery Network (CDN) URL Path / SSL URL Path
* This is the full URL web path to the files on a CDN
* Example: http://cdn.domain.com
*******************************************************************/
$CDN_URL = '';
$CDN_URL_SSL = '';

/*******************************************************************
* Database Settings
********************************************************************/
define('DB_HOST', '');
define('DB_USER', '');
define('DB_PASS', '');
define('DB_NAME', '');
define('DB_PORT', '');
define('DB_TABLE_PREFIX', 'pmd_');
define('DB_CHARSET', 'utf8');
define('DB_COLLATE', '');

/*******************************************************************
* Encryption/Security Key
********************************************************************/
$SECURITY_KEY = '';

/*******************************************************************
* Root Path (absolute path to script)
* This is the full server path to the script install directory.
* Example: /home/username/public_html/directory
* NOTE: This path is usually automatically set by the script.
*******************************************************************/
$PMDROOT = '';

/******************************************************************
* Files Path (absolute path) and URL
* This is used to store static files in an alternate location
*******************************************************************/
$FILES_PATH = NULL;
$FILES_URL = NULL;

/******************************************************************
* Session and Cookie Settings
*******************************************************************/
$COOKIE_PATH = NULL;
$COOKIE_DOMAIN = NULL;

$PMDROOT = !empty($PMDROOT) ? $PMDROOT : realpath(dirname(str_replace('\\','/',__FILE__)));
include($PMDROOT.'/defaults_setup.php');
?>