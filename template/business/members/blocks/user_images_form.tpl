<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('image_details'); ?></legend>
        <?php echo $form->getFieldGroup('title'); ?>
        <?php echo $form->getFieldGroup('description'); ?>
        <?php echo $form->getFieldGroup('image'); ?>
        <?php echo $form->getFieldGroup('ordering'); ?>
        <?php foreach($fields as $field) { ?>
            <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
        <?php } ?>
        <?php if($form->fieldExists('preview')) { ?>
            <?php echo $form->getFieldGroup('preview'); ?>
        <?php } ?>
    </fieldset>
    <?php echo $form->getFormActions(); ?>
    <?php echo $form->getFieldHTML('listing_id'); ?>
<?php echo $form->getFormCloseHTML(); ?>