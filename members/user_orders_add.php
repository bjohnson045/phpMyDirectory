<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_orders'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_orders_add'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_add.php','text'=>$PMDR->getLanguage('user_orders_add')));

if(isset($_GET['pricing_id'])) {
    $product = $db->GetRow("SELECT p.*, pp.user_limit FROM ".T_PRODUCTS." p INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=? AND pp.active=1",array($_GET['pricing_id']));
    if($product['type'] == 'listing_membership') {
        redirect_url(rebuild_url(array(),array(),false,BASE_URL.MEMBERS_FOLDER.'user_orders_add_listing.php'));
    } else {
        // redirect directly to payment
    }
}

if($product_groups = $PMDR->get('Products')->getProductsArray(null,false,false)) {
    if(is_array($product_groups) AND count($product_groups) == 1) {
        if(is_array($product_groups[0]['products']) AND count($product_groups[0]['products']) == 1) {
            if(is_array($product_groups[0]['products'][0]['pricing']) AND count($product_groups[0]['products'][0]['pricing']) == 1) {
                redirect_url(rebuild_url(array('pricing_id'=>$product_groups[0]['products'][0]['pricing'][0]['id']),array(),false,BASE_URL.MEMBERS_FOLDER.'user_orders_add_listing.php'));
            }
        }
    }

    foreach($product_groups as $group_key=>$group) {
        foreach($group['products'] as $product_key=>$product) {
            foreach($product['pricing'] as $key=>$price) {
                $form_pricing[$price['id']] = 'Pricing Level '.$price['id'];
                $product_groups[$group_key]['products'][$product_key]['pricing'][$key]['url'] = rebuild_url(array('pricing_id'=>$price['id']));
                $product_groups[$group_key]['products'][$product_key]['pricing'][$key]['period'] = $PMDR->getLanguage('user_orders_'.$price['period']);
            }
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_orders_add.tpl');
$template_content->set('form',$form);
$template_content->set('product_groups',$product_groups);

include(PMDROOT.'/includes/template_setup.php');
?>