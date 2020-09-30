<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_orders','user_invoices','user_listings'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_orders'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_orders')));

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if($_GET['action'] == 'copy') {
    $order = $db->GetRow("SELECT user_id, pricing_id, type_id FROM ".T_ORDERS." WHERE id=? AND user_id=?",array($_GET['id'],$user['id']));
    $listing = $db->GetRow("SELECT id, primary_category_id FROM ".T_LISTINGS." WHERE id=?",array($order['type_id']));
    redirect('user_orders_add_listing.php?pricing_id='.$order['pricing_id'].'&user_id='.$order['user_id'].'&create_invoice=0&primary_category_id='.$listing['primary_category_id'].'&copy='.$listing['id']);
}

if($_GET['action'] == 'renew') {
    if($order = $db->GetRow("SELECT id, type FROM ".T_ORDERS." WHERE id=? AND amount_recurring=0.00 AND next_due_date IS NOT NULL AND next_due_date < CURDATE() AND renewable=1",array($_GET['id']))) {
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($_GET['id']))) {
            $PMDR->get('Orders')->renew($order['id'], date('Y-m-d'));
            $PMDR->get('Orders')->changeStatus($order['id'],'active');
            $PMDR->addMessage('success',$PMDR->getLanguage('user_orders_renew_successful'));
        }
    }
}

$table_list = $PMDR->get('TableList');
$table_list->addColumn('order_id',$PMDR->getLanguage('user_orders_id'));
$table_list->addColumn('date',$PMDR->getLanguage('user_orders_date'));
$table_list->addColumn('product',$PMDR->getLanguage('user_orders_product'));
$table_list->addColumn('next_due_date',$PMDR->getLanguage('user_orders_next_due_date'));
$table_list->addColumn('status',$PMDR->getLanguage('user_orders_status'));
$table_list->addColumn('product_status',$PMDR->getLanguage('user_orders_product_status'));
$table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));

$table_list->setTotalResults($PMDR->get('Orders')->getCount(array('user_id'=>$PMDR->get('Session')->get('user_id'))));

if($PMDR->getConfig('approve_update')) {
    $update_ids = $db->GetCol("SELECT type_id FROM ".T_UPDATES." WHERE type='listings' AND user_id=?",array($user['id']));
} else {
    $update_ids = array();
}

$records = $db->GetAll("
    SELECT
        o.*, u.user_first_name, u.user_last_name, pp.label, p.name AS product, l.title, l.status AS product_status, l.friendly_url
    FROM ".T_ORDERS." o
    INNER JOIN ".T_USERS." u ON o.user_id=u.id
    INNER JOIN ".T_PRODUCTS_PRICING." pp ON o.pricing_id=pp.id
    INNER JOIN ".T_PRODUCTS." p ON p.id=pp.product_id
    INNER JOIN ".T_LISTINGS." l ON o.type_id=l.id AND o.type='listing_membership'
    WHERE u.id=? ORDER BY o.date DESC LIMIT ".$table_list->page_data['limit1'].",".$table_list->page_data['limit2'],array($PMDR->get('Session')->get('user_id')));
foreach($records as $key=>$record) {
    if($record['type'] == 'listing_membership') {
        $records[$key]['product_url'] = $PMDR->get('Listings')->getURL($record['type_id'],$record['friendly_url']);
        $records[$key]['product_title'] = $record['title'];
    }
    $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
    if($PMDR->get('Dates')->isZero($record['next_due_date'])) {
        $records[$key]['next_due_date'] = '-';
    } else {
        $records[$key]['next_due_date'] = $PMDR->get('Dates_Local')->formatDate($record['next_due_date']);
        if(strtotime($record['next_due_date']) < time() AND $record['renewable']) {
            $records[$key]['overdue'] = true;
            if($record['amount_recurring'] == 0.00 AND !$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($record['id']))) {
                $records[$key]['renew'] = true;
            }
        }
    }
    if(in_array($record['type_id'],$update_ids)) {
        $records[$key]['product_pending_approval'] = true;
    }
}
$table_list->addRecords($records);

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_orders.tpl');
$table_list->addToTemplate($template_content);

include(PMDROOT.'/includes/template_setup.php');
?>