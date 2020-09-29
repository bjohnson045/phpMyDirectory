<?php
if(!IN_PMD) exit();

$form->addField('offlinecreditcard_instructions','textarea',array('label'=>'Payment Instructions','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));
?>