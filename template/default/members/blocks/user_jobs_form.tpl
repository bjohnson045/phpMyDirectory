<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('categories'); ?>
            <?php echo $form->getFieldGroup('type'); ?>
            <?php echo $form->getFieldGroup('description_short'); ?>
            <?php echo $form->getFieldGroup('description'); ?>
            <?php echo $form->getFieldGroup('requirements'); ?>
            <?php echo $form->getFieldGroup('compensation'); ?>
            <?php echo $form->getFieldGroup('benefits'); ?>
            <?php echo $form->getFieldGroup('website'); ?>
            <?php echo $form->getFieldGroup('email'); ?>
            <?php echo $form->getFieldGroup('phone'); ?>
            <?php echo $form->getFieldGroup('contact_name'); ?>
            <?php foreach($fields as $field) { ?>
                <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
            <?php } ?>
        </fieldset>
        <?php echo $form->getFormActions('submit'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>