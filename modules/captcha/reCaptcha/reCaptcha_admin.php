<?php
if(!IN_PMD) exit();

$theme_options = array(
    'light'=>'Light',
    'dark'=>'Dark',
);

$size_options = array(
    'compact'=>'Compact',
    'normal'=>'Normal',
);

$type_options = array(
    'audio'=>'Audio',
    'image'=>'Image',
);

$form->addField('public_key','text',array('label'=>'Site Key','fieldset'=>'details'));
$form->addField('private_key','text',array('label'=>'Secret Key','fieldset'=>'details'));
$form->addField('theme','select',array('label'=>'Theme','fieldset'=>'details','options'=>$theme_options));
$form->addField('size','select',array('label'=>'Size','fieldset'=>'details','options'=>$size_options));
$form->addField('type','select',array('label'=>'Type','fieldset'=>'details','options'=>$type_options));

$form->addValidator('public_key',new Validate_NonEmpty());
$form->addValidator('private_key',new Validate_NonEmpty());
?>