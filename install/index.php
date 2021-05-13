<?php
error_reporting(E_ALL ^ E_NOTICE);

include('./includes/functions.php');

define('PMDROOT',str_replace('/install/','',dirname(str_replace('\\','/',__FILE__)).'/'));
define('BASE_URL',str_replace('/install/install.php','',preg_replace('/\?.*/', '', getURL())));

$show_options = false;

if(file_exists(PMDROOT.'/defaults.php')) {
    $defaults_contents = file_get_contents(PMDROOT.'/defaults.php');
    if(!preg_match('/\$BASE_URL = \'\';/',$defaults_contents) AND
       !preg_match('/define(\'DB_USER\', \'\');/',$defaults_contents)) {
       $show_options = true;
    }
}

if(!$show_options) {
    header('Location: ./install.php');
    exit();
}

include('./template/header.tpl');
include('./template/index.tpl');
include('./template/footer.tpl');
?>