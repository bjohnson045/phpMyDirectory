<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $form->getFieldSetLabel('credit_card'); ?></legend>
    <?php echo $form->getFieldGroup('cc_type'); ?>
    <?php echo $form->getFieldGroup('cc_number'); ?>
    <?php echo $form->getFieldGroup('cc_expire_month'); ?>
    <?php echo $form->getFieldGroup('cc_expire_year'); ?>
    <?php echo $form->getFieldGroup('cc_cvv2'); ?>
</fieldset>
<?php echo $form->getFormActions(); ?>
<?php echo $form->getFieldSetHTML('hidden'); ?>
<?php echo $form->getFormCloseHTML(); ?>