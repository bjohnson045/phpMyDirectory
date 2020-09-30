<?php
if(!IN_PMD) exit();

$form->addField('client_key','text',array('label'=>'Client Key','fieldset'=>'details'));

$form->addValidator('client_key',new Validate_NonEmpty());
?>