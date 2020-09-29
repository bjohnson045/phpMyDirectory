<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_templates_view');

/** @var Templates */
$templates = $PMDR->get('Templates');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_templates_delete');
    $templates->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_templates'))),'delete');
    redirect();
}

if($_GET['action'] == 'sync') {
    $non_writable_files= $templates->sync($_GET['id']);
    if(count($non_writable_files) > 0) {
        foreach($non_writable_files as $file) {
            $PMDR->addMessage('error',$PMDR->getLanguage('messages_not_writable',$file));
        }
    } else {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_templates_synced'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_templates_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_templates_description'));
    $table_list->addColumn('author',$PMDR->getLanguage('admin_templates_author'));
    $table_list->addColumn('folder',$PMDR->getLanguage('admin_templates_folder'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'),false,true);

    $table_list->setTotalResults($templates->getCount());
    $records = $templates->getRowsLimit($table_list->page_data['limit1'],$table_list->page_data['limit2']);

    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $records[$key]['author'] = $PMDR->get('Cleaner')->clean_output($record['author']);
        $records[$key]['folder'] = $PMDR->get('Cleaner')->clean_output($record['folder']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('sync',array('label'=>$PMDR->getLanguage('admin_templates_sync'),'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\');','href'=>URL_NOQUERY.'?action=sync&id='.$record['id']));
        if($PMDR->getConfig('template') != $record['folder']) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_templates'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_templates_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_templates')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_templates_title'),'fieldset'=>'information'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_templates_description'),'fieldset'=>'information'));
    $form->addField('author','text',array('label'=>$PMDR->getLanguage('admin_templates_author'),'fieldset'=>'information'));
    $form->addField('folder','text',array('label'=>$PMDR->getLanguage('admin_templates_folder'),'fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('folder',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_templates_edit'));
        $form->loadValues($templates->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_templates_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $templates->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_templates'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $templates->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_templates'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_templates_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>