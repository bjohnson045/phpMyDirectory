<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('document_details'); ?></legend>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('description'); ?>
            <?php echo $form->getFieldGroup('document'); ?>
            <?php if($fields) { ?>
                <?php foreach($fields as $field) { ?>
                    <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
                <?php } ?>
            <?php } ?>
        </fieldset>
        <?php echo $form->getFormActions(); ?>
        <?php echo $form->getFieldHTML('listing_id'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>