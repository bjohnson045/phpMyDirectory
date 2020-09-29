<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['x_subscription_id'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/AuthorizeNetARB/AuthorizeNetARB_class.php');
    $processor = new AuthorizeNetARB_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>