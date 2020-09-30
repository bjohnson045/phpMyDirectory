<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_cancellations','email_templates'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_CANCELLATIONS." WHERE id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_cancellations_cancellation'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
// Add ability to simply delete and checkbox to send email or not
if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_cancellations'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id');
    $table_list->addColumn('user_id');
    $table_list->addColumn('order_id');
    $table_list->addColumn('date');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_CANCELLATIONS));
    $records = $db->GetAll("SELECT c.*, u.user_first_name, u.user_last_name, u.login FROM ".T_CANCELLATIONS." c INNER JOIN ".T_USERS." u ON c.user_id=u.id ORDER BY date ASC LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
        $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
        $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        $records[$key]['order_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_orders.php?action=edit&id='.$records[$key]['order_id'].'">'.$records[$key]['order_id'].'</a>';
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_cancellations_cancellation')));
    $form->addField('user_comment','custom',array('label'=>$PMDR->getLanguage('admin_cancellations_request_comment'),'fieldset'=>'information'));
    $options = array(
        'cancel'=>$PMDR->getLanguage('admin_cancellations_cancel'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $form->addField('options','select',array('fieldset'=>'information','first_option'=>'','options'=>$options));
    $form->addField('comment','textarea',array('fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addField('order_id','hidden',array('fieldset'=>'information'));

    $form->addValidator('options',new Validate_NonEmpty());
    $form->addValidator('comment',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_cancellations_edit'));
        $values = $db->GetRow("SELECT * FROM ".T_CANCELLATIONS." WHERE id=?",array($_GET['id']));
        $values['user_comment'] = $values['comment'];
        unset($values['comment']);
        $form->loadValues($values);
        unset($values);
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'edit') {
                $db->Execute("DELETE FROM ".T_CANCELLATIONS." WHERE id=?",array($_GET['id']));
                $PMDR->get('Email_Templates')->send('cancellation_request_response',array('variables'=>$data,'order_id'=>$data['order_id']));
                if($data['options'] == 'delete') {
                    $PMDR->get('Orders')->delete($data['order_id']);
                } elseif($data['options'] == 'cancel') {
                    $PMDR->get('Orders')->changeStatus($data['order_id'],'canceled');
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_cancellation_cancellation'))),'update');
            }
            redirect();
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>