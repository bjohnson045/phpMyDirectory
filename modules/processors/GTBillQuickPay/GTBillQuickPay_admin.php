<?php
if(!IN_PMD) exit();

$form->addField('gtbillquickpay_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('gtbillquickpay_site_id','text',array('label'=>'Site ID','fieldset'=>'details'));
$form->addField('gtbillquickpay_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('gtbillquickpay_currency',new Validate_NonEmpty());
$form->addValidator('gtbillquickpay_currency',new Validate_Currency_Code());
$form->addValidator('gtbillquickpay_merchant_id',new Validate_NonEmpty());
$form->addValidator('gtbillquickpay_site_id',new Validate_NonEmpty());
?>