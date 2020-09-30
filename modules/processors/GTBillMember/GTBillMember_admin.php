<?php
if(!IN_PMD) exit();

$form->addField('gtbill_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('gtbill_site_id','text',array('label'=>'Site ID','fieldset'=>'details'));
$form->addField('gtbill_currency','text',array('label'=>'Currency','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Test Mode','fieldset'=>'details'));

$form->addFieldSet('details_recurring',array('legend'=>'Price ID Configuration'));
$product_groups = $PMDR->get('Products')->getProductsArray(null,true);
foreach($product_groups as $product_group) {
    foreach($product_group['products'] AS $product) {
        foreach($product['pricing'] AS $pricing) {
            $pricing_ids[] = $pricing['id'];
            $form->addField('gtbill_price_id_'.$pricing['id'],'text',array('label'=>$product['name'].' GTBill Price ID','fieldset'=>'details_recurring'));
            $form->addFieldNote('gtbill_price_id_'.$pricing['id'],$pricing['period_count'].' '.$PMDR->getLanguage($pricing['period']).' - '.$pricing['price'].'/'.$pricing['setup_price'].' '.$PMDR->getLanguage('setup'));
        }
    }
}

$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('gtbill_currency',new Validate_NonEmpty());
$form->addValidator('gtbill_currency',new Validate_Currency_Code());
$form->addValidator('gtbill_merchant_id',new Validate_NonEmpty());
$form->addValidator('gtbill_site_id',new Validate_NonEmpty());
?>