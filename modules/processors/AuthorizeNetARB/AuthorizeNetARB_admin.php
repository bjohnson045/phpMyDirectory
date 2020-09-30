<?php
if(!IN_PMD) exit();

$form->addField('autharb_login','text',array('label'=>'Login ID','fieldset'=>'details'));
$form->addField('autharb_tran_key','text',array('label'=>'Transaction Key','fieldset'=>'details'));
$form->addField('autharb_hash','text',array('label'=>'Hash (secret word)','fieldset'=>'details'));
$form->addField('credit_card_types','checkbox',array('label'=>'Credit Card Types','fieldset'=>'details','options'=>array('Visa'=>'Visa','MasterCard'=>'MasterCard','Amex'=>'Amex','Discover'=>'Discover')));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('autharb_login',new Validate_NonEmpty());
$form->addValidator('autharb_tran_key',new Validate_NonEmpty());
$form->addValidator('autharb_hash',new Validate_NonEmpty());
?>