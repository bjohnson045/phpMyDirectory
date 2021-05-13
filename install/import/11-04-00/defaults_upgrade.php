<?php
if(!IN_PMD) exit();

if(md5($_SESSION['login'].$_SESSION['pass']) != $_SESSION['import_hash']) {
    redirect(BASE_URL.'/install/import/index.php');
}

$table_prefix = $upgrade_data['table_prefix'];

define('OLD_PMDROOT',$upgrade_data['PMDROOT']);

define('OLD_T_USERS',$table_prefix.'_users');
define('OLD_T_LISTINGS',$table_prefix.'_listings');
define('OLD_T_OFFERS',$table_prefix.'_offers');
define('OLD_T_IMAGES',$table_prefix.'_images');
define('OLD_T_DOCUMENTS',$table_prefix.'_documents');
define('OLD_T_RATINGS',$table_prefix.'_rating');
define('OLD_T_REVIEWS',$table_prefix.'_reviews');
define('OLD_T_CATEGORIES',$table_prefix.'_category');
define('OLD_T_LOCATIONS',$table_prefix.'_locations');
define('OLD_T_LIST2CAT',$table_prefix.'_listing2category');
define('OLD_T_ADMIN',$table_prefix.'_admin');
define('OLD_T_SETTINGS',$table_prefix.'_config_main');
define('OLD_T_MEMBERSHIPS',$table_prefix.'_config_memberships');
define('OLD_T_INVOICES',$table_prefix.'_invoices');
define('OLD_T_BANNED',$table_prefix.'_banned');
define('OLD_T_FIELDS',$table_prefix.'_fields');
define('OLD_T_PLUGINS',$table_prefix.'_plugins');
define('OLD_T_BANNERS',$table_prefix.'_banners');
define('OLD_T_BANNER_TYPES',$table_prefix.'_banner_types');
define('OLD_T_GATEWAYS',$table_prefix.'_processors');
?>