<?php
if(!IN_PMD) exit();

$form->addField('program_id','text',array('label'=>'Program ID','fieldset'=>'details'));
$form->addField('website_id','text',array('label'=>'Website ID','fieldset'=>'details'));
$form->addField('website_location_id','text',array('label'=>'Location ID','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('program_id',new Validate_NonEmpty());
$form->addValidator('website_id',new Validate_NonEmpty());
$form->addValidator('website_location_id',new Validate_NonEmpty());
?>