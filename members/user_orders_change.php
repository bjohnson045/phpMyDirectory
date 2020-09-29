<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_orders','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_orders_change_order'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_change.php','text'=>$PMDR->getLanguage('user_orders_change_order')));

$order = $db->GetRow("SELECT * FROM ".T_ORDERS." WHERE id=? AND user_id=?",array($_GET['id'],$PMDR->get('Session')->get('user_id')));

if($order['type'] == 'listing_membership') {
    $PMDR->set('page_header',$PMDR->get('Listing',$order['type_id'])->getUserHeader('order'));
}

if(!$order) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

if(in_array($order['status'],array('pending','suspended','canceled','fraud')) AND !($order['status'] == 'suspended' AND $order['amount_recurring'] == 0.00)) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_change_pending_error'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

if(empty($order['upgrades'])) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_change_upgrades_error'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

if(!empty($order['subscription_id'])) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_change_subscription_error'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

if($db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE status='unpaid' AND order_id=?",array($order['id']))) {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_orders_change_due_invoices_error'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
}

$form = $PMDR->getNew('Form');
$form->addFieldSet('order_change',array('legend'=>$PMDR->getLanguage('admin_products_product')));
if(count(explode(',',$order['upgrades'])) == 1) {
    $product_name = $db->GetOne("SELECT CONCAT_WS(' - ',pg.name,p.name) AS product_name FROM ".T_PRODUCTS_GROUPS." pg INNER JOIN ".T_PRODUCTS." p ON pg.id=p.group_id INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=?",$order['upgrades']);
    $form->addField('pricing_id_new','custom',array('label'=>$PMDR->getLanguage('user_orders_change_upgrade_to'),'fieldset'=>'order_change','value'=>$order['upgrades'],'html'=>$product_name));
} else {
    $form->addField('pricing_id_new','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_orders_change_upgrade_to'),'fieldset'=>'order_change','value'=>'','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','pricing_ids'=>$order['upgrades'],'hidden'=>true)));
}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

$form->addValidator('pricing_id_new',new Validate_NonEmpty());

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        $invoice_id = $PMDR->get('Orders')->changePricingID($order['id'],$data['pricing_id_new'],true);

        if(!is_null($invoice_id)) {
            $PMDR->get('Email_Templates')->send('order_status_change',array('to'=>$order['user_id'],'order_id'=>$order['id']));
            $status = 'incomplete';
        } else {
            $status = 'complete';
        }

        $PMDR->get('Email_Templates')->send('admin_order_upgrade',array('order_id'=>$order['id']));

        $db->Execute("
        INSERT INTO
            ".T_UPGRADES."
        SET
            order_id=?,
            invoice_id=?,
            date=NOW(),
            pricing_id=?,
            pricing_id_new=?,
            status=?",array($order['id'],$invoice_id,$order['pricing_id'],$data['pricing_id_new'],$status));

        if(!is_null($invoice_id)) {
            $PMDR->addMessage('success',$PMDR->getLanguage('user_orders_change_pay_invoice'));
            redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices_pay.php?id='.$invoice_id);
        } else {
            $PMDR->addMessage('success',$PMDR->getLanguage('user_orders_change_no_payment'));
            redirect(BASE_URL.MEMBERS_FOLDER.'user_orders.php');
        }
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_orders_change.tpl');
$template_content->set('form',$form);

include(PMDROOT.'/includes/template_setup.php');
?>