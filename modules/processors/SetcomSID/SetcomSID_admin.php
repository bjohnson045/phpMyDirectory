<?php
if(!IN_PMD) exit();

$form->addField('setcom_merchant','text',array('label'=>'Merchant Identifier','fieldset'=>'details'));
$form->addField('setcom_key','text',array('label'=>'Consistent Key','fieldset'=>'details'));
$form->addField('setcom_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('setcom_merchant',new Validate_NonEmpty());
$form->addValidator('setcom_key',new Validate_NonEmpty());
$form->addValidator('setcom_currency',new Validate_NonEmpty());
?>