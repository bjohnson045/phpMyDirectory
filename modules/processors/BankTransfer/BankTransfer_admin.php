<?php
if(!IN_PMD) exit();

$form->addField('banktransfer_instructions','textarea',array('label'=>'Transfer Instructions','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));
?>