<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['ap_status'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/Payza/Payza_class.php');
    $processor = new Payza_Notification($PMDR);
    $processor->process();

    if($processor->response['ap_test']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>