<div class="row">
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><?php echo $lang['user_orders_details']; ?></h3>
            </div>
            <table class="table table-bordered">
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_id']; ?>:</td><td><?php echo $order['order_id']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_product']; ?>:</td>
                    <td>
                        <?php echo $this->escape($order['product_group_name']); ?> - <?php echo $this->escape($order['product_name']); ?>
                        <?php if($order['upgrades_link'] == true) { ?>
                            <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_orders_change.php?id=<?php echo $order['id'] ?>" class="btn btn-default btn-xs btn-info pull-right"><?php echo $lang['user_orders_change']; ?></a>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_date']; ?>:</td><td><?php echo $order['date']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_next_due_date']; ?>:</td>
                    <td>
                        <?php echo $order['next_due_date']; ?>
                        <?php if($order['renew']) { ?>
                            <a class="btn btn-default btn-success btn-xs pull-right" href="user_orders.php?action=renew&id=<?php echo $order['id']; ?>"><?php echo $lang['user_orders_renew']; ?></a>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_status']; ?>:</td><td><?php echo $order['status']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_amount']; ?>:</td><td><?php echo $order['amount']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_product_status']; ?>:</td><td><?php echo $order['product_status']; ?></td>
                </tr>
                <tr>
                    <td class="text-right"><?php echo $lang['user_orders_subscription_id']; ?>:</td><td><?php echo $order['subscription_id']; ?></td>
                </tr>
            </table>
        </div>
    </div>
    <?php if($invoices) { ?>
    <div class="col-lg-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices.php?order_id=<?php echo $order['id']; ?>" class="btn btn-default btn-xs pull-right"><?php echo $lang['view_all']; ?></a>
                <h3 class="panel-title"><?php echo $lang['user_orders_recent_invoices']; ?></h3>
            </div>
            <ul class="list-group">
                <?php foreach($invoices AS $invoice) { ?>
                    <a class="list-group-item" href="user_invoices_pay.php?id=<?php echo $invoice['id']; ?>"><?php echo $this->getLanguage('user_orders_invoice',array($invoice['id'],$invoice['balance'],$invoice['date_due'])); ?></a>
                <?php } ?>
            </ul>
        </div>
    </div>
    <?php } ?>
</div>
<a href="user_cancellations.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger"><?php echo $lang['user_orders_cancel']; ?></a>



