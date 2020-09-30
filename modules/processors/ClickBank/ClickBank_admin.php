<?php
if(!IN_PMD) exit();

$form->addField('clickbank_nickname','text',array('label'=>'Account Nickname','fieldset'=>'details'));
$form->addField('clickbank_secret_key','text',array('label'=>'Secret Key','fieldset'=>'details'));
$product_groups = $PMDR->get('Products')->getProductsArray(null,true);
foreach($product_groups as $product_group) {
    foreach($product_group['products'] AS $product) {
        foreach($product['pricing'] AS $pricing) {
            $pricing_ids[] = $pricing['id'];
            $form->addField('clickbank_product_id_'.$pricing['id'],'text',array('label'=>$product['name'].' ClickBank Product ID','fieldset'=>'details_recurring'));
            $form->addFieldNote('clickbank_product_id_'.$pricing['id'],$pricing['period_count'].' '.$PMDR->getLanguage($pricing['period']).' - '.$pricing['price'].'/'.$pricing['setup_price'].' '.$PMDR->getLanguage('setup'));
        }
    }
}
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));
?>