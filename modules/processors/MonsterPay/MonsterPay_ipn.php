<?php
include('../../../defaults.php');

$PMDR->loadLanguage(array('user_invoices'));

if(isset($_POST['txnid'])) {
    include(PMDROOT.'/includes/class_payment_api.php');
    include(PMDROOT.'/modules/processors/MonsterPay/MonsterPay_class.php');
    $processor = new MonsterPay_Notification($PMDR);
    $processor->process();
}
?>