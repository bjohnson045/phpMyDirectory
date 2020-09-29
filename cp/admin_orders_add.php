<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_orders','general_locations','email_templates','admin_users'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_orders_edit');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders_add.tpl');

if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('orders'));
}

$form = $PMDR->getNew('Form');
$form->addFieldSet('products',array('legend'=>$PMDR->getLanguage('admin_orders_order')));

$pricing_id_options = array('type'=>'products_tree','hidden'=>true,'expandall'=>'true');
if(isset($_GET['type'])) {
    $pricing_id_options['product_type'] = $_GET['type'];
}
if(isset($_GET['user_id'])) {
    $form->addField('user_id','hidden',array('value'=>$_GET['user_id']));
} else {
    if($PMDR->getConfig('user_select') == 'select_window') {
        $form->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_orders_user_id'),'fieldset'=>'products','icon'=>'users_search','options'=>'select_user'));
    } else {
        $form->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_orders_user_id'),'fieldset'=>'products','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
    }
}
$form->addFieldNote('user_id','<a class="btn btn-default btn-xs" href="admin_users.php?action=add&addorder=true">'.$PMDR->getLanguage('admin_orders_add_user').'</a>');

$pricing = $db->GetCol("SELECT id FROM ".T_PRODUCTS_PRICING);

$javascript .= '
$.ajax({
    data: ({
        action: "pricing_free",
        id: node.data.key
    }),
    success: function(data) {
        if(data == 1) {
            $("#create_invoice-control-group").show();
        } else {
            $("#create_invoice-control-group").hide();
        }
    }
});';

if(!$pricing) {
    $PMDR->addMessage('error','You must add at least one product and pricing before adding an order.');
    redirect_url(BASE_URL_ADMIN.'/admin_products_pricing.php?action=add');
} elseif(count($pricing) > 1) {
    $form->addField('pricing_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_orders_product'),'fieldset'=>'products','value'=>'','options'=>$pricing_id_options,'onselect'=>$javascript));
} else {
    $form->addField('pricing_id','custom',array('label'=>$PMDR->getLanguage('admin_orders_product'),'fieldset'=>'products','value'=>$pricing[0],'html'=>$PMDR->get('Products')->getPricingLabel($pricing[0])));
}
$form->addField('create_invoice','checkbox',array('label'=>$PMDR->getLanguage('admin_orders_create_invoice'),'fieldset'=>'products','value'=>'1','wrapper_attributes'=>array('style'=>'display:none')));
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

$form->addValidator('user_id',new Validate_NonEmpty());
$form->addValidator('pricing_id',new Validate_NonEmpty());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();
    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $product = $db->GetRow("SELECT p.* FROM ".T_PRODUCTS." p INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=?",array($_POST['pricing_id']));
        if($product['type'] == 'listing_membership') {
            redirect(BASE_URL_ADMIN.'/admin_orders_add_listing.php',array('user_id'=>$data['user_id'],'pricing_id'=>trim($data['pricing_id'],','),'create_invoice'=>$data['create_invoice']));
        } else {
            // redirect directly to payment
        }
    }
}
$template_content->set('title',$PMDR->getLanguage('admin_orders_add'));
$template_content->set('content',$form->toHTML());
if(!isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_orders_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>