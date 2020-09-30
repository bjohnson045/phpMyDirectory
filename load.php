<?php
define('DEFAULTS_PATHS_ONLY',true);

include('./defaults.php');

header("Expires: ".gmdate("D, d M Y H:i:s",strtotime("+7 day"))." GMT");

if(strstr($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') AND extension_loaded('zlib')) {
    @ob_start('ob_gzhandler');
    @ob_implicit_flush(0);
    @header("Content-Encoding: zlib,deflate,gzip");
}

$last_modified = 0;

header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified)." GMT");

if(isset($_GET['css'])) {
    header("Content-Type: text/css");
    $parts = explode(',',$_GET['css']);
    ob_start();
    foreach($parts AS $part) {
        switch($part) {
            default:
                break;
        }
        if($file_time > $last_modified) {
            $last_modified = $file_time;
        }
    }
    $contents = ob_get_contents();
    ob_end_clean();
    echo $contents;
}

if(strstr('gzip',$_SERVER['HTTP_ACCEPT_ENCODING']) AND extension_loaded('zlib')) {
    $gzip_contents = ob_get_contents();
    ob_end_clean();

    $gzip_size = strlen($gzip_contents);
    $gzip_crc = crc32($gzip_contents);

    $gzip_contents = gzcompress($gzip_contents, 9);
    $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

    echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
    echo $gzip_contents;
    echo pack("V", $gzip_crc);
    echo pack("V", $gzip_size);
}
?>