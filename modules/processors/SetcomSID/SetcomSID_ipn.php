<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['SID_RECEIPTNO'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/SetcomSID/SetcomSID_class.php');
    $processor = new SetcomSID_Notification($PMDR);
    $processor->process();
}
?>