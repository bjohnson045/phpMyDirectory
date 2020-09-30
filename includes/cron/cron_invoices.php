<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

// Invoice Creation
function cron_invoices($j) {
    global $PMDR, $db;

    if($PMDR->getConfig('disable_billing')) {
        return array('status'=>true,'data'=>array('invoices_created'=>array()));
    }

    // Because the next invoice date will be NULL if its a free product we don't need to check its price for invoicing
    $orders = $db->GetAll("
        SELECT
            o.id,
            o.user_id,
            o.type,
            o.type_id,
            o.pricing_id,
            o.next_due_date,
            o.next_invoice_date,
            o.gateway_id,
            o.period,
            o.period_count,
            o.amount_recurring,
            o.discount_code,
            o.discount_code_value,
            o.discount_code_type,
            o.discount_code_discount_type
        FROM
            ".T_ORDERS." o
        WHERE
            o.status IN ('active','pending','suspended') AND
            DATE_SUB(o.next_invoice_date,INTERVAL ".$PMDR->getConfig('invoice_generation_days')." DAY) <= '".date('Y-m-d')."'
            AND next_invoice_date IS NOT NULL"
    );
    $cron_invoices = array();
    foreach($orders AS $order) {
        $product = $PMDR->get('Products')->getByPricingID($order['pricing_id'],$order['user_id']);
        $next_invoice_date = $PMDR->get('Dates')->dateAdd($order['next_invoice_date'],$order['period_count'],$order['period']);
        $invoice = array (
            'order_id'=>$order['id'],
            'user_id'=>$order['user_id'],
            'type'=>$order['type'],
            'type_id'=>$order['type_id'],
            'date_due'=>$order['next_invoice_date'],
            'tax_rate'=>(float) $product['tax_rate'],
            'tax_rate2'=>(float) $product['tax_rate2'],
            'gateway_id'=>$order['gateway_id'],
            'notes'=>'Automatically generated invoice',
            'next_due_date'=>$next_invoice_date,
            'product_name'=>$product['product_name'],
            'product_group_name'=>$product['product_group_name']
        );
        if(!empty($order['affiliate_program_tracking_code'])) {
            $invoice['affiliate_program_tracking_code'] = $order['affiliate_program_tracking_code'];
        }
        if(!empty($order['discount_code_type']) AND $order['discount_code_type'] != 'onetime') {
            $invoice['discount_code'] = (string) $order['discount_code'];
            $invoice['discount_code_value'] = (float) $order['discount_code_value'];
            $invoice['discount_code_type'] = (string) $order['discount_code_type'];
            $invoice['discount_code_discount_type'] = (string) $order['discount_code_discount_type'];
        }
        if($PMDR->getConfig('tax_type') == 'exclusive') {
            $invoice['tax'] = (float) $order['amount_recurring']*($product['tax_rate']/100);
            if($PMDR->getConfig('compound_tax')) {
                $invoice['tax2'] = (float) ($order['amount_recurring']+$invoice['tax'])*($product['tax_rate2']/100);
            } else {
                $invoice['tax2'] = (float) $order['amount_recurring']*($product['tax_rate2']/100);
            }
            $invoice['subtotal'] = $order['amount_recurring'];
        } else {
            $invoice['tax'] = (float) round($order['amount_recurring'] - ($order['amount_recurring']*100/($product['tax_rate']+100)),2);
            $invoice['tax2'] = (float) round($order['amount_recurring'] - ($order['amount_recurring']*100/($product['tax_rate2']+100)),2);
            $invoice['subtotal'] = $order['amount_recurring'] - $invoice['tax'] - $invoice['tax2'];
        }
        $invoice['total'] = $invoice['subtotal'] + $invoice['tax'] + $invoice['tax2'];

        $invoice_id = $PMDR->get('Invoices')->insert($invoice);

        // For continuous invoice generation, update the next invoice date
        $db->Execute("UPDATE ".T_ORDERS." SET next_invoice_date=? WHERE id=?",array($next_invoice_date,$order['id']));

        // An option can be added for non-continuous integration and if turned on next_invoice_date can be set to NULL until all
        // invoices are paid at which time we will set the next_invoice_date back to a date in the future so this CRON job will pick it up

        $invoice = array_merge($invoice,$PMDR->get('Invoices')->recalculatePrice($invoice_id));

        $PMDR->get('Invoices')->sendInvoiceCreatedEmail($invoice_id);

        $cron_invoices[] = $invoice_id;
        unset($invoice);
    }
    $cron_data['data']['invoices_created'] = $cron_invoices;

    $cron_data['status'] = true;

    unset($invoice);
    unset($orders);
    unset($order);

    return $cron_data;
}
$cron['cron_invoices'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>5);
?>