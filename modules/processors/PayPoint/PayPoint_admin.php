<?php
if(!IN_PMD) exit();

$form->addField('paypoint_id','text',array('label'=>'User Name/ID','fieldset'=>'details'));
$form->addField('paypoint_password','text',array('label'=>'Remote Password','fieldset'=>'details'));
$form->addField('paypoint_ssl_cb','checkbox',array('label'=>'Use SSL Callback','fieldset'=>'details'));
$form->addField('paypoint_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('paypoint_template','text',array('label'=>'Template URL','fieldset'=>'details'));
$form->addField('paypoint_require_cv2','checkbox',array('label'=>'Require CVV2 Code','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('paypoint_id',new Validate_NonEmpty());
$form->addValidator('paypoint_password',new Validate_NonEmpty());
$form->addValidator('paypoint_currency',new Validate_NonEmpty());
$form->addValidator('paypoint_currency',new Validate_Currency_Code());
?>