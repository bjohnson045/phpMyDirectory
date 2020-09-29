<?php
define('PMD_SECTION','members');

include ('../defaults.php');

if(ADDON_DISCOUNT_CODES) {
    $PMDR->get('Discount_Codes')->setURLCode();
}

$PMDR->loadLanguage(array('user_invoices'));

$PMDR->get('Authentication')->authenticate();

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_invoices_pay_invoice'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'/user_invoices.php','text'=>$PMDR->getLanguage('user_invoices')));
$PMDR->setAddArray('breadcrumb',array('text'=>$PMDR->getLanguage('user_invoices_pay_invoice')));

// Get the user details
$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

// Retreive the order and invoice details from the database
$invoice = $db->GetRow("
SELECT
    i.id,
    i.order_id,
    o.subscription_id,
    o.discount_code AS order_discount_code,
    o.pricing_id,
    u.pricing_id_new
FROM ".T_INVOICES." i
LEFT JOIN ".T_ORDERS." o ON i.order_id = o.id
LEFT JOIN ".T_UPGRADES." u ON i.id = u.invoice_id
WHERE
    i.id=? AND
    i.user_id=? AND
    i.status='unpaid'",array($_GET['id'],$user['id']));

// If the invoice does not exist return the user to the invoices list
if(!$invoice) {
    redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
}

// If the order has a subscription ID attached to it we do not let the user pay it since it will be paid automatically
if(trim($invoice['subscription_id']) != '') {
    $PMDR->addMessage('error',$PMDR->getLanguage('user_invoices_pay_subscription_error'));
    redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices.php');
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_invoices_pay.tpl');

if(!is_null($invoice['order_id'])) {
    $allowed_gateways = $db->GetOne("SELECT gateway_ids FROM ".T_PRODUCTS_PRICING." WHERE id=?",array($invoice['pricing_id']));
    $active_gateways = $db->GetAll("SELECT * FROM ".T_GATEWAYS." WHERE enabled=1 AND hidden=0 AND id IN('".implode('\',\'',explode(',',$allowed_gateways))."') ORDER BY ordering ASC");
    unset($allowed_gateways);
} else {
    $active_gateways = $db->GetAll("SELECT * FROM ".T_GATEWAYS." WHERE enabled=1 AND hidden=0 ORDER BY ordering ASC");
}

$form = $PMDR->getNew('Form');
// Check if we have any active gateways, if not, show a message
if(!empty($active_gateways)) {
    $form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('user_invoices_user_details')));
    $form->addField('user_email','text',array('label'=>$PMDR->getLanguage('user_invoices_email'),'fieldset'=>'user_details'));
    $form->addField('user_first_name','text',array('label'=>$PMDR->getLanguage('user_invoices_first_name'),'fieldset'=>'user_details'));
    $form->addField('user_last_name','text',array('label'=>$PMDR->getLanguage('user_invoices_last_name'),'fieldset'=>'user_details'));
    $form->addField('user_organization','text',array('label'=>$PMDR->getLanguage('user_invoices_organization'),'fieldset'=>'user_details'));
    $form->addField('user_address1','text',array('label'=>$PMDR->getLanguage('user_invoices_address1'),'fieldset'=>'user_details'));
    $form->addField('user_address2','text',array('label'=>$PMDR->getLanguage('user_invoices_address2'),'fieldset'=>'user_details'));
    $form->addField('user_city','text',array('label'=>$PMDR->getLanguage('user_invoices_city'),'fieldset'=>'user_details'));
    $form->addField('user_state','text',array('label'=>$PMDR->getLanguage('user_invoices_state'),'fieldset'=>'user_details'));
    $form->addField('user_country','select',array('label'=>$PMDR->getLanguage('user_invoices_country'),'fieldset'=>'user_details','first_option'=>'','options'=>get_countries_array()));
    $form->addField('user_zip','text',array('label'=>$PMDR->getLanguage('user_invoices_zipcode'),'fieldset'=>'user_details'));
    $form->addField('user_phone','text',array('label'=>$PMDR->getLanguage('user_invoices_phone'),'fieldset'=>'user_details'));
    $form->loadValues($user);
    foreach($active_gateways as $key=>$gateway) {
        $form_values[$gateway['id']] = $gateway['display_name'];
        if($url = get_file_url(PMDROOT.'/modules/processors/'.$gateway['id'].'/logo.*')) {
            $form_values[$gateway['id']] .= '<br /><img style="padding-bottom: 10px;" src="'.$url.'">';
        }
    }
    $form->addField('gateway_id','radio',array('label'=>$PMDR->getLanguage('user_invoices_payment_method'),'fieldset'=>'gateways','value'=>$active_gateways[0]['id'],'options'=>$form_values));
    $form->addValidator('gateway_id',new Validate_NonEmpty());
    unset($active_gateways,$key,$gateway,$form_values,$url);

    // If the order does not have a discount applied to it yet, or its the first invoice for the order, or the invoice is not associated
    // with an order, we allow a discount code to be applied as long as the addon is available.
    $order_invoice_count = is_null($invoice['order_id']) ? 0 : $db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=?",array($invoice['order_id']));
    if(ADDON_DISCOUNT_CODES AND ($order_invoice_count < 2 OR empty($invoice['order_discount_code']))) {
        $form->addFieldSet('discount_codes',array('legend'=>$PMDR->getLanguage('user_invoices_discount_code')));
        $form->addField('discount_code','text',array('label'=>$PMDR->getLanguage('user_invoices_discount_code'),'fieldset'=>'discount_code'));
        if($discount_code = $PMDR->get('Discount_Codes')->getURLCode()) {
            $form->setFieldAttribute('discount_code','value',$discount_code);
            unset($discount_code);
        }
    }
    unset($order_invoice_count);

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $template_content->set('form',$form);
} else {
    $template_content->set('no_gateways',$PMDR->getLanguage('user_invoices_no_gateways',BASE_URL.'/contact.php'));
}

