<?php
if(!IN_PMD) exit();

$form->addField('braintree_merchant_id','text',array('label'=>'Merchant ID','fieldset'=>'details'));
$form->addField('braintree_public_key','text',array('label'=>'Public Key','fieldset'=>'details'));
$form->addField('braintree_private_key','text',array('label'=>'Private Key','fieldset'=>'details'));

$form->addFieldSet('details_recurring',array('legend'=>'Recurring Billing Configuration (Only used if Braintree recurring billing is used)'));
$product_groups = $PMDR->get('Products')->getProductsArray(null,true);
foreach($product_groups as $product_group) {
    foreach($product_group['products'] AS $product) {
        foreach($product['pricing'] AS $pricing) {
            $pricing_ids[] = $pricing['id'];
            $form->addField('braintree_plan_id_'.$pricing['id'],'text',array('label'=>$product['name'].' Braintree Plan ID','fieldset'=>'details_recurring'));
            $form->addFieldNote('braintree_plan_id_'.$pricing['id'],$pricing['period_count'].' '.$PMDR->getLanguage($pricing['period']).' - '.$pricing['price'].'/'.$pricing['setup_price'].' '.$PMDR->getLanguage('setup'));
        }
    }
}
$form->addField('testmode','checkbox',array('label'=>'Use Sandbox Test Mode','fieldset'=>'details'));
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('braintree_merchant_id',new Validate_NonEmpty());
$form->addValidator('braintree_public_key',new Validate_NonEmpty());
$form->addValidator('braintree_private_key',new Validate_NonEmpty());
?>