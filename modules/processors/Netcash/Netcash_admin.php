<?php
if(!IN_PMD) exit();

$form->addField('netcash_username','text',array('label'=>'Username','fieldset'=>'details'));
$form->addField('netcash_password','text',array('label'=>'Password','fieldset'=>'details'));
$form->addField('netcash_pin','text',array('label'=>'PIN','fieldset'=>'details'));
$form->addField('netcash_terminal_number','text',array('label'=>'Terminal Number','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('netcash_username',new Validate_NonEmpty());
$form->addValidator('netcash_password',new Validate_NonEmpty());
$form->addValidator('netcash_pin',new Validate_NonEmpty());
$form->addValidator('netcash_terminal_number',new Validate_NonEmpty());
?>