<?php
if(!IN_PMD) exit();

$form->addField('payflowpro_id','text',array('label'=>'Payflow ID','fieldset'=>'details'));
$form->addField('payflowpro_vendor','text',array('label'=>'Merchant Login ID (Vendor)','fieldset'=>'details'));
$form->addField('payflowpro_partner','text',array('label'=>'Partner','fieldset'=>'details','value'=>'PayPal'));
$form->addField('payflowpro_password','text',array('label'=>'Password','fieldset'=>'details'));
$form->addField('payflowpro_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('payflowpro_id',new Validate_NonEmpty());
$form->addValidator('payflowpro_vendor',new Validate_NonEmpty());
$form->addValidator('payflowpro_partner',new Validate_NonEmpty());
$form->addValidator('payflowpro_password',new Validate_NonEmpty());
$form->addValidator('payflowpro_currency',new Validate_NonEmpty());
$form->addValidator('payflowpro_currency',new Validate_Currency_Code());
?>