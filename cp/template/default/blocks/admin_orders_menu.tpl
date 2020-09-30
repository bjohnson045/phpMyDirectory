<a class="list-group-item" href="admin_orders.php"><div class="icon icon_page"></div><?php echo $lang['admin_orders_all']; ?></a>
<a class="list-group-item" id="order_search_link" href="admin_orders.php?action=search"><div class="icon icon_search"></div><?php echo $lang['admin_orders_search']; ?></a>
<a class="list-group-item" href="admin_orders.php?status=active"><div class="icon icon_page_active"></div><?php echo $lang['admin_orders_active_orders']; ?></a>
<a class="list-group-item" href="admin_orders.php?status=pending"><div class="icon icon_page_pending"></div><?php echo $lang['admin_orders_pending_orders']; ?></a>
<a class="list-group-item" href="admin_orders.php?status=suspended"><div class="icon icon_page_suspended"></div><?php echo $lang['admin_orders_suspended_orders']; ?></a>
<a class="list-group-item" href="admin_orders.php?status=canceled"><div class="icon icon_page_canceled"></div><?php echo $lang['admin_orders_canceled_orders']; ?></a>
<a class="list-group-item" href="admin_orders.php?status=fraud"><div class="icon icon_page_fraud"></div><?php echo $lang['admin_orders_fraud_orders']; ?></a>
<a class="list-group-item" href="admin_orders_add.php"><div class="icon icon_page_add"></div><?php echo $lang['admin_orders_add']; ?></a>
<a class="list-group-item" href="admin_cancellations.php"><i class="fa fa-lg fa-user-times text-danger"></i> <?php echo $lang['admin_general_menu_cancellations']; ?></a>