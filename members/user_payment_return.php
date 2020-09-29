<?php
define('PMD_SECTION','members');

include ('../defaults.php');
include ('../includes/class_payment_api.php');

$PMDR->loadLanguage(array('user_invoices'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_invoices_payment_status'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'/user_invoices.php','text'=>$PMDR->getLanguage('user_invoices_pay_invoice')));
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('user_invoices_pay_invoice')));

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_payment_return.tpl');

$gateway = $db->GetRow("SELECT id FROM ".T_GATEWAYS." WHERE MD5(CONCAT(id,'".LICENSE."'))=?",array($_COOKIE[COOKIE_PREFIX.'payment_gateway']));

if(!$gateway) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
}

if(file_exists(PMDROOT.'/modules/processors/'.$gateway['id'].'/'.$gateway['id'].'_class.php')) {
    include(PMDROOT.'/modules/processors/'.$gateway['id'].'/'.$gateway['id'].'_class.php');
    $processor = new $gateway['id']($PMDR);

    // If we had to get credit card details, we need to re-load these into the class as parematers
    if($processor->on_site_payment) {
        $processor->loadParameters($_POST);
    }

    // Process the payment/response.  If we are processing a credit card this step charges the card/gets response/processes the payment in one step.
    $invoice_id = $processor->process();
}

// Push results to the template
$template_content->set('result',$processor->result);
$template_content->set('result_message',Strings::nl2br_replace($processor->result_message));
if(!valid_url($PMDR->getConfig('affiliate_program_code'))) {
    $template_content->set('affiliate_code',str_replace(array('*amount*','*invoice_id*'),array($processor->result_amount,$invoice_id),$PMDR->getConfig('affiliate_program_code')));
}
$template_content->set('invoice_id',$invoice_id);

include(PMDROOT.'/includes/template_setup.php');
?>