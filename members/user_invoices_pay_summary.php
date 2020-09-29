<?php
define('PMD_SECTION','members');

include ('../defaults.php');
include ('../includes/class_payment_api.php');

$PMDR->loadLanguage(array('user_invoices'));

$PMDR->get('Authentication')->authenticate();

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

// Retreive the order and invoice details from the database
$invoice = $db->GetRow("
SELECT
    i.id,
    i.order_id,
    i.gateway_id,
    i.subtotal,
    i.tax,
    i.total,
    i.discount_code_discount_type,
    i.discount_code_value,
    o.order_id AS order_number,
    o.pricing_id
FROM ".T_INVOICES." i
LEFT JOIN ".T_ORDERS." o ON i.order_id = o.id
WHERE
    i.id=? AND
    i.user_id=? AND
    i.status='unpaid'",array($_SESSION['invoices_pay']['invoice']['id'],$user['id']));

// If the invoice does not exist return the user to the invoices list
if(!$invoice) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
}

$transactions = $db->GetAll("SELECT t.amount FROM ".T_TRANSACTIONS." t WHERE t.invoice_id=?",array($invoice['id']));
$invoice['balance'] = $invoice['total'];

foreach($transactions as $key=>$transaction) {
    $invoice['balance'] -= $transaction['amount'];
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_invoices_pay_invoice'));
$PMDR->setAdd('page_title',$PMDR->getLanguage('user_invoices_summary'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'/user_invoices.php','text'=>$PMDR->getLanguage('user_invoices')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'/user_invoices_pay.php?id='.$invoice['id'],'text'=>$PMDR->getLanguage('user_invoices_pay_invoice')));
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('user_invoices_summary')));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_invoices_pay_summary.tpl');

// Include the payment gateway class file and get the processor object
$gateway = $db->GetRow("SELECT * FROM ".T_GATEWAYS." WHERE id=? AND enabled=1 AND hidden=0",array($invoice['gateway_id']));
include(PMDROOT.'/modules/processors/'.$gateway['id'].'/'.$gateway['id'].'_class.php');
$processor = new $gateway['id']($PMDR);

// Set cookie so we can retreive the proper gateway after returning from the invoice payment
setcookie(COOKIE_PREFIX.'payment_gateway',md5($gateway['id'].LICENSE),0,COOKIE_PATH,COOKIE_DOMAIN);

$form = $PMDR->getNew('Form');

$form->addFieldSet('payment_form',array('legend'=>$PMDR->getLanguage('user_invoices_summary')));
$form->addField('invoice_id','custom',array('label'=>$PMDR->getLanguage('user_invoices_id'),'fieldset'=>'payment_form','html'=>$invoice['id']));
$form->addField('gateway_id','custom',array('label'=>$PMDR->getLanguage('user_invoices_payment_method'),'fieldset'=>'payment_form','html'=>$gateway['display_name']));
$form->addField('invoice_subtotal','custom',array('label'=>$PMDR->getLanguage('user_invoices_subtotal'),'fieldset'=>'payment_form','html'=>format_number_currency($invoice['subtotal'])));
$form->addField('invoice_tax','custom',array('label'=>$PMDR->getLanguage('user_invoices_tax'),'fieldset'=>'payment_form','html'=>format_number_currency($invoice['tax'])));
if($invoice['discount_code_value'] > 0) {
    $form->addField('discount','custom',array('label'=>$PMDR->getLanguage('user_invoices_discount'),'fieldset'=>'payment_form','html'=>format_number_currency((($invoice['discount_code_discount_type'] == 'percentage') ? (($invoice['discount_code_value'] / 100) * $invoice['subtotal']) : $invoice['discount_code_value']))));
}
$form->addField('invoice_total','custom',array('label'=>$PMDR->getLanguage('user_invoices_total'),'fieldset'=>'payment_form','html'=>format_number_currency($invoice['total'])));
$form->addField('invoice_balance','custom',array('label'=>$PMDR->getLanguage('user_invoices_balance'),'fieldset'=>'payment_form','html'=>format_number_currency($invoice['balance'])));

$form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('user_invoices_user_details')));
$form->addField('user_email','custom',array('label'=>$PMDR->getLanguage('user_invoices_email'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_email']));
$form->addField('user_first_name','custom',array('label'=>$PMDR->getLanguage('user_invoices_first_name'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_first_name']));
$form->addField('user_last_name','custom',array('label'=>$PMDR->getLanguage('user_invoices_last_name'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_last_name']));
$form->addField('user_address1','custom',array('label'=>$PMDR->getLanguage('user_invoices_address1'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_address1']));
$form->addField('user_address2','custom',array('label'=>$PMDR->getLanguage('user_invoices_address2'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_address2']));
$form->addField('user_city','custom',array('label'=>$PMDR->getLanguage('user_invoices_city'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_city']));
$form->addField('user_state','custom',array('label'=>$PMDR->getLanguage('user_invoices_state'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_state']));
$form->addField('user_country','custom',array('label'=>$PMDR->getLanguage('user_invoices_country'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_country']));
$form->addField('user_zip','custom',array('label'=>$PMDR->getLanguage('user_invoices_zipcode'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_zip']));
$form->addField('user_phone','custom',array('label'=>$PMDR->getLanguage('user_invoices_phone'),'fieldset'=>'user_details','html'=>$_SESSION['invoices_pay']['user']['user_phone']));

// If the discount code made the invoice 0.00 in value (a 100% discount) we just continue
if($invoice['total'] == '0.00') {
    $payment_form = $PMDR->getNew('Form');
    $payment_form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $payment_form = $payment_form->toHTML();
} else {
    $processor_data = array(
        'user_id'=>$user['id'],
        'invoice_id'=>$invoice['id'],
        'order_id'=>$invoice['order_id'],
        'order_number'=>$invoice['order_number'],
        'subtotal'=>$invoice['subtotal'],
        'tax'=>$invoice['tax']+$invoice['tax2'],
        'total'=>$invoice['total'],
        'balance'=>$invoice['balance']
    );

    $processor_data = $processor_data + $_SESSION['invoices_pay']['user'];

    if(!is_null($invoice['order_id'])) {
        $pricing = $db->GetRow("SELECT o.pricing_id, DATEDIFF(o.future_due_date,CURDATE())-1 AS days_until_due, o.amount_recurring, o.period, o.period_count FROM ".T_ORDERS." o WHERE id=?",$invoice['order_id']);
        $processor_data['pricing_id'] = $pricing['pricing_id'];
        $processor_data['days_until_due'] = $pricing['days_until_due'];
        $processor_data['amount_recurring'] = $pricing['amount_recurring'];
        $processor_data['period'] = $pricing['period'];
        $processor_data['period_count'] = $pricing['period_count'];

        unset($pricing);
    }
    $processor->loadParameters($processor_data);
    $payment_form = $processor->getPaymentForm($processor_data);
    unset($processor_data);

}
$template_content->set('form',$form);
$template_content->set('payment_form',$payment_form);
unset($payment_form);

if($form->wasSubmitted('submit')) {
    $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $PMDR->get('Invoices')->changeStatus($invoice['id'],'paid');
        redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>