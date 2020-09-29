<div class="row">
    <div class="col-md-10">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a class="btn btn-default btn-xs pull-right" title="<?php echo $lang['admin_edit']; ?>" href="admin_orders.php?action=edit&user_id=<?php echo $order['user_id']; ?>&id=<?php echo $order['id']; ?>"><i class="fa fa-pencil"></i> <?php echo $lang['admin_edit']; ?></a>
                Order Details
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-borderless">
                    <tr><td><?php echo $lang['admin_orders_id']; ?>:</td><td><?php echo $order['id']; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_status']; ?>:</td><td><span class="label label-<?php echo $order['status']; ?>"><?php echo $status; ?></span></td></tr>
                    <tr><td><?php echo $lang['admin_orders_number']; ?>:</td><td><?php echo $order['order_id']; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_date']; ?>:</td><td><?php echo $date; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_ip_address']; ?>:</td><td><?php echo $order['ip_address']; ?></td></tr>
                    <?php if(isset($order['discount'])) { ?>
                        <tr><td><?php echo $lang['admin_orders_discount']; ?>:</td><td><?php echo $order['discount']; ?></td></tr>
                    <?php } ?>
                    <?php if(!empty($order['gateway_id'])) { ?>
                        <tr><td><?php echo $lang['admin_orders_payment_method']; ?>:</td><td><?php echo $order['gateway_id']; ?></td></tr>
                    <?php } ?>
                    <?php if(isset($amount_recurring)) { ?>
                        <tr><td><?php echo $lang['admin_orders_amount_recurring']; ?>:</td><td><?php echo $amount_recurring; ?></td></tr>
                    <?php } ?>
                    <tr><td><?php echo $lang['admin_orders_period']; ?>:</td><td><?php echo $period; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_period_count']; ?>:</td><td><?php echo $order['period_count']; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_next_due_date']; ?>:</td><td><?php echo $next_due_date; ?></td></tr>
                    <?php if($next_invoice_date) { ?>
                        <tr><td><?php echo $lang['admin_orders_next_invoice_date']; ?>:</td><td><?php echo $next_invoice_date; ?></td></tr>
                        <tr><td><?php echo $lang['admin_orders_next_invoice_creation']; ?>:</td><td><?php echo $next_invoice_creation; ?></td></tr>
                    <?php } ?>
                    <tr>
                        <td><?php echo $lang['admin_orders_suspend_date']; ?>:</td>
                        <td>
                            <?php if($suspend_date == 0) { ?>
                                <span class="label label-success"><?php echo $lang['admin_never']; ?></span>
                            <?php } else { ?>
                                <?php echo $suspend_date; ?>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr><td><?php echo $lang['admin_orders_taxed']; ?>:</td><td><i class="fa fa-circle <?php echo $order['taxed'] ? 'text-success' : 'text-danger'; ?>"></i></td></tr>
                    <?php if(!empty($order['subscription_id'])) { ?>
                        <tr><td><?php echo $lang['admin_orders_subscription_id']; ?>:</td><td><?php echo $order['subscription_id']; ?></td></tr>
                    <?php } ?>
                    <tr><td><?php echo $lang['admin_orders_renewable']; ?>:</td><td><i class="fa fa-circle <?php echo $order['renewable'] ? 'text-success' : 'text-danger'; ?>"></i></td></tr>
                </table>
            </div>
        </div>
        <div class="panel panel-default">
            <div class="panel-heading">
                <a class="btn btn-default btn-xs pull-right" title="<?php echo $lang['admin_edit']; ?>" href="admin_orders.php?action=edit&user_id=<?php echo $order['user_id']; ?>&id=<?php echo $order['id']; ?>"><i class="fa fa-pencil"></i> <?php echo $lang['admin_edit']; ?></a>
                <?php echo $lang['admin_orders_product']; ?>
            </div>
            <div class="panel-body">
                <table class="table table-condensed table-borderless">
                    <tr><td><?php echo $lang['admin_orders_product_type']; ?>:</td><td><?php echo $order['product_type']; ?></td></tr>
                    <tr><td><?php echo $lang['admin_orders_status']; ?>:</td><td><span class="label label-<?php echo $order['product_status']; ?>"><?php echo $product_status; ?></span></td></tr>
                    <tr><td><?php echo $lang['admin_orders_product_name']; ?>:</td><td><a href="admin_products.php?action=edit&id=<?php echo $order['product_id']; ?>"><?php echo $order['product_name']; ?></a></td></tr>
                    <tr><td><?php echo $lang['admin_orders_product_title']; ?>:</td><td> <a href="admin_listings.php?action=edit&id=<?php echo $order['type_id']; ?>&user_id=<?php echo $order['user_id']; ?>"><?php echo $order['product_title']; ?></a> <a target="_blank" class="btn btn-default btn-xs" title="View Public Listing" href="<?php echo BASE_URL; ?>/listing.php?id=<?php echo $order['type_id']; ?>"><?php echo $lang['view']; ?> <i class="fa fa-external-link"></i></a></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-10">
        <?php if(!$config['disable_billing']) { ?>
        <div class="panel panel-default">
            <div class="panel-heading">Invoices</div>
            <?php if(count($invoices)) { ?>
                <ul class="list-group">
                <?php foreach($invoices AS $invoice) { ?>
                    <li class="list-group-item"><?php echo $lang['admin_orders_invoice_number']; ?><?php echo $invoice['id']; ?> <span class="label <?php echo ($invoice['status'] == 'paid' ? 'label-success' : 'label-danger'); ?>"><?php echo $lang[$invoice['status']]; ?></span><a class="btn btn-xs btn-default pull-right" href="admin_invoices.php?action=edit&id=<?php echo $invoice['id']; ?>&user_id=<?php echo $invoice['user_id']; ?>">View</a></li>
                <?php } ?>
                </ul>
            <?php } else { ?>
                <div class="panel-body">
                    No Invoices
                </div>
            <?php } ?>
            <div class="panel-footer">
                <?php if(count($invoices)) { ?>
                    <a class="btn btn-default btn-xs" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?order_id=<?php echo $order['id']; ?>&user_id=<?php echo $order['user_id']; ?>">View All</a>
                <?php } ?>
                <a class="btn btn-default btn-xs" href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?action=add&order_id=<?php echo $order['id']; ?>&user_id=<?php echo $order['user_id']; ?>">Add Invoice</a>
            </div>
        </div>
        <?php } ?>
        <div class="panel panel-default">
            <div class="panel-heading"><?php echo $lang['admin_users_send_email']; ?></div>
            <div class="panel-body">
                <?php echo $email_form->getFormOpenHTML(); ?>
                <?php echo $email_form->getFieldHTML('email'); ?><br />
                <?php echo $email_form->getFieldHTML('submit_email'); ?>
                <?php echo $email_form->getFormCloseHTML(); ?>
            </div>
        </div>
    </div>
</div>