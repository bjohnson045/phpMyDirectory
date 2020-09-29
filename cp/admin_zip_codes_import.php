<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_zip_codes'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_zip_codes_import');

// Load template and set title
$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_zip_codes_import'));

if(!isset($_GET['action'])) {
    $form = $PMDR->get('Form');
    $form->setName('import_form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_zip_codes_import_file')));
    $form->addField('file_path','text',array('label'=>$PMDR->getLanguage('admin_zip_codes_import_file'),'fieldset'=>'information','value'=>PMDROOT.'/','help'=>$PMDR->getLanguage('admin_zip_codes_import_file_help')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('file_path',new Validate_NonEmpty());

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!is_file($data['file_path'])) {
            $form->addError($PMDR->getLanguage('admin_zip_codes_import_not_found'),'file_path');
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            redirect(array('action'=>'import','file_path'=>$data['file_path']));
        }
    }
    $template_content->set('content',$form->toHTML());
} else {
    $script = '
    <script type="text/javascript">
    var zipImportOnComplete = function(data) {
        $("#status").progressbar("option", "value", data.percent);
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            $("#status").progressbar("destroy");
            $("#status_percent").hide();
            window.location.replace("'.BASE_URL_ADMIN.'/admin_zip_codes.php");
        } else {
            zipImportStart(data.start,data.num);
        }
    };

    var zipImportStart = function(start,num) {
        if(start == 0) {
            $("#status_percent").html("0%");
            $("#status").progressbar({ value: 0 });
        }
        $.ajax({ data: ({ action: "admin_zip_codes_import", start: start, num: num, file_path: "'.$_GET['file_path'].'" }), success: zipImportOnComplete, dataType: "json"});
    };
    $(document).ready(function() {
        zipImportStart(0,4096);
    });
    </script>';
    $template_content->set('content',$script.'<div style="width: 500px; float: left;" id="status"></div><div style="float: left; margin: 5px 0 0 10px; font-weight: bold;" id="status_percent"></div>');
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_zip_codes_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>