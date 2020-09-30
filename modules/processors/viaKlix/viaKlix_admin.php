<?php
if(!IN_PMD) exit();

$form->addField('viaklix_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('viaklix_user_id','text',array('label'=>'User ID','fieldset'=>'details'));
$form->addField('viaklix_pin','checkbox',array('label'=>'PIN','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('viaklix_merchant_id',new Validate_NonEmpty());
$form->addValidator('viaklix_user_id',new Validate_NonEmpty());
$form->addValidator('viaklix_pin',new Validate_NonEmpty());
?>