<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$form = $PMDR->get('Form');
$form->setName('rebuild_form');
$form->addFieldSet('image_type',array('legend'=>$PMDR->getLanguage('admin_maintenance_images_type')));
$types = array(
    'logos'=>$PMDR->getLanguage('admin_maintenance_images_logos'),
    'images'=>$PMDR->getLanguage('admin_maintenance_images_images'),
    'classifieds'=>$PMDR->getLanguage('admin_maintenance_images_classifieds'),
    'profile_images'=>$PMDR->getLanguage('admin_maintenance_profile_images')
);
$form->addField('image','select',array('label'=>$PMDR->getLanguage('admin_maintenance_images_type'),'fieldset'=>'image_type','options'=>$types));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit','onclick'=>'rebuildStart(0,10,$(\'#image\').val()); return false;'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(DEMO_MODE) {
    $PMDR->addMessage('error','Rebuild images disabled in demo.');
}
// $PMDR->getLanguage('admin_maintenance_images_rebuilt_failed')

$script = '
<script type="text/javascript">
var rebuildOnComplete = function(data) {
    $("#status").progressbar("option", "value", data.percent);
    $("#status_percent").html(data.percent+"%");
    if(data.percent == 100) {
        $("#status").progressbar("destroy");
        $("#status_percent").hide();
        addMessage("success","'.$PMDR->getLanguage('admin_maintenance_images_rebuilt').'","status_container");
    } else {
        rebuildStart(data.start+data.num,data.num,data.type);
    }
};

var rebuildStart = function(start,num,type) {
    if(start == 0) {
        $("#rebuild_form").hide();
        $("#status_percent").html("0%");
        $("#status").progressbar({ value: 0 });
    }
    $.ajax({ data: ({ action: "admin_maintenance_images", start: start, num: num, type: type }), success: rebuildOnComplete, dataType: "json"});
};
</script>';

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_images'));
$template_content->set('content',$script.$form->toHTML().'<div id="status_container"><div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div></div>');

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>