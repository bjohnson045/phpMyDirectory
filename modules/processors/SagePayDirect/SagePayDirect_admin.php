<?php
if(!IN_PMD) exit();

$form->addField('sagepay_login','text',array('label'=>'Login Name','fieldset'=>'details'));
$form->addField('sagepay_encryption_password','text',array('label'=>'Encryption Password','fieldset'=>'details'));
$form->addField('sagepay_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('simulator','checkbox',array('label'=>'Simulator Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('sagepay_login',new Validate_NonEmpty());
$form->addValidator('sagepay_encryption_password',new Validate_NonEmpty());
?>