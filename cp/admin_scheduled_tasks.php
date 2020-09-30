<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_scheduled_tasks','admin_maintenance'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_scheduled_tasks.tpl');

$table_list = $PMDR->get('TableList');
$table_list->all_one_page = true;
$table_list->addColumn('id');
$table_list->addColumn('period');
$table_list->addColumn('run_date');
$table_list->addColumn('last_run_date');
$table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_CRON));
$records = $db->GetAll("SELECT * FROM ".T_CRON." ORDER BY id");
foreach($records as $key=>$record) {
    $records[$key]['id'] = $PMDR->getLanguage('admin_scheduled_tasks_'.substr_replace($record['id'],'',0,5));
    $records[$key]['run_date'] = $PMDR->get('Dates_Local')->formatDateTime($record['run_date']);
    $records[$key]['last_run_date'] = $PMDR->get('Dates_Local')->formatDateTime($record['last_run_date']);
    if(abs($record['minute']) > 0) {
        $records[$key]['period'] = sprintf($PMDR->getLanguage('admin_scheduled_tasks_every_minute'),abs($record['minute']));
    } elseif(abs($record['hour']) > 0) {
        $records[$key]['period'] = sprintf($PMDR->getLanguage('admin_scheduled_tasks_every_hour'),abs($record['hour']));
    } elseif(abs($record['day']) > 0) {
        $records[$key]['period'] = sprintf($PMDR->getLanguage('admin_scheduled_tasks_every_day'),abs($record['day']));
    } else {
        $records[$key]['period'] = '-';
    }
}
$table_list->addRecords($records);
$template_content->set('content',$table_list->render());

if($_GET['run'] == 'true') {
    $PMDR->addMessage('success',$PMDR->getLanguage('completed'));
    $template_content->set('cron',true);
} else {
    $template_content->set('cron',false);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>