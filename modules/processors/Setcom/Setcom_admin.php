<?php
if(!IN_PMD) exit();

$form->addField('setcom_id','text',array('label'=>'Merchant Identifier','fieldset'=>'details'));
$form->addField('setcom_outlet','text',array('label'=>'Outlet','fieldset'=>'details'));
$form->addField('setcom_key','text',array('label'=>'Consistent Key','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('setcom_id',new Validate_NonEmpty());
$form->addValidator('setcom_outlet',new Validate_NonEmpty());
?>