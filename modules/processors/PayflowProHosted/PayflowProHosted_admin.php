<?php
if(!IN_PMD) exit();

$form->addField('payflowpro_partner','text',array('label'=>'Partner','fieldset'=>'details','value'=>'PayPal'));
$form->addField('payflowpro_vendor','text',array('label'=>'Merchant Login (Vendor)','fieldset'=>'details'));
$form->addField('payflowpro_user','text',array('label'=>'User','fieldset'=>'details'));
$form->addField('payflowpro_password','text',array('label'=>'Password','fieldset'=>'details'));
$form->addField('payflowpro_authorization','checkbox',array('label'=>'Authorization Only','fieldset'=>'details'));
$form->addField('payflowpro_iframe','checkbox',array('label'=>'Use Embedded Version (iFrame)','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('payflowpro_partner',new Validate_NonEmpty());
$form->addValidator('payflowpro_vendor',new Validate_NonEmpty());
$form->addValidator('payflowpro_password',new Validate_NonEmpty());
?>