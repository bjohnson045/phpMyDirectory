<h3 style="margin-top: 0px"><?php echo $this->escape($listing['title']); ?></h3>
<?php echo $form->getFormOpenHTML(); ?>
<?php echo $form->getFieldLabel('comments'); ?><?php echo $form->getFieldHTML('comments'); ?>
<?php foreach($custom_fields as $field) { ?>
    <?php echo $form->getFieldLabel('custom_'.$field['id']); ?><?php echo $form->getFieldHTML('custom_'.$field['id']); ?>
    <?php if($form->hasFieldNote('custom_'.$field['id'])) { ?>
        <p class="note"><?php echo $form->getFieldNote('custom_'.$field['id']); ?></p>
    <?php } ?>
<?php } ?>
<?php echo $form->getFieldLabel('security_code'); ?><?php echo $form->getFieldHTML('security_code'); ?>
<?php echo $form->getFieldHTML('submit'); ?>
<?php echo $form->getFormCloseHTML(); ?>