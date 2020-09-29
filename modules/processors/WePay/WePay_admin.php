<?php
if(!IN_PMD) exit();

$form->addField('wepay_appid','text',array('label'=>'WePay Client ID','fieldset'=>'details'));
$form->addField('wepay_secret','text',array('label'=>'WePay Client Secret','fieldset'=>'details'));
$form->addField('wepay_token','text',array('label'=>'WePay Access Token','fieldset'=>'details'));
$form->addField('wepay_account_id','text',array('label'=>'WePay Account ID','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('wepay_appid',new Validate_NonEmpty());
$form->addValidator('wepay_secret',new Validate_NonEmpty());
$form->addValidator('wepay_token',new Validate_NonEmpty());
$form->addValidator('wepay_account_id',new Validate_NonEmpty());
?>