<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('blog_details'); ?></legend>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('friendly_url'); ?>
            <?php if($form->fieldExists('date_publish')) { ?>
                <?php echo $form->getFieldGroup('date_publish'); ?>
            <?php } ?>
            <?php echo $form->getFieldGroup('user_display'); ?>
            <?php echo $form->getFieldGroup('image'); ?>
            <?php if($form->fieldExists('image_current')) { ?>
                <?php echo $form->getFieldGroup('image_current'); ?>
                <?php echo $form->getFieldGroup('image_delete'); ?>
            <?php } ?>
            <?php echo $form->getFieldGroup('categories'); ?>
            <?php echo $form->getFieldGroup('content_short'); ?>
            <?php echo $form->getFieldGroup('content'); ?>
            <?php echo $form->getFieldGroup('keywords'); ?>
            <?php echo $form->getFieldGroup('meta_title'); ?>
            <?php echo $form->getFieldGroup('meta_keywords'); ?>
            <?php echo $form->getFieldGroup('meta_description'); ?>
            <?php foreach($fields as $field) { ?>
                <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
            <?php } ?>
        </fieldset>
        <?php echo $form->getFormActions('submit'); ?>
    <?php echo $form->getFormCloseHTML(); ?>
</div>