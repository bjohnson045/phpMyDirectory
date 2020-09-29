<?php
if(!IN_PMD) exit();

$form->addField('store_id','text',array('label'=>'Store ID','fieldset'=>'details'));
$form->addField('recurring','checkbox',array('label'=>'Use Recurring Billing','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('store_id',new Validate_NonEmpty());
?>