if($form->wasSubmitted('submit')) {
    $data = $form->loadValues();

    // If the gateway does not exist, or the invoice does not exist, or the gateway API class does not exist, we redirect backwards
    $gateway = $db->GetRow("SELECT * FROM ".T_GATEWAYS." WHERE id=? AND enabled=1 AND hidden=0",array($data['gateway_id']));
    if(!$gateway OR !file_exists(PMDROOT.'/modules/processors/'.$gateway['id'].'/'.$gateway['id'].'_class.php')) {
        $form->addError('Invalid payment gateway selected.','gateway_id');
    }

    // We apply recurring discount codes in CRON because we have to send correct amounts in the invoice emails with the discount
    // If the user typed in a new discount code
    if(!empty($data['discount_code'])) {
        // Get the number of times the discount code has been used by this user for use in the next query
        $user_used_limit = $db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE id!=? AND user_id=? AND discount_code=?",array($invoice['id'],$user['id'],$data['discount_code']));
        // Get the pricing ID in case this is an upgrade so we can check the discount code is valid for the new pricing ID
        $pricing_id = !is_null($invoice['pricing_id_new']) ? $invoice['pricing_id_new'] : $invoice['pricing_id'];
        $pricing_id_sql = '';
        if(!empty($pricing_id)) {
            $pricing_id_sql = "AND CONCAT(',',pricing_ids,',') LIKE '%,".$pricing_id.",%'";
        }
        // User active orders
        $user_pricing_ids = $db->GetCol("SELECT pricing_id FROM ".T_ORDERS." WHERE user_id=? AND status='active'",array($user['id']));
        // Validate the discount code
        if(!$discount = $db->GetRow("SELECT *, IF(date_expire > NOW(),0,1) AS discount_expired FROM ".T_DISCOUNT_CODES." WHERE code=? AND date_start <= NOW() AND (used_limit != used OR used_limit = 0) AND value > 0 AND (user_used_limit > $user_used_limit OR user_used_limit=0) AND CONCAT(',',gateway_ids,',') LIKE '%,".$data['gateway_id']."%,' $pricing_id_sql",array($data['discount_code']))) {
            $form->addError($PMDR->getLanguage('user_invoices_discount_code_error'),'discount_code');
        } elseif($discount['discount_expired']) {
            $form->addError($PMDR->getLanguage('user_invoices_discount_code_expired'),'discount_code');
        } elseif($discount['user_order_status'] == 'new' AND count($user_pricing_ids)) {
            $form->addError($PMDR->getLanguage('user_invoices_discount_code_error'),'discount_code');
        } elseif($discount['user_order_status'] == 'old' AND !count($user_pricing_ids)) {
            $form->addError($PMDR->getLanguage('user_invoices_discount_code_error'),'discount_code');
        } elseif(!empty($discount['pricing_ids_required'])) {
            $required_pricing_ids = array_filter(explode(',',$discount['pricing_ids_required']));
            if($user_pricing_ids) {
                foreach($required_pricing_ids AS $required_pricing_id) {
                    if(!in_array($required_pricing_id,$user_pricing_ids)) {
                        $form->addError($PMDR->getLanguage('user_invoices_discount_code_error').'4','discount_code');
                        break;
                    }
                }
            } else {
                $form->addError($PMDR->getLanguage('user_invoices_discount_code_error'),'discount_code');
            }
        }
    }

    if(!$form->validate()) {
        $PMDR->addMessage('error',$form->parseErrorsForTemplate());
    } else {
        if(!empty($data['discount_code'])) {
            // If the discount code is recurring, we add it to the order
            if($discount['type'] == 'recurring') {
                $PMDR->get('Orders')->update(array('discount_code'=>$discount['code'],'discount_code_value'=>$discount['value'],'discount_code_type'=>$discount['type'],'discount_code_discount_type'=>$discount['discount_type']),$invoice['order_id']);
            }
            // Update the invoice with the discount code details
            $PMDR->get('Invoices')->update(array('discount_code'=>$discount['code'],'discount_code_value'=>$discount['value'],'discount_code_type'=>$discount['type'],'discount_code_discount_type'=>$discount['discount_type']),$invoice['id']);
            // Recalculate the invoice pricing details because we have added a new discount code
            $PMDR->get('Invoices')->recalculatePrice($invoice['id']);
        }
        // Update the gateway ID for the invoice
        $db->Execute("UPDATE ".T_INVOICES." SET gateway_id=? WHERE id=?",array($data['gateway_id'],$invoice['id']));
        $db->Execute("UPDATE ".T_ORDERS." SET gateway_id=? WHERE invoice_id=?",array($data['gateway_id'],$invoice['id']));

        $PMDR->get('Session')->delete('invoices_pay');

        $_SESSION['invoices_pay']['invoice']['id'] = $invoice['id'];
        $_SESSION['invoices_pay']['user']['user_email'] = $data['user_email'];
        $_SESSION['invoices_pay']['user']['user_first_name'] = $data['user_first_name'];
        $_SESSION['invoices_pay']['user']['user_last_name'] = $data['user_last_name'];
        $_SESSION['invoices_pay']['user']['user_address1'] = $data['user_address1'];
        $_SESSION['invoices_pay']['user']['user_address2'] = $data['user_address2'];
        $_SESSION['invoices_pay']['user']['user_city'] = $data['user_city'];
        $_SESSION['invoices_pay']['user']['user_state'] = $data['user_state'];
        $_SESSION['invoices_pay']['user']['user_country'] = $data['user_country'];
        $_SESSION['invoices_pay']['user']['user_zip'] = $data['user_zip'];
        $_SESSION['invoices_pay']['user']['user_phone'] = $data['user_phone'];

        redirect(BASE_URL.MEMBERS_FOLDER.'user_invoices_pay_summary.php');
    }
}

include(PMDROOT.'/includes/template_setup.php');
?>