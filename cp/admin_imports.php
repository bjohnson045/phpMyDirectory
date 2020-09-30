<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_imports'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_import');

/** @var Imports */
$imports = $PMDR->get('Imports');

$import_running = $db->GetOne("SELECT COUNT(*) FROM ".T_IMPORTS." WHERE status='running'");

if($_GET['action'] == 'pause') {
    $db->Execute("UPDATE ".T_IMPORTS." SET status='paused' WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_imports'))),'update');
    redirect();
}

if($_GET['action'] == 'resume') {
    $db->Execute("UPDATE ".T_IMPORTS." SET status='running' WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_imports'))),'update');
    redirect();
}

if($_GET['action'] == 'delete') {
    $imports->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_imports'))),'delete');
    redirect();
}

if($_GET['action'] == 'import') {
    if(!$import_running) {
        $import = $db->GetRow("SELECT * FROM ".T_IMPORTS." WHERE id=?",array($_GET['id']));
        $import['data'] = unserialize($import['data']);
        $import['data']['statistics'] = array();
        $imports->update(
            array(
                'status'=>'running',
                'date'=>$PMDR->get('Dates')->dateTimeNow(),
                'import_count'=>0,
                'error_count'=>0,
                'position'=>0,
                'position_line'=>1,
                'rate'=>100,
                'date_activity'=>'',
                'date_complete'=>'',
                'data'=>serialize($import['data'])
            ),
            $_GET['id']
        );
        $imports->clear($_GET['id']);
        redirect('admin_import.php',array('action'=>'import','id'=>$_GET['id']));
    } else {
        $PMDR->addMessage('error','Only one import may be running at a time.');
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('name',$PMDR->getLanguage('admin_imports_name'));
    $table_list->addColumn('import_count',$PMDR->getLanguage('admin_imports_count'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_imports_date'));
    $table_list->addColumn('status',$PMDR->getLanguage('admin_imports_status'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($imports->getCount());
    $records = $imports->getRowsLimit($table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['status'] = $PMDR->getLanguage('admin_imports_'.$record['status']);
        if($record['status'] == 'running') {
            $records[$key]['status'] .= ' <a class="btn btn-xs btn-warning" href="admin_imports.php?action=pause&id='.$record['id'].'">'.$PMDR->getLanguage('admin_imports_pause').'</a>';
            $percent_complete = floor(($record['position']*100)/filesize(TEMP_UPLOAD_PATH.'imports_'.$record['id'].'.csv'));
            $records[$key]['status'] .= '<br><small>'.$PMDR->getLanguage('admin_imports_percent_complete',array($percent_complete)).'</small>';
            $minutes_past = (time() - strtotime($record['date']))/60;
            $estimated_minutes = @ceil(((((time() - strtotime($record['date']))/60)*100) / $percent_complete) - $minutes_past);
            if($estimated_minutes < 1) {
                $estimated_minutes = 1;
            }
            $records[$key]['status'] .= '<br><small>'.$PMDR->getLanguage('admin_imports_minutes_remaining',array($estimated_minutes)).'</small>';
        }
        if($record['status'] == 'paused') {
            $records[$key]['status'] .= ' <a class="btn btn-xs btn-success" href="admin_imports.php?action=resume&id='.$record['id'].'">'.$PMDR->getLanguage('admin_imports_resume').'</a>';
        }
        if($record['status'] == 'pending' AND $record['scheduled']) {
            $records[$key]['status'] .= ' <i class="glyphicon glyphicon-time" title="Scheduled"></i>';
        }
        if($record['notifications']) {
            $records[$key]['name'] .= ' <i class="glyphicon glyphicon-envelope" title="Notifications On"></i>';
        }
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if($record['import_count'] > 0) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('doc',array('target'=>'_blank','href'=>get_file_url(TEMP_UPLOAD_PATH.'import_'.$record['id'].'_log.txt'),'label'=>'Log'));
        }
        if($record['error_count'] > 0) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('error',array('target'=>'_blank','href'=>get_file_url(TEMP_UPLOAD_PATH.'import_'.$record['id'].'_errors.txt'),'label'=>$PMDR->getLanguage('admin_import_errors')));
        }
        if($record['status'] != 'running' AND !$import_running AND (!$record['scheduled'] OR $record['status'] == 'complete')) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('arrow_green',array('label'=>$PMDR->getLanguage('admin_imports_reimport'),'href'=>URL_NOQUERY.'?action=import&id='.$record['id'],'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\')'));
        }
        if($record['status'] != 'running') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_imports'));
    $template_content->set('content',$table_list->render());
} elseif($_GET['action'] == 'edit') {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_imports_import')));
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_imports_name'),'fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('name',new Validate_NonEmpty());

    $template_content->set('title',$PMDR->getLanguage('admin_imports_edit'));
    $form->loadValues($imports->getRow($_GET['id']));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $imports->update($data, $_GET['id']);
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_imports'))),'update');
            redirect();
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_imports_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>