<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2>Email</h2>
<?php echo $form->getFormOpenHTML(); ?>
    <?php echo $form->getFieldGroup('from_name'); ?>
    <?php echo $form->getFieldGroup('from_email'); ?>
    <?php echo $form->getFieldGroup('email'); ?>
    <?php echo $form->getFieldGroup('message'); ?>
    <?php foreach($custom_fields as $field) { ?>
        <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
    <?php } ?>
    <?php echo $form->getFieldGroup('security_code'); ?>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>