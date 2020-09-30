<?php
if(!defined('IN_PMD')) exit();

// If debug mode is turned on, display query, template, language, and server global information
if($PMDR->get('Debug')->debug) {
    echo $PMDR->get('Debug')->getOutput();
}

// If GZIP is being used handle the gzip data and output it
if($PMDR->getConfig('gzip') AND @strstr('gzip',$_SERVER['HTTP_ACCEPT_ENCODING']) AND extension_loaded('zlib') AND PMD_SECTION != 'admin') {
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
exit();
?>