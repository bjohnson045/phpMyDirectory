<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['x_response_code'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/AuthorizeNetSIM/AuthorizeNetSIM_class.php');
    $processor = new AuthorizeNetSIM_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>