<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_languages'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_languages_edit');

$script = '
<script type="text/javascript">
var translateOnComplete = function(data) {
    $("#status").progressbar("option", "value", data.percent);
    $("#status_percent").html(data.percent+"%");
    if(data.percent == 100) {
        $("#status").progressbar("destroy");
        $("#status_percent").hide();
        $("#status").html(\''.$PMDR->getLanguage('admin_languages_translate_complete',array('<a href="'.BASE_URL_ADMIN.'/admin_languages_translate.php?action=download&to=\'+data.to+\'">Language_\'+data.to+\'.csv</a>')).'\');
    } else {
        translateStart(data.start+data.num,data.num,data.to);
    }
};

var translateStart = function(start,num,to) {
    if(start == 0) {
        $("#translate_form").hide();
        $("#status_percent").html("0%");
        $("#status").progressbar({ value: 0 });
    }
    $.ajax({ data: ({ action: "admin_languages_translate", start: start, num: num, to: to }), success: translateOnComplete, dataType: "json"});
};

$(document).ready(function(){
    showLoadingMessage("'.$PMDR->getLanguage('admin_languages_key_checking').'");
    $.ajax({
        cache: false,
        data: ({
            action: "admin_languages_translate_check"
        }),
        success: function(result) {
            hideLoadingMessage();

            // Show the result div. I.E. #key_invalid
            if(result == "valid") {
                $("#submit").removeAttr("disabled");
                addMessage("success","'.$PMDR->getLanguage('admin_languages_key_valid').'");
            } else if(result == "disabled") {
                addMessage("error","'.$PMDR->getLanguage('admin_languages_key_disabled').'");
            } else if(result == "invalid") {
                addMessage("error","'.$PMDR->getLanguage('admin_languages_key_invalid').'");
            } else if(result == "error") {
                addMessage("error","'.$PMDR->getLanguage('admin_languages_key_error').'");
            }
        },
        dataType: "json"
    });
});
</script>';
$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_languages_translator'));

if($_GET['action'] == 'download') {
    $serve = $PMDR->get('ServeFile');
    $serve->serve('Language_'.$_GET['to'].'.csv',file_get_contents(TEMP_UPLOAD_PATH.'Language_'.$_GET['to'].'.csv'));
}

if(!isset($_GET['action'])) {
    $form = $PMDR->get('Form');
    $form->setName('translate_form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_languages_translator')));
    $form->addField('service','select',array('label'=>$PMDR->getLanguage('admin_languages_translate_service'),'fieldset'=>'information','value'=>'','options'=>array('google'=>'Google')));
    $form->addField('to','select',array('label'=>$PMDR->getLanguage('admin_languages_translate_to'),'fieldset'=>'information','value'=>'','options'=>$PMDR->get('Google_Translate')->language_reference));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit','onclick'=>'translateStart(0,10,$(\'#to\').val()); return false;','disabled'=>'disabled'));
    $markup = '<div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div>';
    $template_content->set('content',$script.$form->toHTML().$markup);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_languages_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>