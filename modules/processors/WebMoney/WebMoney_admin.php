<?php
if(!IN_PMD) exit();

$form->addField('webmoney_purse','text',array('label'=>'Purse','fieldset'=>'details'));
$form->addField('webmoney_secret_key','text',array('label'=>'Secret Key','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('webmoney_purse',new Validate_NonEmpty());
$form->addValidator('webmoney_secret_key',new Validate_NonEmpty());
?>