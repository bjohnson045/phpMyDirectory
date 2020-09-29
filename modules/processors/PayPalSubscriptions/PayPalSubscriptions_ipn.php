<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['subscr_id'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/PayPalSubscriptions/PayPalSubscriptions_class.php');
    $processor = new PayPalSubscriptions($PMDR);
    $processor->processNotification();

    if($processor->settings['testmode']) {
         $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>