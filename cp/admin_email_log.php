<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_log','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_users'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    $PMDR->get('Email_Log')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_log'))),'delete');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_email_log.tpl');

if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('email_log'));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_email_log'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('date');
    $table_list->addColumn('subject');
    $table_list->addColumn('manage');
    $table_list->addSorting(array('date','subject'));
    $paging = $PMDR->get('Paging');
    $where = array();
    if(!empty($_GET['user_id'])) {
        $where[] = 'el.user_id='.$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS el.*, u.user_email FROM ".T_EMAIL_LOG." el INNER JOIN ".T_USERS." u ON el.user_id=u.id $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'el.date DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('doc',array('id'=>'email_log_message_link_'.$record['id'],'href'=>'#','label'=>$PMDR->getLanguage('admin_email_log_view_message')));
        if(!empty($_GET['user_id'])) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id'],'user_id'=>$_GET['user_id']));
        } else {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
}

if(!isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>