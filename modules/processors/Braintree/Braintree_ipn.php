<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

include(PMDROOT.'/includes/class_payment_api.php');
include(PMDROOT.'/modules/processors/Braintree/Braintree_class.php');

$processor = new Braintree($PMDR);
$processor->processNotification();

if($processor->settings['testmode']) {
     $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
}
    
header("HTTP/1.1 200 OK");
?>