<?php
if(!IN_PMD) exit();

$form->addField('authsim_login','text',array('label'=>'Login Name','fieldset'=>'details'));
$form->addField('authsim_tran_key','text',array('label'=>'Transaction Key','fieldset'=>'details'));
$form->addField('authsim_hash','text',array('label'=>'MD5 Hash Value','fieldset'=>'details'));
$form->addField('authsim_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('authsim_url','text',array('label'=>'Alternate Gateway URL','fieldset'=>'details'));
$form->addField('authsim_logo_url','text',array('label'=>'Logo URL','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('authsim_currency',new Validate_Currency_Code());
$form->addValidator('authsim_login',new Validate_NonEmpty());
$form->addValidator('authsim_tran_key',new Validate_NonEmpty());
$form->addValidator('authsim_hash',new Validate_NonEmpty());
?>