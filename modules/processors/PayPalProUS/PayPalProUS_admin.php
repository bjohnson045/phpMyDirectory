<?php
if(!IN_PMD) exit();

$form->addField('paypalpro_api_username','text',array('label'=>'API Username','fieldset'=>'details'));
$form->addField('paypalpro_api_password','text',array('label'=>'API Password','fieldset'=>'details'));
$form->addField('paypalpro_api_signiture','text',array('label'=>'API Signature','fieldset'=>'details'));
$form->addField('paypalpro_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('paypalpro_currency',new Validate_NonEmpty());
$form->addValidator('paypalpro_currency',new Validate_Currency_Code());
$form->addValidator('paypalpro_api_username',new Validate_NonEmpty());
$form->addValidator('paypalpro_api_password',new Validate_NonEmpty());
$form->addValidator('paypalpro_api_signiture',new Validate_NonEmpty());
?>