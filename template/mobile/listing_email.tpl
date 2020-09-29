<h3 style="margin-top: 0px"><?php echo $this->escape($listing['title']); ?></h3>
<?php echo $form->getFormOpenHTML(array('data-history'=>'false')); ?>
<?php echo $form->getFieldLabel('from_name'); ?><?php echo $form->getFieldHTML('from_name'); ?>
<?php echo $form->getFieldLabel('from_email'); ?><?php echo $form->getFieldHTML('from_email'); ?>
<?php echo $form->getFieldLabel('message'); ?><?php echo $form->getFieldHTML('message'); ?>
<p class="note counter"></p>
<?php foreach($custom_fields as $field) { ?>
    <?php echo $form->getFieldLabel('custom_'.$field['id']); ?><?php echo $form->getFieldHTML('custom_'.$field['id']); ?>
    <?php if($form->hasFieldNote('custom_'.$field['id'])) { ?>
        <p class="note"><?php echo $form->getFieldNote('custom_'.$field['id']); ?></p>
    <?php } ?>
<?php } ?>
<?php if($form->fieldExists('attachment')) { ?>
    <?php echo $form->getFieldLabel('attachment'); ?><?php echo $form->getFieldHTML('attachment'); ?>
    <p class="note"><?php echo $lang['public_listing_email_attachment_limit']; ?>: <?php echo $email_attach_size; ?><?php echo $lang['public_listing_email_kilobytes']; ?></p>
<?php } ?>
<?php if($form->fieldExists('security_code')) { ?>
    <?php echo $form->getFieldLabel('security_code'); ?><?php echo $form->getFieldHTML('security_code'); ?>
<?php } ?>
<?php echo $form->getFieldHTML('submit'); ?>
<?php echo $form->getFormCloseHTML(); ?>
