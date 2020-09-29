<?php
if(!IN_PMD) exit();

$form->addField('psigatexml_store_id','text',array('label'=>'Store ID','fieldset'=>'details'));
$form->addField('psigatexml_passphrase','text',array('label'=>'Pass Phrase','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('psigatexml_store_id',new Validate_NonEmpty());
$form->addValidator('psigatexml_passphrase',new Validate_NonEmpty());
?>