<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_lists','admin_email_campaigns','admin_email_queue','email_templates','admin_email_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_manager');

$mail_queue = $PMDR->get('Email_Queue');

if($_GET['action'] == 'approve') {
    $PMDR->get('Email_Queue')->approve($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_queue_approved'));
    redirect();
}

if($_GET['action'] == 'process') {
    $sent_number = $PMDR->get('Email_Queue')->processEmail($_GET['id']);
    if($sent_number) {
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_queue_processed',array($sent_number)));
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_email_queue_sent',array('0')));
    }
    redirect();
}

if($_GET['action'] == 'empty') {
    $PMDR->get('Email_Queue')->emptyQueue();
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_queue_emptied'));
    redirect();
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Email_Queue')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_queue'))),'delete');
    redirect();
}

if(isset($_POST['table_list_submit'])) {
    if($_POST['action'] == 'delete') {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Email_Queue')->delete($id);
        }
    }
    if($_POST['action'] == 'approve') {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Email_Queue')->approve($id);
        }
    }
    if($_POST['action'] == 'process') {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Email_Queue')->processEmail($id);
        }
    }
    $PMDR->addMessage('success','Email queue updated.');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

$form = $PMDR->get('Form');
$form->addField('process_number','text');
$form->addField('send','submit',array('label'=>$PMDR->getLanguage('admin_submit')));

$form->addValidator('process_number',new Validate_NonEmpty());
$form->addValidator('process_number',new Validate_Numeric());

if($form->wasSubmitted('send')) {
    $data = $form->loadValues();
    if(file_exists(PMDROOT.'/files/temp/cronlock/')){
        $form->addError($PMDR->getLanguage('admin_email_queue_locked'));
    }
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        if($sent_number = $mail_queue->processQueue($data['process_number'])) {
            $PMDR->addMessage('success',$PMDR->getLanguage('admin_email_queue_sent',$sent_number));  // Add language variable
        } else {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_email_queue_none_queued')); // Add language variable
        }
    }
}

$table_list = $PMDR->get('TableList');
$table_list->addColumn('id');
$table_list->addColumn('campaign_id');
$table_list->addColumn('user_id');
$table_list->addColumn('date_queued');
$table_list->addColumn('manage');
$checkbox_options = array(
    ''=>'',
    'approve'=>$PMDR->getLanguage('admin_email_queue_approve'),
    'process'=>$PMDR->getLanguage('admin_email_queue_process'),
    'delete'=>$PMDR->getLanguage('admin_delete')
);
$table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$checkbox_options)));

$where = array();
$where[] =  'q.user_id=u.id';
if(value($_GET,'action') == 'moderated') {
    $where[] = 'moderate=1';
}
$where = 'WHERE '.implode(' AND ',$where);

$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS q.*, u.user_first_name, u.user_last_name, u.login FROM ".T_EMAIL_QUEUE." q, ".T_USERS." u $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date_queued ASC')." LIMIT ?,?",array($table_list->paging->limit1,$table_list->paging->limit2));
$table_list->setTotalResults($db->FoundRows());
foreach($records as $key=>$record) {
    $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
    $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
    $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
    if(!is_null($record['campaign_id'])) {
        $records[$key]['campaign_id'] = '<a href="admin_email_campaigns.php?id='.$record['campaign_id'].'&action=edit">'.$record['campaign_id'].'</a>';
    } elseif(!is_null($record['template_id'])) {
        $records[$key]['campaign_id'] = '<a href="admin_email_templates.php?id='.$record['template_id'].'&action=edit">'.$PMDR->getLanguage('email_templates_'.$record['template_id'].'_name').'</a>';
    }
    $records[$key]['manage'] = $PMDR->get('HTML')->icon('eye',array('id'=>'email_queue_message_link'.$record['id'],'href'=>'#','label'=>$PMDR->getLanguage('admin_email_log_view_message'),'target'=>'_blank'));
    if($record['moderate']) {
        $records[$key]['campaign_id'] .= ' <i class="glyphicon glyphicon-flag"></i>';
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('checkmark',array('id'=>'email_queue_approve_link'.$record['id'],'label'=>$PMDR->getLanguage('admin_email_queue_approve'),'href'=>URL_NOQUERY.'?action=approve&id='.$record['id']));
    }
    $records[$key]['manage'] .= $PMDR->get('HTML')->icon('arrow_green',array('label'=>$PMDR->getLanguage('admin_email_queue_process'),'href'=>URL_NOQUERY.'?action=process&id='.$record['id']));
    $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    $records[$key]['date_queued'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_queued']);
    if(is_null($record['date_sent'])) {
        $records[$key]['date_sent'] = '-';
    } else {
        $records[$key]['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_sent']);
    }
}
$table_list->addRecords($records);

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_email_queue.tpl');
$template_content->set('table_list',$table_list->render());
$template_content->set('form',$form);

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>