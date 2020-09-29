<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$script = '
<script type="text/javascript">
var rebuildOnComplete = function(data) {
    $("#status").progressbar("option", "value", data.percent);
    $("#status_percent").html(data.percent+"%");
    if(data.percent == 100) {
        $("#status").progressbar("destroy");
        $("#status_percent").hide();
        addMessage("success","'.$PMDR->getLanguage('admin_maintenance_recounted').'","status_container");
    } else {
        rebuildStart(data.start+data.num,data.num);
    }
};

var rebuildStart = function(start,num) {
    if(start == 0) {
        $("#status_percent").html("0%");
        $("#status").progressbar({ value: 0 });
    }
    $.ajax({ data: ({ action: "admin_maintenance_recount_listings", start: start, num: num, }), success: rebuildOnComplete, dataType: "json"});
};

$(document).ready(function(){
    rebuildStart(0,1);
});
</script>';

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_recount'));
$template_content->set('content',$script.'<div id="status_container"><div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div></div>');

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>