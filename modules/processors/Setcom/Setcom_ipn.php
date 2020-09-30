<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['Outcome'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/Setcom/Setcom_class.php');
    $processor = new Setcom_Notification($PMDR);
    $processor->process();
}
?>