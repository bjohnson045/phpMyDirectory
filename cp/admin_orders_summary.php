<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_orders','admin_users','admin_products','general_locations','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_orders_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders.tpl');

if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('orders'));
}

if(!$order = $PMDR->get('Orders')->getRow($_GET['id'])) {
    redirect();
}

$email_form = $PMDR->getNew('Form');
$email_options = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type='order' AND id NOT LIKE 'admin_%' AND id NOT IN('cancellation_request_response')");
foreach($email_options AS $id) {
    $email_options[$id] = $PMDR->getLanguage('email_templates_'.$id.'_name');
}
$email_form->addField('email','select',array('label'=>'','options'=>$email_options,'first_option'=>array(''=>'')));
$email_form->addField('submit_email','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$template_content->set('listing_header',$PMDR->get('Listing',$order['type_id'])->getAdminHeader('order'));

$order['upgrades'] = explode(',',$order['upgrades']);
$user = $PMDR->get('Users')->getRow($order['user_id']);
$template_content->set('title',$PMDR->getLanguage('admin_orders_order'));
$template_order = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders_view.tpl');

$template_order->set('invoices',$db->GetAll("SELECT * FROM ".T_INVOICES." WHERE order_id=? ORDER BY date LIMIT 10",array($order['id'])));

if($order['discount_code']) {
    $order['discount'] = $order['discount_code'].' - ';
    if($order['discount_code_discount_type'] == 'fixed') {
        $order['discount'] .= format_number_currency($order['discount_code_value']);
    } else {
        $order['discount'] .= $order['discount_code_value'].'%';
    }
    $order['discount'] .= ' ('.$order['discount_code_type'].')';
}
if(!is_null($order['pricing_id'])) {
    $order = array_merge($order,$db->GetRow("SELECT p.name AS product_name, p.id AS product_id FROM ".T_PRODUCTS." p, ".T_PRODUCTS_PRICING." pp  WHERE p.id=pp.product_id AND pp.id=?",array($order['pricing_id'])));
}
if($order['type'] == 'listing_membership') {
    $order = array_merge($order,(array) $db->GetRow("SELECT l.title AS product_title, l.status AS product_status FROM ".T_LISTINGS." l WHERE l.id=?",array($order['type_id'])));
    $order['product_type'] = $PMDR->getLanguage('admin_products_types_listing_membership');
}

$template_order->set('order',$order);

$form = $PMDR->getNew('Form');

$status_options = array(
    'active'=>$PMDR->getLanguage('active'),
    'completed'=>$PMDR->getLanguage('completed'),
    'pending'=>$PMDR->getLanguage('pending'),
    'suspended'=>$PMDR->getLanguage('suspended'),
    'canceled'=>$PMDR->getLanguage('canceled'),
    'fraud'=>$PMDR->getLanguage('fraud')
);

$template_order->set('status',$status_options[$order['status']]);
$template_order->set('period',$PMDR->getLanguage($order['period']));
$template_order->set('product_status',$status_options[$order['product_status']]);
$template_order->set('date',$PMDR->get('Dates_Local')->formatDate($order['date']));
$template_order->set('next_due_date',$PMDR->get('Dates_Local')->formatDate($order['next_due_date']));
if(!$PMDR->get('Dates')->isZero($order['next_invoice_date'])) {
    $template_order->set('next_invoice_date',$PMDR->get('Dates_Local')->formatDate($order['next_invoice_date']));
    $template_order->set('next_invoice_creation',$PMDR->get('Dates_Local')->formatDate($PMDR->get('Dates')->dateSubtract($order['next_invoice_date'],$PMDR->getConfig('invoice_generation_days'))));
}
if($order['amount_recurring'] != '0.00') {
    $template_order->set('amount_recurring',format_number_currency($order['amount_recurring']));
}
if($order['suspend_overdue_days'] > 0) {
    $template_order->set('suspend_date',$PMDR->get('Dates_Local')->formatDate($PMDR->get('Dates')->dateAdd($order['next_due_date'],$order['suspend_overdue_days'])));
} else {
    $template_order->set('suspend_date',0);
}

if($email_form->wasSubmitted('submit_email')) {
    $data = $email_form->loadValues();
    if(!$email_form->validate()) {
        $PMDR->addMessage('error',$email_form->parseErrorsForTemplate());
    } else {
        $variables = array();
        $PMDR->get('Email_Templates')->send($data['email'],array('to'=>$order['user_id'],'order_id'=>$order['id']));
        $PMDR->addMessage('success',$PMDR->getLanguage('admin_orders_email_sent'));
        redirect(URL);
    }
}

$template_order->set('form',$form->toHTML());
$template_order->set('email_form',$email_form);
$template_content->set('content',$template_order);

if(!isset($_GET['user_id']) OR empty($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_orders_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>