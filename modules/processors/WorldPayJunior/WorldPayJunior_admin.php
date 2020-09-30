<?php
if(!IN_PMD) exit();

$form->addField('worldpay_id','text',array('label'=>'WorldPay ID#','fieldset'=>'details'));
$form->addField('worldpay_pw','text',array('label'=>'WorldPay Security Password','fieldset'=>'details'));
$form->addField('worldpay_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('worldpay_variables','textarea',array('label'=>'Custom Variables','fieldset'=>'details'));
$form->addFieldNote('worldpay_variables','Enter one option per line in the format: name|value');
$form->addField('worldpay_futurepay','checkbox',array('label'=>'Recurring Payments (FuturePay)','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('worldpay_id',new Validate_NonEmpty());
$form->addValidator('worldpay_pw',new Validate_NonEmpty());
$form->addValidator('worldpay_currency',new Validate_NonEmpty());
$form->addValidator('worldpay_currency',new Validate_Currency_Code());
?>