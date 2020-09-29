<?php
if(!IN_PMD) exit();

$form->addField('monsterpay_merchant_identifier','text',array('label'=>'Merchant Identifier','fieldset'=>'details'));
$form->addField('monsterpay_username','text',array('label'=>'MonsterPay Username','fieldset'=>'details'));
$form->addField('monsterpay_password','text',array('label'=>'MonsterPay Password','fieldset'=>'details'));
$form->addField('monsterpay_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('monsterpay_merchant_identifier',new Validate_NonEmpty());
$form->addValidator('monsterpay_username',new Validate_NonEmpty());
$form->addValidator('monsterpay_password',new Validate_NonEmpty());
$form->addValidator('monsterpay_currency',new Validate_NonEmpty());
$form->addValidator('monsterpay_currency',new Validate_Currency_Code());
?>