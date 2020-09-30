<div class="form-container">
    <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <?php echo $form->getFieldGroup('title'); ?>
            <?php echo $form->getFieldGroup('friendly_url'); ?>
            <?php echo $form->getFieldGroup('categories'); ?>
            <?php echo $form->getFieldGroup('image'); ?>
            <?php if($form->fieldExists('preview')) { ?>
                <?php echo $form->getFieldGroup('preview'); ?>
                <?php echo $form->getFieldGroup('delete_image'); ?>
            <?php } ?>
            <?php echo $form->getFieldGroup('date_start'); ?>
            <?php echo $form->getFieldGroup('date_end'); ?>
            <?php echo $form->getFieldGroup('recurring'); ?>
            <?php echo $form->getFieldGroup('recurring_end'); ?>
            <?php echo $form->getFieldGroup('recurring_type'); ?>
            <?php echo $form->getFieldGroup('recurring_interval'); ?>
            <?php echo $form->getFieldGroup('recurring_days'); ?>
            <?php echo $form->getFieldGroup('recurring_monthly'); ?>
            <?php echo $form->getFieldGroup('color'); ?>
            <?php echo $form->getFieldGroup('allow_rsvp'); ?>
            <?php echo $form->getFieldGroup('website'); ?>
            <?php echo $form->getFieldGroup('email'); ?>
            <?php echo $form->getFieldGroup('phone'); ?>
            <?php echo $form->getFieldGroup('contact_name'); ?>
            <?php echo $form->getFieldGroup('admission'); ?>
            <?php echo $form->getFieldGroup('description_short'); ?>
            <?php echo $form->getFieldGroup('description'); ?>
            <?php echo $form->getFieldGroup('venue'); ?>
            <?php echo $form->getFieldGroup('location'); ?>
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