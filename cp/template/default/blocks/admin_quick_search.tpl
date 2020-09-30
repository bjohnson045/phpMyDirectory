<?php if($results) { ?>
    <?php if($listings) { ?>
        <strong><?php echo $lang['listings']; ?></strong><br />
        <?php foreach($listings AS $listing) { ?>
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_listings.php?action=edit&id=<?php echo $listing['id']; ?>&user_id=<?php echo $listing['user_id']; ?>"><?php echo $listing['title']; ?></a> (ID: <?php echo $listing['id']; ?>)<br />
        <?php } ?>
    <?php } ?>
    <?php if($users) { ?>
        <?php if($listings) { ?><br /><?php } ?>
        <strong>Users</strong><br />
        <?php foreach($users AS $user) { ?>
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?action=edit&id=<?php echo $user['id']; ?>"><?php echo $user['name']; ?></a> (ID: <?php echo $user['id']; ?>)<br />
        <?php } ?>
    <?php } ?>
    <?php if($classifieds) { ?>
        <?php if($listings OR $users) { ?><br /><?php } ?>
        <strong>Classifieds</strong><br />
        <?php foreach($classifieds AS $classified) { ?>
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_classifieds.php?action=edit&id=<?php echo $classified['id']; ?>&user_id=<?php echo $classified['user_id']; ?>"><?php echo $classified['title']; ?></a> (ID: <?php echo $classified['id']; ?>)<br />
        <?php } ?>
    <?php } ?>
    <?php if($invoice) { ?>
        <?php if($listings OR $users OR $classifieds) { ?><br /><?php } ?>
        <strong>Invoices</strong><br />
        <a href="<?php echo BASE_URL_ADMIN; ?>/admin_invoices.php?action=edit&id=<?php echo $invoice['id']; ?>&user_id=<?php echo $invoice['user_id']; ?>">Invoice #<?php echo $invoice['id']; ?></a> -
        <a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?action=edit&id=<?php echo $invoice['user_id']; ?>"><?php echo $invoice['user_name']; ?></a><br />
    <?php } ?>
    <?php if($order) { ?>
        <?php if($invoice) { ?><br /><?php } ?>
        <strong>Orders</strong><br />
        <a href="<?php echo BASE_URL_ADMIN; ?>/admin_orders.php?action=edit&id=<?php echo $order['id']; ?>&user_id=<?php echo $order['user_id']; ?>">Order ID: <?php echo $order['id']; ?> (#<?php echo $order['order_id']; ?>)</a> -
        <a href="<?php echo BASE_URL_ADMIN; ?>/admin_users.php?action=edit&id=<?php echo $order['user_id']; ?>"><?php echo $order['user_name']; ?></a>
    <?php } ?>
    <?php if($transactions) { ?>
        <?php if($listings OR $users OR $invoice) { ?><br /><?php } ?>
        <strong>Transactions</strong><br />
        <?php foreach($transactions AS $transaction) { ?>
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_transactions.php?action=edit&id=<?php echo $transaction['id']; ?>"><?php echo $transaction['transaction_id']; ?></a><br />
        <?php } ?>
    <?php } ?>
    <?php if($settings) { ?>
        <?php if($listings OR $users OR $invoice OR $transactions) { ?><br /><?php } ?>
        <strong>Settings</strong><br />
        <?php foreach($settings AS $setting) { ?>
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_settings.php?group=<?php echo $setting['grouptitle']; ?>&varname=<?php echo $setting['varname']; ?>"><?php echo $setting['content']; ?></a><br />
        <?php } ?>
    <?php } ?>
<?php } else { ?>
    No Results
<?php } ?>