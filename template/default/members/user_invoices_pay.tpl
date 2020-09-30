<?php if($no_gateways) { ?>
    <?php echo $no_gateways; ?>
<?php } else { ?>
    <?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $lang['user_invoices_user_details']; ?></legend>
        <?php echo $form->getFieldGroup('user_first_name'); ?>
        <?php echo $form->getFieldGroup('user_last_name'); ?>
        <?php echo $form->getFieldGroup('user_organization'); ?>
        <?php echo $form->getFieldGroup('user_email'); ?>
        <?php echo $form->getFieldGroup('user_address1'); ?>
        <?php echo $form->getFieldGroup('user_address2'); ?>
        <?php echo $form->getFieldGroup('user_city'); ?>
        <?php echo $form->getFieldGroup('user_state'); ?>
        <?php echo $form->getFieldGroup('user_country'); ?>
        <?php echo $form->getFieldGroup('user_zip'); ?>
        <?php echo $form->getFieldGroup('user_phone'); ?>
    </fieldset>
    <fieldset>
        <legend><?php echo $lang['user_invoices_payment_method']; ?></legend>
        <?php echo $form->getFieldGroup('gateway_id'); ?>
    </fieldset>
    <?php if($form->fieldExists('discount_code')) { ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('discount_codes'); ?></legend>
            <?php echo $form->getFieldGroup('discount_code'); ?>
        </fieldset>
    <?php } ?>
    <?php echo $form->getFormActions(); ?>
    <?php echo $form->getFormCloseHTML(); ?>
<?php } ?>