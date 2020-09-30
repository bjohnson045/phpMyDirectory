<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['status'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/Skrill/Skrill_class.php');
    $processor = new Skrill_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
         $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>