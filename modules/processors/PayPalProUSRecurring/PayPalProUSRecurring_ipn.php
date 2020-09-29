<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['recurring_payment_id'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/PayPalProUSRecurring/PayPalProUSRecurring_class.php');
    $processor = new PayPalProUSRecurring_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
         $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>