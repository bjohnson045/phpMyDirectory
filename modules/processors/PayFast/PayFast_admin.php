<?php
if(!IN_PMD) exit();

$form->addField('payfast_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('payfast_merchant_key','text',array('label'=>'Merchant Key','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('payfast_merchant_id',new Validate_NonEmpty());
$form->addValidator('payfast_merchant_key',new Validate_NonEmpty());
?>