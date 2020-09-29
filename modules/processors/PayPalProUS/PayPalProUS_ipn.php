<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['payment_status'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/PayPalProUS/PayPalProUS_class.php');
    $processor = new PayPalProUS_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
         $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>