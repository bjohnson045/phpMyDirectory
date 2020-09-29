<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_orders','user_invoices','user_listings'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$order = $db->GetRow("SELECT
    id,
    order_id,
    type,
    type_id,
    pricing_id,
    upgrades,
    date,
    next_due_date,
    next_invoice_date,
    status,
    amount_recurring,
    period,
    period_count,
    renewable,
    subscription_id
 FROM ".T_ORDERS." WHERE id=? AND user_id=?",array($_GET['id'],$user['id']));


if(!$order) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

$PMDR->setAdd('page_title',$order['order_id'].' '.$PMDR->getLanguage('user_orders_information'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_orders')));
$PMDR->setAddArray('breadcrumb',array('text'=>$order['order_id'].' '.$PMDR->getLanguage('user_orders_information')));

$product_labels = $db->GetRow("SELECT pg.name AS group_name, p.name AS name, pp.label FROM ".T_PRODUCTS_GROUPS." pg INNER JOIN ".T_PRODUCTS." p ON pg.id=p.group_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=?",$order['pricing_id']);

if($order['type'] == 'listing_membership') {
    $PMDR->set('page_header',$PMDR->get('Listing',$order['type_id'])->getUserHeader('order'));
    $listing = $db->GetRow("SELECT * FROM ".T_LISTINGS." WHERE id=?",array($order['type_id']));
    $order['product_status'] = $PMDR->getLanguage($listing['status']);
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_orders_view.tpl');
$order['date'] = $PMDR->get('Dates_Local')->formatDateTime($order['date']);
$order['product_group_name'] = $product_labels['group_name'];
$order['product_name'] = $product_labels['name'];
if($order['upgrades'] != '' AND $order['status'] != 'pending') {
    $order['upgrades_link'] = true;
}
$order['product_title'] = $product['title'];
if($PMDR->get('Dates')->isZero($record['next_due_date'])) {
     $order['next_due_date'] = '-';
} else {
     $order['next_due_date'] = $PMDR->get('Dates_Local')->formatDate($record['next_due_date']);
    if(strtotime($record['next_due_date']) < time() AND $record['renewable']) {
         $order['overdue'] = true;
        if($record['amount_recurring'] == 0.00 AND !$db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($record['id']))) {
             $order['renew'] = true;
        }
    }
}
if($PMDR->get('Dates')->isZero($order['next_invoice_date'])) {
    $order['next_invoice_date'] = '-';
} else {
    $order['next_invoice_date'] = $PMDR->get('Dates_Local')->formatDate($order['next_invoice_date']);
}

$order['status'] = $PMDR->getLanguage($order['status']);

if($product_labels['label']) {
    $order['amount'] = $price['label'];
} else {
    if($order['period_count']) {
        $order['amount'] = $order['period_count'].' '.$PMDR->getLanguage('user_orders_'.$order['period']);
    } else {
        $order['amount'] = $PMDR->getLanguage('user_orders_lifetime');
    }
    if($order['amount_recurring'] != '0.00') {
        $order['amount'] .= ' - '.format_number_currency($order['amount_recurring']);
    } else {
        $order['amount'] .= ' - '.$PMDR->getLanguage('user_orders_free');
    }
}
$order['amount_recurring'] = format_number_currency($order['amount_recurring']);
$order['subscription_id'] =  $order['subscription_id'] != '' ? $order['subscription_id'] : '-';

$invoices = $db->GetAll("SELECT i.id, i.date_due, i.total-IFNULL(SUM(t.amount),0.00) AS balance FROM ".T_INVOICES." i LEFT JOIN ".T_TRANSACTIONS." t ON i.id=t.invoice_id WHERE i.order_id=? GROUP BY i.id ORDER BY date_due ASC LIMIT 10",array($order['id']));
foreach($invoices AS &$invoice) {
    $invoice['date_due']= $PMDR->get('Dates_Local')->formatDate($invoice['date_due']);
    $invoice['balance'] = format_number_currency($invoice['balance']);
}
$template_content->set('invoices',$invoices);

$template_content->set('order',$order);
$template_content->set('product',$template_content_product);
$template_content->set('invoices',$invoices);

include(PMDROOT.'/includes/template_setup.php');
?>
