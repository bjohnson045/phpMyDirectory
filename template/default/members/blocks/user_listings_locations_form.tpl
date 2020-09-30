<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('address1'); ?>
            <?php echo $form->getFieldGroup('address2'); ?>
            <?php echo $form->getFieldGroup('location_id'); ?>
            <?php if($form->fieldExists('location_text_1')) { ?>
                <?php echo $form->getFieldGroup('location_text_1'); ?>
            <?php } ?>
            <?php if($form->fieldExists('location_text_2')) { ?>
                <?php echo $form->getFieldGroup('location_text_2'); ?>
            <?php } ?>
            <?php if($form->fieldExists('location_text_3')) { ?>
                <?php echo $form->getFieldGroup('location_text_3'); ?>
            <?php } ?>
            <?php echo $form->getFieldGroup('zip'); ?>
            <?php echo $form->getFieldGroup('phone'); ?>
            <?php echo $form->getFieldGroup('url'); ?>
            <?php echo $form->getFieldGroup('email'); ?>
        </fieldset>
        <?php echo $form->getFormActions('submit'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>