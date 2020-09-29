<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_log');

$logger = $PMDR->get('Logger');

if($_GET['action'] == 'clear') {
    $db->Execute("TRUNCATE ".T_LOG);
    $db->Execute("OPTIMIZE TABLE ".T_LOG);
    $PMDR->log('delete',$PMDR->getLanguage('admin_log_cleared'));
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_log_cleared'));
}

$table_list = $PMDR->get('TableList');
$table_list->addColumn('action_date',$PMDR->getLanguage('admin_log_date'),true);
$table_list->addColumn('user_id',$PMDR->getLanguage('admin_log_userid'),true);
$table_list->addColumn('ip_address',$PMDR->getLanguage('admin_log_ip'));
$table_list->addColumn('action',$PMDR->getLanguage('admin_log_action'));
$table_list->setTotalResults($logger->getLogCount());

$paging = $PMDR->get('Paging');
$records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_LOG." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'action_date DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
$paging->setTotalResults($db->FoundRows());

foreach($records as $key=>$record) {
    $records[$key]['action_date'] = $PMDR->get('Dates_Local')->formatDateTime($record['action_date']);
    $records[$key]['user_id'] = '<a href="admin_users_summary.php?id='.$record['user_id'].'">'.$record['user_id'].'</a>';
}
$table_list->addPaging($paging);
$table_list->addRecords($records);

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_log'));
$template_content->set('content',$table_list->render());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_log_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>