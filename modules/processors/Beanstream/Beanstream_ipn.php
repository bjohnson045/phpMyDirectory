<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['trnApproved'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/Beanstream/Beanstream_class.php');
    $processor = new Beanstream_Notification($PMDR);
    $processor->process();

    if($processor->settings['testmode']) {
        $processor->sendDebugEmail($PMDR->getConfig('admin_email'));
    }
}
?>