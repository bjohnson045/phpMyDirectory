<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['transStatus'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/WorldPayJunior/WorldPayJunior_class.php');
    $processor = new WorldPayJunior($PMDR);
    $processor->processNotification();

    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>