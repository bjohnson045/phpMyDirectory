<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_invoices','admin_gateways','admin_transactions'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_gateway_log');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

$table_list = $PMDR->get('TableList');
$table_list->addColumn('date',$PMDR->getLanguage('admin_gateways_log_date'));
$table_list->addColumn('gateway',$PMDR->getLanguage('admin_gateways_log_name'));
$table_list->addColumn('data',$PMDR->getLanguage('admin_gateways_log_data'));
$table_list->addColumn('result',$PMDR->getLanguage('admin_gateways_log_result'));
$table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_GATEWAYS_LOG));
$records = $db->GetAll("SELECT * FROM ".T_GATEWAYS_LOG." ORDER BY date DESC");
foreach($records as &$record) {
    $record['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    $record['data'] = '<textarea class="textarea" readonly="readonly" style="width: 400px">'.$record['data'].'</textarea>';
}
$table_list->addRecords($records);

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$PMDR->getLanguage('admin_gateways_log'));
$template_content->set('content',$table_list->render());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_invoices_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>