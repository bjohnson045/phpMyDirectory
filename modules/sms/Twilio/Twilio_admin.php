<?php
if(!IN_PMD) exit();

$form->addField('twilio_sid','text',array('label'=>'Account SID','fieldset'=>'details'));
$form->addField('twilio_token','text',array('label'=>'Auth Token','fieldset'=>'details'));
$form->addField('twilio_number','text',array('label'=>'Number or Caller ID','fieldset'=>'details'));

$form->addValidator('twilio_sid',new Validate_NonEmpty());
$form->addValidator('twilio_token',new Validate_NonEmpty());
$form->addValidator('twilio_number',new Validate_NonEmpty());
?>