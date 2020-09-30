<?php
include('./defaults.php');

if($_GET['method'] == 'siteLinks.getLink') {
    redirect_url(BASE_URL.'/site_links.php?action=display&id='.$_GET['id']);
}

// Requests from the same server don't have a HTTP_ORIGIN header
if(!array_key_exists('HTTP_ORIGIN', $_SERVER)) {
    $_SERVER['HTTP_ORIGIN'] = $_SERVER['SERVER_NAME'];
}

$parameters = array(
    'version'=>$_GET['version'],
    'request'=>$_GET['request'],
    'origin'=>$_SERVER['HTTP_ORIGIN']
);

try {
    echo $PMDR->get('API',$parameters)->process();
} catch (Exception $e) {
    echo json_encode(array('error'=>$e->getMessage()));
}
?>