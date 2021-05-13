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
define('OLD_T_LOC_ONE',$table_prefix.'_loc_one');
define('OLD_T_LOC_TWO',$table_prefix.'_loc_two');
define('OLD_T_LOC_THREE',$table_prefix.'_loc_three');
define('OLD_T_LOC_FOUR',$table_prefix.'_loc_four');
define('OLD_T_LIST2CAT',$table_prefix.'_listcat');
define('OLD_T_ADMIN',$table_prefix.'_admin');
define('OLD_T_SETTINGS',$table_prefix.'_config_main');
define('OLD_T_MEMBERSHIPS',$table_prefix.'_config_memberships');
define('OLD_T_GATEWAYS',$table_prefix.'_config_paygateway');
?>