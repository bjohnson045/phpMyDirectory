<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$template_content->set('title','Memcache Information');

function getDetails($status){
    $output = "<table class=\"table table-bordered table-striped\">";
    $output .= "<tr><td>Memcache Server version:</td><td> ".$status["version"]."</td></tr>";
    $output .= "<tr><td>Process id of this server process </td><td>".$status["pid"]."</td></tr>";
    $output .= "<tr><td>Number of seconds this server has been running </td><td>".$status["uptime"]."</td></tr>";
    $output .= "<tr><td>Accumulated user time for this process </td><td>".$status["rusage_user"]." seconds</td></tr>";
    $output .= "<tr><td>Accumulated system time for this process </td><td>".$status["rusage_system"]." seconds</td></tr>";
    $output .= "<tr><td>Total number of items stored by this server ever since it started </td><td>".$status["total_items"]."</td></tr>";
    $output .= "<tr><td>Number of open connections </td><td>".$status["curr_connections"]."</td></tr>";
    $output .= "<tr><td>Total number of connections opened since the server started running </td><td>".$status["total_connections"]."</td></tr>";
    $output .= "<tr><td>Number of connection structures allocated by the server </td><td>".$status["connection_structures"]."</td></tr>";
    $output .= "<tr><td>Cumulative number of retrieval requests </td><td>".$status["cmd_get"]."</td></tr>";
    $output .= "<tr><td> Cumulative number of storage requests </td><td>".$status["cmd_set"]."</td></tr>";

    $percCacheHit=((real)$status ["get_hits"]/ (real)$status ["cmd_get"] *100);
    $percCacheHit=round($percCacheHit,3);
    $percCacheMiss=100-$percCacheHit;

    $output .= "<tr><td>Number of keys that have been requested and found present </td><td>".$status["get_hits"]." ($percCacheHit%)</td></tr>";
    $output .= "<tr><td>Number of items that have been requested and not found </td><td>".$status["get_misses"]."($percCacheMiss%)</td></tr>";

    $MBRead= (real)$status["bytes_read"]/(1024*1024);

    $output .= "<tr><td>Total number of bytes read by this server from network </td><td>".$MBRead." Mega Bytes</td></tr>";
    $MBWrite=(real) $status["bytes_written"]/(1024*1024) ;
    $output .= "<tr><td>Total number of bytes sent by this server to network </td><td>".$MBWrite." Mega Bytes</td></tr>";
    $MBSize=(real) $status["limit_maxbytes"]/(1024*1024) ;
    $output .= "<tr><td>Number of bytes this server is allowed to use for storage.</td><td>".$MBSize." Mega Bytes</td></tr>";
    $MBUsed=(real) $status["bytes"]/(1024*1024) ;
    $output .= "<tr><td>Number of bytes currently used for storage.</td><td>".$MBUsed." Mega Bytes</td></tr>";
    $output .= "<tr><td>Number of valid items removed from cache to free memory for new items.</td><td>".$status["evictions"]."</td></tr>";
    $output .= "</table>";
    return $output;
}


if(DEMO_MODE) {
    $PMDR->addMessage('error','PHP information is disabled in the demo.');
} else {
    try {
        $cache = $PMDR->get('Cache_Memcache');
        $template_content->set('content',getDetails($cache->getMemcacheStats()));
    } catch (Exception $e) {
        $PMDR->addMessage('error',$e->getMessage());
    }
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>