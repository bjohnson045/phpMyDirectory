<?php
if(!IN_PMD) exit();

$theme_options = array(
    'red'=>'Red',
    'white'=>'White',
    'black'=>'Black',
    'purple'=>'Purple'
);

$form->addField('challenge_key','text',array('label'=>'Challenge Key','fieldset'=>'details'));
$form->addField('verification_key','text',array('label'=>'Verification Key','fieldset'=>'details'));
$form->addField('hash_key','text',array('label'=>'Authentication Hash Key','fieldset'=>'details'));
$form->addField('theme','select',array('label'=>'Theme','fieldset'=>'details','options'=>$theme_options));

$form->addValidator('challenge_key',new Validate_NonEmpty());
$form->addValidator('verification_key',new Validate_NonEmpty());
$form->addValidator('hash_key',new Validate_NonEmpty());
?>