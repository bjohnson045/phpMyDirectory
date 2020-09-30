<?php
if(!IN_PMD) exit();

$form->addField('type','select',array('label'=>'Content','options'=>array('text'=>'Text Only','textnumbers'=>'Text and Numbers'),'fieldset'=>'details'));

$form->addValidator('type',new Validate_NonEmpty());
?>