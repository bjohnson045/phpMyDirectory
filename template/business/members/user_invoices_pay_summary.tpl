<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $lang['user_invoices_summary']; ?></legend>
        <?php echo $form->getFieldGroup('invoice_id'); ?>
        <?php echo $form->getFieldGroup('gateway_id'); ?>
        <?php echo $form->getFieldGroup('invoice_subtotal'); ?>
        <?php echo $form->getFieldGroup('invoice_tax'); ?>
        <?php echo $form->getFieldGroup('discount'); ?>
        <?php echo $form->getFieldGroup('invoice_total'); ?>
        <?php echo $form->getFieldGroup('invoice_balance'); ?>
    </fieldset>
    <fieldset>
        <legend><?php echo $lang['user_invoices_user_details']; ?></legend>
        <?php echo $form->getFieldGroup('user_first_name'); ?>
        <?php echo $form->getFieldGroup('user_last_name'); ?>
        <?php echo $form->getFieldGroup('user_email'); ?>
        <?php echo $form->getFieldGroup('user_address1'); ?>
        <?php echo $form->getFieldGroup('user_address2'); ?>
        <?php echo $form->getFieldGroup('user_city'); ?>
        <?php echo $form->getFieldGroup('user_state'); ?>
        <?php echo $form->getFieldGroup('user_country'); ?>
        <?php echo $form->getFieldGroup('user_zip'); ?>
        <?php echo $form->getFieldGroup('user_phone'); ?>
    </fieldset>
<?php echo $form->getFormCloseHTML(); ?>
<?php echo $payment_form; ?>
