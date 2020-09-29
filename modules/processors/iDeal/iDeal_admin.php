<?php
if(!IN_PMD) exit();

$form->addField('ideal_partner_id','text',array('label'=>'Partner ID','fieldset'=>'details'));
$form->addField('ideal_profile_key','text',array('label'=>'Profile Key','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('ideal_partner_id',new Validate_NonEmpty());
?>