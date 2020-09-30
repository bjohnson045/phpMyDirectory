<?php
if(!IN_PMD) exit();

$form->addField('authaim_login','text',array('label'=>'Login Name','fieldset'=>'details'));
$form->addField('authaim_tran_key','text',array('label'=>'Transaction Key','fieldset'=>'details'));
$form->addField('authaim_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('authaim_url','text',array('label'=>'Alternate Gateway URL','fieldset'=>'details'));
$form->addField('authaim_require_cvv','checkbox',array('label'=>'Require CVV2 Code','fieldset'=>'details'));
$form->addField('authaim_hash','text',array('label'=>'Hash (secret word)','fieldset'=>'details'));
$form->addField('credit_card_types','checkbox',array('label'=>'Credit Card Types','fieldset'=>'details','options'=>array('Visa'=>'Visa','MasterCard'=>'MasterCard','Amex'=>'Amex','Discover'=>'Discover')));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('authaim_login',new Validate_NonEmpty());
$form->addValidator('authaim_tran_key',new Validate_NonEmpty());
$form->addValidator('authaim_currency',new Validate_NonEmpty());
$form->addValidator('authaim_currency',new Validate_Currency_Code());
$form->addValidator('credit_card_types',new Validate_NonEmpty());
?>