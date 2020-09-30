<?php echo $form->getFormOpenHTML(array('data-history'=>'false')); ?>
<div class="ui-field-contain"><?php echo $form->getFieldLabel('name'); ?><?php echo $form->getFieldHTML('name'); ?></div>
<div class="ui-field-contain"><?php echo $form->getFieldLabel('email'); ?><?php echo $form->getFieldHTML('email'); ?></div>
<div class="ui-field-contain"><?php echo $form->getFieldLabel('confirm_email'); ?><?php echo $form->getFieldHTML('confirm_email'); ?></div>
<div class="ui-field-contain"><?php echo $form->getFieldLabel('comments'); ?><?php echo $form->getFieldHTML('comments'); ?></div>
<?php foreach($custom_fields as $field) { ?>
    <div class="ui-field-contain">
    <?php echo $form->getFieldLabel('custom_'.$field['id']); ?><?php echo $form->getFieldHTML('custom_'.$field['id']); ?>
    <?php if($form->hasFieldNote('custom_'.$field['id'])) { ?>
        <p class="note"><?php echo $form->getFieldNote('custom_'.$field['id']); ?></p>
    <?php } ?>
    </div>
<?php } ?>
<div data-role="none"><?php echo $form->getFieldLabel('security_code'); ?>
<?php echo $form->getFieldHTML('security_code'); ?></div>
<input data-theme="a" type="submit" name="submit" value="Submit" />
<?php echo $form->getFormCloseHTML(); ?>