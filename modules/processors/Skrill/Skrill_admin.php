<?php
if(!IN_PMD) exit();

$form->addField('skrill_merchant_id','text',array('label'=>'Skrill Merchant ID','fieldset'=>'details'));
$form->addField('skrill_email','text',array('label'=>'Skrill Email Address','fieldset'=>'details'));
$form->addField('skrill_secret_word','text',array('label'=>'Secret Word','fieldset'=>'details'));
$form->addField('skrill_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('skrill_currency',new Validate_NonEmpty());
$form->addValidator('skrill_currency',new Validate_Currency_Code());
$form->addValidator('skrill_secret_word',new Validate_NonEmpty());
$form->addValidator('skrill_email',new Validate_NonEmpty());
$form->addValidator('skrill_email',new Validate_Email());
?>