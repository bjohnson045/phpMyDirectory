<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('email_templates','admin_updates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_updates_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_updates'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('user_id',$PMDR->getLanguage('admin_updates_user'));
    //$table_list->addColumn('type',$PMDR->getLanguage('admin_updates_type'));
    $table_list->addColumn('type_id',$PMDR->getLanguage('admin_updates_item_link'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_updates_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_UPDATES));
    $records = $db->GetAll("SELECT up.*, u.user_first_name, u.user_last_name, u.login FROM ".T_UPDATES." up LEFT JOIN ".T_USERS." u ON up.user_id=u.id ORDER BY date ASC LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
        $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
        $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>BASE_URL_ADMIN.'/admin_listings.php?action=edit&id='.$record['type_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('target'=>'_blank','href'=>BASE_URL.'/listing.php?id='.$record['type_id'],'label'=>$PMDR->getLanguage('admin_updates_view')));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('arrow_green',array('href'=>URL_NOQUERY.'?action=process&id='.$record['id'],'label'=>$PMDR->getLanguage('admin_updates_process')));
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_updates_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('update',array('legend'=>$PMDR->getLanguage('admin_updates_update')));
    $form->addField('update_action','radio',array('label'=>$PMDR->getLanguage('admin_updates_process'),'fieldset'=>'update','value'=>'approve','options'=>array('approve'=>$PMDR->getLanguage('admin_updates_approve'),'reject'=>$PMDR->getLanguage('admin_updates_reject'))));
    $form->addField('notify','checkbox',array('label'=>$PMDR->getLanguage('admin_updates_notify'),'fieldset'=>'update','value'=>'1'));
    $form->addField('message','textarea',array('label'=>$PMDR->getLanguage('admin_updates_message'),'fieldset'=>'update'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $template_content->set('title',$PMDR->getLanguage('admin_updates_process'));

    $template_content->set('content',$form->toHTML());

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $update_data = $db->GetRow("SELECT u.user_email, up.id, up.type, up.type_id FROM ".T_USERS." u, ".T_UPDATES." up WHERE u.id = up.user_id AND up.id=?",array($_GET['id']));
            if($data['update_action'] == 'approve') {
                $order = $db->GetRow("SELECT status FROM ".T_ORDERS." WHERE type=? AND type_id=?",array($update_data['type'],$update_data['type_id']));
                if($order['status'] == 'active') {
                    $PMDR->get('Listings')->changeStatus($update_data['type_id'],'active');
                }
                if($data['notify']) {
                    $PMDR->get('Email_Templates')->send('updates_approved',array('to'=>$update_data['user_email'],'variables'=>$data,'listing_id'=>$update_data['type_id']));
                }
            } else {
                if($data['notify']) {
                    $PMDR->get('Email_Templates')->send('updates_rejected',array('to'=>$update_data['user_email'],'variables'=>$data,'listing_id'=>$update_data['type_id']));
                }
                $PMDR->get('Listings')->changeStatus($update_data['type_id'],'suspended');
                $PMDR->addMessage('success',$PMDR->getLanguage('admin_updates_rejected'));
            }
            $db->Execute("DELETE FROM ".T_UPDATES." WHERE id=?",array($update_data['id']));
            redirect();
        }
    }
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>