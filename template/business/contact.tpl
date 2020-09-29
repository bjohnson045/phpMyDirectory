<?php echo $form->getFormOpenHTML(); ?>
    <?php echo $form->getFieldGroup('name'); ?>
    <?php echo $form->getFieldGroup('email'); ?>
    <?php echo $form->getFieldGroup('confirm_email'); ?>
    <?php echo $form->getFieldGroup('comments'); ?>
    <?php foreach($custom_fields as $field) { ?>
        <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
    <?php } ?>
    <?php if($form->fieldExists('security_code')) { ?>
        <?php echo $form->getFieldGroup('security_code'); ?>
    <?php } ?>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>