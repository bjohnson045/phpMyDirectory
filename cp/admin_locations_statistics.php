<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_locations'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_locations_view');

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

if($_GET['action'] == 'download') {
    $fp = fopen(TEMP_UPLOAD_PATH.'location_impressions.csv', 'w');
    $header = array($PMDR->getLanguage('admin_locations_id'),$PMDR->getLanguage('admin_locations_title'),$PMDR->getLanguage('admin_locations_impressions'),$PMDR->getLanguage('admin_locations_impressions_search'));
    fputcsv($fp, $header);
    $limit = 0;
    while($impressions = $db->GetAll("SELECT id, title, impressions, impressions_search FROM ".T_LOCATIONS." WHERE id!=1 AND (impressions > 0 OR impressions_search > 0) ORDER BY impressions DESC LIMIT $limit,1000")) {
        foreach($impressions AS $impression) {
            fputcsv($fp, $impression,',','"');
        }
        $limit += 1000;
    }
    fclose($fp);
    $PMDR->get('ServeFile')->serve(TEMP_UPLOAD_PATH.'location_impressions.csv');
    exit();
}

if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_locations_statistics.tpl');
    $impressions = $db->GetAll("SELECT id, title, impressions FROM ".T_LOCATIONS." WHERE id!=1 AND impressions > 0 ORDER BY impressions DESC LIMIT 10");
    $template_content->set('impressions',$impressions);
    $impressions_search = $db->GetAll("SELECT id, title, impressions_search FROM ".T_LOCATIONS." WHERE id!=1 AND impressions_search > 0 ORDER BY impressions_search DESC LIMIT 10");
    $template_content->set('impressions_search',$impressions_search);
    $counts = $db->GetAll("SELECT id, title, count FROM ".T_LOCATIONS." WHERE id!=1 AND count > 0 ORDER BY count DESC LIMIT 10");
    $template_content->set('counts',$counts);

    $template_content->set('title',$PMDR->getLanguage('admin_locations_statistics'));
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_locations_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>