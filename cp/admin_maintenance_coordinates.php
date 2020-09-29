<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$script = '
<script type="text/javascript">
var coordinatesOnComplete = function(data) {
    $("#status").progressbar("option", "value", data.percent);
    $("#status_percent").html(data.percent+"%");
    if(data.percent == 100) {
        $("#status").progressbar("destroy");
        $("#status_percent").hide();
        addMessage("success","'.$PMDR->getLanguage('admin_maintenance_coordinates_processed').' "+data.count,"status_container");
    } else {
        coordinatesStart(data.count, data.max, data.possible);
    }
};

var coordinatesStart = function(count, max, possible) {
    if(count == 0) {
        $("#status_percent").html("0%");
        $("#status").progressbar({ value: 0 });
    }
    $.ajax({ data: ({ action: "admin_maintenance_coordinates", count: count, max: max, possible: possible }), success: coordinatesOnComplete, dataType: "json"});
};

$(document).ready(function(){
    coordinatesStart(0, 1000, 0);
});
</script>';

// Fix title and output
$template_content->set('title',$PMDR->getLanguage('admin_maintenance_coordinates'));
$template_content->set('content',$script.'<div id="status_container"><div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div></div>');

$PMDR->addMessage('notice','Running repeatedly may cause the geocoder to block your website.');

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>