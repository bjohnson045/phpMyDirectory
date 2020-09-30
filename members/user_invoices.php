<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_invoices'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php','text'=>$PMDR->getLanguage('user_invoices')));

if($_GET['action'] == 'pdf') {
    $PMDR->get('Invoices')->getPDF($_GET['id'],true);
}

$users = $PMDR->get('Users');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

$table_list = $PMDR->get('TableList');
$table_list->addColumn('id',$PMDR->getLanguage('user_invoices_id'));
$table_list->addColumn('order_id',$PMDR->getLanguage('user_invoices_order_number'));
$table_list->addColumn('date',$PMDR->getLanguage('user_invoices_date'));
$table_list->addColumn('date_due',$PMDR->getLanguage('user_invoices_date_due'));
$table_list->addColumn('total',$PMDR->getLanguage('user_invoices_total'));
$table_list->addColumn('balance',$PMDR->getLanguage('user_invoices_balance'));
$table_list->addColumn('status',$PMDR->getLanguage('user_invoices_payment_status'));
$table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));

$table_list->setTotalResults($PMDR->get('Invoices')->getCount(array('user_id'=>$PMDR->get('Session')->get('user_id'))));
$records = $db->GetAll("
    SELECT
        i.*,
        o.order_id AS order_number,
        o.subscription_id,
        u.user_first_name,
        u.user_last_name,
        i.total-IFNULL(SUM(t.amount),0.00) AS balance
    FROM
        ".T_INVOICES." i INNER JOIN ".T_USERS." u ON i.user_id=u.id
        LEFT JOIN ".T_ORDERS." o ON i.order_id=o.id
        LEFT JOIN ".T_TRANSACTIONS." t ON i.id=t.invoice_id
    WHERE
        u.id=? GROUP BY i.id ORDER BY date DESC LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2'],
    array($user['id']));

$gateways = $db->GetAssoc("SELECT id, display_name FROM ".T_GATEWAYS);

foreach($records as $key=>$record) {
    $records[$key]['order_id'] = $record['order_number'];
    if(is_null($record['order_id'])) {
        $records[$key]['order_id'] = '-';
    }
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDate($record['date']);
    $records[$key]['date_due'] = $PMDR->get('Dates_Local')->formatDate($record['date_due']);
    $records[$key]['total'] = format_number_currency($record['total']);
}
$table_list->addRecords($records);

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_invoices.tpl');
$table_list->addToTemplate($template_content);

include(PMDROOT.'/includes/template_setup.php');
?>