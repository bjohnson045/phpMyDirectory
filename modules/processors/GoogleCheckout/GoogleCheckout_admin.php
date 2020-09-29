<?php
if(!IN_PMD) exit();

$form->addField('googlecheckout_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('googlecheckout_merchant_key','text',array('label'=>'Merchant Key','fieldset'=>'details'));
$form->addField('googlecheckout_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('googlecheckout_merchant_id',new Validate_NonEmpty());
$form->addValidator('googlecheckout_merchant_key',new Validate_NonEmpty());
$form->addValidator('googlecheckout_currency',new Validate_Currency_Code());

?>