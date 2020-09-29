<?php
if(!IN_PMD) exit();

$form->addField('skipjack_serial','text',array('label'=>'Serial Number','fieldset'=>'details'));
$form->addField('skipjack_developer_serial','text',array('label'=>'Developer Serial Number','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('skipjack_serial',new Validate_NonEmpty());
?>