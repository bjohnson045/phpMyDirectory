<?php
if(!IN_PMD) exit();
$form->addField('2checkout_account_id','text',array('label'=>'Account ID','fieldset'=>'details'));
$form->addField('2checkout_private_key','text',array('label'=>'Private Key','fieldset'=>'details'));
$form->addField('2checkout_public_key','text',array('label'=>'Public Key','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('2checkout_account_id',new Validate_NonEmpty());
$form->addValidator('2checkout_private_key',new Validate_NonEmpty());
$form->addValidator('2checkout_public_key',new Validate_NonEmpty());
?>