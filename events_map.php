<?php
// Define the current section being accessed
define('PMD_SECTION', 'public');

// Include initialization script
include('./defaults.php');

// Load language variables from database based on section
$PMDR->loadLanguage(array('public_events'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_events_events_map'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('events_map_meta_title'),$PMDR->getLanguage('public_events_events_map')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('events_map_meta_description'),$PMDR->getLanguage('public_events_events_map')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/events_map.php','text'=>$PMDR->getLanguage('public_events_events_map')));

$template_content = $PMDR->get('Template',PMDROOT.TEMPLATE_PATH.'events_map.tpl');

$map = $PMDR->get('Map');
$map->mapID = 'map_full';
$map->setZoomLevel(3);
$coordinates = explode(',',$PMDR->getConfig('map_select_coordinates'));
$map->setCenterCoords($coordinates[1],$coordinates[0]);
$map->viewportLoader = true;
$map->viewportLoaderSource = 'map_events';

$PMDR->loadJavascript($map->getHeaderJS());
$PMDR->loadJavascript($map->getMapJS());
$PMDR->setAdd('javascript_onload','mapOnLoad();');

$map_output = $map->getMap();

$template_content->set('map',$map_output);

include(PMDROOT.'/includes/template_setup.php');
?>