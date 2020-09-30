<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_status_changes($j) {
    global $PMDR, $db;

    if($PMDR->getConfig('disable_billing')) {
        return array(
            'status'=>true,
            'data'=>array(
                'orders_suspended'=>array(),
                'orders_canceled'=>array(),
                'orders_deleted'=>array(),
                'orders_changed'=>array()
            )
        );
    }

    $cron_orders_suspended = array();
    $cron_orders_canceled = array();
    $cron_orders_deleted = array();
    $cron_orders_changed = array();

    // Get all orders that should be suspended on X days past due date with status active and override suspension turned off
    $orders = $db->GetAll("SELECT o.id, o.type, o.type_id, o.user_id, o.pricing_id, o.status FROM ".T_ORDERS." o WHERE o.suspend_overdue_days != 0 AND
    DATE_ADD(next_due_date,INTERVAL o.suspend_overdue_days DAY) <= '".date('Y-m-d')."' AND o.status = 'active'");
    // We do not need to check for the renewable setting here because its based on next_due_date which still gets set for non renewable orders
    foreach($orders AS $order) {
        $product_pricing = $db->GetRow("SELECT p.name, pp.id, pp.overdue_action, pp.overdue_pricing_id FROM ".T_PRODUCTS_PRICING." pp INNER JOIN ".T_PRODUCTS." p ON pp.product_id=p.id WHERE pp.id=?",array($order['pricing_id']));
        switch($product_pricing['overdue_action']) {
            case 'product_change':
                $PMDR->get('Orders')->changePricingID($order['id'],$product_pricing['overdue_pricing_id']);
                $email_parameters = array(
                    'order_change_type'=>'Pricing',
                    'order_old_value'=>$product_pricing['name'],
                    'order_new_value'=>$db->GetOne("SELECT name FROM ".T_PRODUCTS." p INNER JOIN ".T_PRODUCTS_PRICING." pp ON p.id=pp.product_id WHERE pp.id=?",array($product_pricing['overdue_pricing_id']))
                );
                $PMDR->get('Email_Templates')->queue('admin_order_changed',array('order_id'=>$order['id'],'variables'=>$email_parameters));
                $cron_orders_changed[] = $order['id'];
                break;
            case 'delete':
                $email_parameters = array(
                    'order_change_type'=>'Deleted'
                );
                $PMDR->get('Email_Templates')->queue('admin_order_changed',array('order_id'=>$order['id'],'variables'=>$email_parameters));
                $PMDR->get('Orders')->delete($order['id']);
                $cron_orders_deleted[] = $order['id'];
                break;
            case 'nothing':
                break;
            case 'suspend':
            case 'cancel':
            default:
                $email_parameters = array(
                    'order_change_type'=>'Status',
                    'order_old_value'=>$PMDR->getLanguage($order['status'])
                );
                if($product_pricing['overdue_action'] == 'cancel') {
                    $status = 'canceled';
                    $cron_orders_canceled[] = $order['id'];
                } else {
                    $status = 'suspended';
                    $cron_orders_suspended[] = $order['id'];
                }
                $email_parameters['order_new_value'] = $PMDR->getLanguage($status);
                $PMDR->get('Orders')->changeStatus($order['id'],$status);
                $PMDR->get('Email_Templates')->queue('order_status_change',array('to'=>$order['user_id'],'order_id'=>$order['id']));
                $PMDR->get('Email_Templates')->queue('admin_order_changed',array('order_id'=>$order['id'],'variables'=>$email_parameters));
                break;
        }
    }

    $cron_data['data']['orders_suspended'] = $cron_orders_suspended;
    $cron_data['data']['orders_canceled'] = $cron_orders_canceled;
    $cron_data['data']['orders_deleted'] = $cron_orders_deleted;
    $cron_data['data']['orders_changed'] = $cron_orders_changed;
    $cron_data['status'] = true;

    unset($orders);
    unset($cron_suspended);

    return $cron_data;
}
$cron['cron_status_changes'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>11);
?>