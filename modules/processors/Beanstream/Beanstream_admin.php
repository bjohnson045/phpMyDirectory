<?php
if(!IN_PMD) exit();

$form->addField('beanstream_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('beanstream_username','text',array('label'=>'Username','fieldset'=>'details'));
$form->addField('beanstream_password','text',array('label'=>'Password','fieldset'=>'details'));
$form->addField('beanstream_hosted','checkbox',array('label'=>'Use BeanStream Hosted Form','fieldset'=>'details')); 
//$form->addField('beanstream_recurring','checkbox',array('label'=>'Use Recurring Billing','fieldset'=>'details'));
$form->addField('beanstream_cvd','checkbox',array('label'=>'Require CVD Code','fieldset'=>'details'));
$form->addField('beanstream_hashvalue','text',array('label'=>'Hash Value','fieldset'=>'details'));
$form->addField('beanstream_hashencryption','select',array('label'=>'Hash Encryption Type','fieldset'=>'details','first_value'=>'','options'=>array('md5'=>'MD5','sha1'=>'SHA1')));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('beanstream_merchant_id',new Validate_NonEmpty());
$form->addValidator('beanstream_username',new Validate_NonEmpty());
$form->addValidator('beanstream_password',new Validate_NonEmpty());
?>