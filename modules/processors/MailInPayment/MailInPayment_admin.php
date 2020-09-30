<?php
if(!IN_PMD) exit();

$form->addField('mailinpayment_instructions','textarea',array('label'=>'Mail in Instructions','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));
?>