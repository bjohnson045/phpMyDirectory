<?php if($result == 'success') { ?>
    <h2><?php echo $lang['user_invoices_payment_successful']; ?></h2>
    <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices.php"><?php echo $lang['user_invoices_print_view']; ?></a>
    <?php echo $affiliate_code; ?>
<?php } elseif($result == 'pending') { ?>
    <h2><?php echo $lang['user_invoices_payment_pending']; ?></h2>
    <?php echo $result_message; ?>
<?php } else { ?>
    <h2><?php echo $lang['user_invoices_payment_failed']; ?></h2>
    <a href="<?php echo BASE_URL.MEMBERS_FOLDER; ?>user_invoices_pay.php?id=<?php echo $invoice_id; ?>"><?php echo $lang['user_invoices_try_again']; ?><a>
<?php } ?>