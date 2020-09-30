<?php
if(!IN_PMD) exit();

$form->addField('eway_customer_id','text',array('label'=>'Customer ID','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('eway_customer_id',new Validate_NonEmpty());
?>