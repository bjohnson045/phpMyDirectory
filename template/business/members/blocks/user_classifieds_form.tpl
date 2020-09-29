<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('classified_details'); ?></legend>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('description'); ?>
            <?php echo $form->getFieldGroup('price'); ?>
            <?php echo $form->getFieldGroup('expire_date'); ?>
            <?php echo $form->getFieldGroup('www'); ?>
            <?php echo $form->getFieldGroup('buttoncode'); ?>
            <?php if($form->fieldExists('classified_image1')) { ?>
                <?php for($x = 1; $x <= 5; $x++) { ?>
                    <?php echo $form->getFieldGroup('classified_image'.$x); ?>
                <?php } ?>
            <?php } ?>
            <?php if($form->fieldExists('delete_images')) { ?>
                <?php echo $form->getFieldGroup('delete_images'); ?>
            <?php } ?>
            <?php foreach($fields as $field) { ?>
                <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
            <?php } ?>
        </fieldset>
        <?php echo $form->getFormActions('submit'); ?>
        <?php echo $form->getFieldHTML('listing_id'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>