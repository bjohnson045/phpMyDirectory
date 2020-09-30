<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_listing_claim']; ?></h2>
<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
    <?php echo $form->getFieldGroup('comments'); ?>
    <?php foreach($custom_fields as $field) { ?>
        <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
    <?php } ?>
    <?php if($form->fieldExists('security_code')) { ?>
        <?php echo $form->getFieldGroup('security_code'); ?>
    <?php } ?>
    <?php echo $form->getFormActions(); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>