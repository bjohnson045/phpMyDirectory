<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST)) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/GoogleCheckout/GoogleCheckout_class.php');
    $processor = new GoogleCheckout($PMDR);
    $success = $processor->processNotification($_POST);
    
    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }

    if($success) {
        header('HTTP/1.0 200 OK');
    } else {
        header('WWW-Authenticate: Basic realm="Google Checkout API"');
        header('HTTP/1.0 401 Unauthorized');
    }
    exit();
}
?>