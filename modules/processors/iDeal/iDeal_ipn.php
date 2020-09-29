<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_GET['transaction_id'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/iDeal/iDeal_class.php');
    $processor = new iDeal_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>