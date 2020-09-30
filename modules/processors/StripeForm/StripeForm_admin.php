<?php
if(!IN_PMD) exit();
$form->addField('stripe_secret_key','text',array('label'=>'Secret Key','fieldset'=>'details'));
$form->addField('stripe_publishable_key','text',array('label'=>'Publishable Key','fieldset'=>'details'));
$form->addField('stripe_currency','text',array('label'=>'Currency','fieldset'=>'details','value'=>'USD'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('stripe_secret_key',new Validate_NonEmpty());
$form->addValidator('stripe_publishable_key',new Validate_NonEmpty());
$form->addValidator('stripe_currency',new Validate_Currency_Code());
?>