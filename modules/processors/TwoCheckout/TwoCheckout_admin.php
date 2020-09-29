<?php
if(!IN_PMD) exit();

$form->addField('2co_id','text',array('label'=>'Vendor Number','fieldset'=>'details'));
$form->addField('2co_word','text',array('label'=>'Secret Phrase/Word','fieldset'=>'details'));
$form->addField('testmode','checkbox',array('label'=>'Use Test Mode','fieldset'=>'details'));

$form->addFieldSet('details_recurring',array('legend'=>'Recurring Billing Configuration (Only used if 2checkout recurring billing is used)'));
$product_groups = $PMDR->get('Products')->getProductsArray(null,true);
foreach($product_groups as $product_group) {
    foreach($product_group['products'] AS $product) {
        foreach($product['pricing'] AS $pricing) {
            $pricing_ids[] = $pricing['id'];
            $form->addField('2co_product_id_'.$pricing['id'],'text',array('label'=>$product['name'].' 2Checkout Product ID','fieldset'=>'details_recurring'));
            $form->addFieldNote('2co_product_id_'.$pricing['id'],$pricing['period_count'].' '.$PMDR->getLanguage($pricing['period']).' - '.$pricing['price'].'/'.$pricing['setup_price'].' '.$PMDR->getLanguage('setup'));
            $form->addValidator('2co_product_id_'.$pricing['id'], new Validate_Numeric());
        }
    }
}
$form->addField('submit','submit',array('label'=>'Submit','fieldset'=>'submit'));

$form->addValidator('2co_id',new Validate_NonEmpty());
$form->addValidator('2co_word',new Validate_NonEmpty());
?>