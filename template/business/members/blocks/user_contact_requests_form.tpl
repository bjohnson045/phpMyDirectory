<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $form->getFieldSetLabel('contact_request'); ?></legend>
        <?php echo $form->getFieldGroup('first_name'); ?>
        <?php echo $form->getFieldGroup('last_name'); ?>
        <?php echo $form->getFieldGroup('email'); ?>
        <?php if($form->fieldExists('confirm_email')) { ?>
            <?php echo $form->getFieldGroup('confirm_email'); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('phone'); ?>
        <?php echo $form->getFieldGroup('available'); ?>
        <?php echo $form->getFieldGroup('preferred_contact'); ?>
        <?php if($form->isFieldHidden('categories')) { ?>
            <?php echo $form->getFieldHTML('categories'); ?>
        <?php } else { ?>
            <?php echo $form->getFieldGroup('categories'); ?>
        <?php } ?>
        <?php if($form->isFieldHidden('location_id')) { ?>
            <?php echo $form->getFieldHTML('location_id'); ?>
        <?php } else { ?>
            <?php echo $form->getFieldGroup('location_id'); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('message'); ?>
        <?php foreach($fields as $field) { ?>
            <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
        <?php } ?>
        <?php if($form->fieldExists('security_code')) { ?>
            <?php echo $form->getFieldGroup('security_code'); ?>
        <?php } ?>
</fieldset>
<?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>