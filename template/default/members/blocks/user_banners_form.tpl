<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('banner_details'); ?></legend>
                <?php echo $form->getFieldGroup('type_id'); ?>
                <?php echo $form->getFieldGroup('title'); ?>
                <?php echo $form->getFieldGroup('image'); ?>
                <?php if($form->fieldExists('preview')) { ?>
                    <?php echo $form->getFieldGroup('preview'); ?>
                <?php } ?>
        </fieldset>
        <?php echo $form->getFormActions(); ?>
        <?php echo $form->getFieldHTML('listing_id'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>