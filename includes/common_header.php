<?php
if(!defined('IN_PMD')) exit();

if($PMDR->getConfig('gzip') AND strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') AND extension_loaded('zlib') AND PMD_SECTION != 'admin') {
    @ob_start('ob_gzhandler');
    @ob_implicit_flush(0);
    @header("Content-Encoding: zlib,gzip,deflate");
}

header('Content-Type: text/html; charset='.$PMDR->getLanguage('charset'));
header('Content-Language: '.substr($PMDR->getLanguage('languagecode'),0,2));
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');
?>