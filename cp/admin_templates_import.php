<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_templates_edit');

/** @var Templates */
$templates = $PMDR->get('Templates');

if($_GET['action'] == 'import') {
    if(DEMO_MODE) {
        $PMDR->addMessage('error','Template importing is disabled in the demo.');
        redirect_url(BASE_URL_ADMIN.'/admin_templates.php');
    } elseif($templates->importFromFolder($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_templates_import_success'));
        redirect_url(BASE_URL_ADMIN.'/admin_templates.php');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_templates_import_error'));
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_templates_import.tpl');

$form = $PMDR->get('Form');
$form->action = 'admin_templates_import.php?action=importzip';
$form->enctype = 'multipart/form-data';
$form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_templates_import_zip')));
$form->addField('template_file','file',array('label'=>$PMDR->getLanguage('admin_templates_import_file'),'fieldset'=>'information'));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
$form->addValidator('template_file',new Validate_NonEmpty_File());

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('name',$PMDR->getLanguage('admin_templates_import_file'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));

    $records = $templates->getAllFileTemplates();
    $current_templates = $templates->getCurrentTemplatesArray();

    foreach($records as $key=>$record) {
        if(in_array($record['name'],$current_templates)) {
            unset($records[$key]);
            continue;
        }
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('arrow_green',array('label'=>$PMDR->getLanguage('admin_templates_import'),'href'=>URL_NOQUERY.'?action=import&id='.$record['name']));
    }
    $table_list->setTotalResults(count($records));
    $table_list->addRecords($records);
    $template_content->set('table_list',$table_list->render());
    $template_content->set('form',$form->toHTML());
    $template_content->set('template_folder_writable',is_writable(PMDROOT.'/template/'));
} else {
    if($form->wasSubmitted('submit')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','Template importing is disabled in the demo.');
        } else {
            $form->loadValues();
            if(!is_writable(PMDROOT.'/template/')) {
                $form->addError($PMDR->getLanguage('messages_not_writable',PMDROOT.'/template/'));
            }
            $template_name = substr($_FILES['template_file']['name'],0,strpos($_FILES['template_file']['name'],'.'));
            if(file_exists(PMDROOT.'/template/'.$template_name.'/') AND $_FILES['template_file']['size']) {
                $form->addError($PMDR->getLanguage('admin_templates_import_exists',$template_name));
            }
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
                redirect();
            } else {
                move_uploaded_file($_FILES['template_file']['tmp_name'],TEMP_UPLOAD_PATH.$_FILES['template_file']['name']);
                /** @var PclZip */
                $zip = $PMDR->get('Zip',TEMP_UPLOAD_PATH.$_FILES['template_file']['name']);
                $zip_file_list = $zip->extract(PCLZIP_OPT_PATH,PMDROOT.'/template/'.$template_name.'/',PCLZIP_OPT_REMOVE_PATH, $template_name);
                if(!$zip->errorCode() AND $templates->importFromFolder($template_name)) {
                    $PMDR->addMessage('success',$PMDR->getLanguage('admin_templates_import_success'));
                } else {
                    $PMDR->addMessage('error',$PMDR->getLanguage('admin_templates_import_error'));
                }
                unlink(TEMP_UPLOAD_PATH.$_FILES['template_file']['name']);
                redirect_url(BASE_URL_ADMIN.'/admin_templates.php');
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_templates_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>