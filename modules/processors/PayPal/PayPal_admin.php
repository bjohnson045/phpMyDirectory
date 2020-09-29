<?php
if(!IN_PMD) exit();

$form->addField('paypal_email','text',array('label'=>'Paypal Email Address','fieldset'=>'details'));
$form->addField('paypal_pdt_token','text',array('label'=>'Payment Data Transfer Token','fieldset'=>'details'));
$form->addField('paypal_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('paypal_currency',new Validate_NonEmpty());
$form->addValidator('paypal_currency',new Validate_Currency_Code());
$form->addValidator('paypal_email',new Validate_NonEmpty());
$form->addValidator('paypal_email',new Validate_Email());
$form->addValidator('paypal_pdt_token',new Validate_NonEmpty());
?>