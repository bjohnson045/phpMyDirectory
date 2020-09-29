<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['ctransaction'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/ClickBank/ClickBank_class.php');
    $processor = new ClickBank_Notification($PMDR);
    $processor->process();
}
?>