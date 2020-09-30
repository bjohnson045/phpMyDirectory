<?php
if(!IN_PMD) exit();

$form->addField('payza_email','text',array('label'=>'Payza Email Address','fieldset'=>'details'));
$form->addField('payza_securitycode','text',array('label'=>'Security Code','fieldset'=>'details'));
$form->addField('payza_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('payza_subscriptions','checkbox',array('label'=>'Use Subscriptions','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('payza_currency',new Validate_NonEmpty());
$form->addValidator('payza_currency',new Validate_Currency_Code());
$form->addValidator('payza_email',new Validate_NonEmpty());
$form->addValidator('payza_email',new Validate_Email());
$form->addValidator('payza_securitycode',new Validate_NonEmpty());
?>