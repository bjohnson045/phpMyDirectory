<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_templates_view');

$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/ckeditor/ckeditor.js"></script>',20);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/ckeditor/adapters/jquery.js"></script>',25);

/** @var Templates_Data */
$templates_data = $PMDR->get('Templates_Data');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_templates_delete');
    $templates_data->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_templates_files'))),'delete');
    redirect();
}

if($_GET['action'] == 'revert') {
    $PMDR->get('Authentication')->checkPermission('admin_templates_edit');
    $templates_data->revert($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_templates_file_reverted'));
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('status',$PMDR->getLanguage('admin_templates_status'));
    $table_list->addColumn('template_id',$PMDR->getLanguage('admin_templates_id'));
    $table_list->addColumn('name',$PMDR->getLanguage('admin_templates_name'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_templates_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));

    if(isset($_GET['template']) AND isset($_GET['keyword'])) {
        $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) AS count FROM ".T_TEMPLATES_DATA." WHERE template_id=? AND contents LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%"),array($_GET['template'])));
        $records = $db->GetAll("SELECT * FROM ".T_TEMPLATES_DATA." WHERE template_id=? AND contents LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%")." OR name LIKE ".$PMDR->get('Cleaner')->clean_db("%".$_GET['keyword']."%")." LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2'],array($_GET['template']));
    } else {
        $table_list->setTotalResults($templates_data->getCount());
        $records = $templates_data->getRowsLimit($table_list->page_data['limit1'],$table_list->page_data['limit2']);
    }

    foreach($records as $key=>$record) {
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        if($record['flagged']) {
            $active = '3';
        } elseif(md5($record['contents']) != md5($record['contents_default'])) {
            $active = '2';
        } else {
            $active = '0';
        }
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);

        $records[$key]['status'] = '<div class="icon icon_'.$active.'"></div>';

        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if($active != 0) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('arrow_revert',array('href'=>URL_NOQUERY.'?action=revert&id='.$record['id'],'onclick'=>'return confirm(\''.$PMDR->getLanguage('messages_confirm').'\');','label'=>$PMDR->getLanguage('admin_templates_revert')));
        }
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_templates_files'));
    $legend = '<p>'.$PMDR->get('HTML')->icon('0').' '.$PMDR->getLanguage('admin_templates_not_modified').'</p>';
    $legend .= '<p>'.$PMDR->get('HTML')->icon('2').' '.$PMDR->getLanguage('admin_templates_modified').'</p>';
    $legend .= '<p>'.$PMDR->get('HTML')->icon('3').' '.$PMDR->getLanguage('admin_templates_modified_updated').'</p>';
    $template_content->set('content',$legend.$table_list->render()->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_templates_edit');
    /** @var Form */
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_templates_files')));
    $form->addField('name','custom',array('label'=>$PMDR->getLanguage('admin_templates_name'),'fieldset'=>'information'));
    $form->addField('date','custom',array('label'=>$PMDR->getLanguage('admin_templates_date'),'fieldset'=>'information'));
    $form->addField('contents','textarea',array('label'=>$PMDR->getLanguage('admin_templates_content'),'spellcheck'=>'false','fieldset'=>'information','style'=>'width: 600px; height: 250px; font-family: Courier New','fullscreen'=>true));
    // Allow all HTML tags when editing template data
    $form->setAllowedHTML('contents', null, null);
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($_GET['action'] == 'edit') {
        $data = $templates_data->getRow($_GET['id']);
        $template = $db->GetRow("SELECT id, folder FROM ".T_TEMPLATES." WHERE id=?",array($data['template_id']));
        $file_name = '/template/'.$template['folder'].'/'.($data['subfolder'] != '' ? $data['subfolder'].'/' : '').$data['name'];
        if(strtotime($data['date']) < filemtime(PMDROOT.$file_name)) {
            $file_contents = file_get_contents(PMDROOT.$file_name);
            if($data['contents'] != $file_contents) {
                $data['contents'] = $file_contents;
                if(!$form->wasSubmitted('submit')) {
                    $PMDR->addMessage('notice','This template was loaded from file because it was modified last.');
                }
            }
            unset($file_contents);
        }
        $template_content->set('title',$PMDR->getLanguage('admin_templates_file_edit'));
        $data['date'] = ($PMDR->get('Dates')->isZero($data['date'])) ? '-' : $PMDR->get('Dates_Local')->formatDateTime($data['date']);
        $form->loadValues($data);
        $form->addField('submit_edit','submit',array('label'=>$PMDR->getLanguage('admin_submit_reload'),'fieldset'=>'submit'));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_templates_file_add'));
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_edit')) {
        if(DEMO_MODE) {
            $PMDR->addMessage('error','Template file editing is disabled in the demo.');
        } else {
            $data = $form->loadValues();
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                if($_GET['action']=='add') {
                    $templates_data->insert($data);
                    $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_templates_files'))),'insert');
                    redirect();
                } elseif($_GET['action'] == 'edit') {
                    $templates_data->update($data, $_GET['id']);
                    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_templates_files'))),'update');
                    if($form->wasSubmitted('submit_edit')) {
                        redirect(URL);
                    } else {
                        redirect();
                    }
                }
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_templates_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>