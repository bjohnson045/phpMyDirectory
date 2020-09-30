<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_events_new_number'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('block_events_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_events_upcoming_number'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('block_events_upcoming_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_status'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_status'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_start_days'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_start_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_rsvp_reminder_days'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_rsvp_reminder_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_description_size'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_description_size'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Image</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_image_width'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_image_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_image_height'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_image_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_thumb_width'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_thumb_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_thumb_height'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_thumb_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_image_size'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_image_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_image_small'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_image_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_thumb_small'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_thumb_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_thumb_crop'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_thumb_crop'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('event_images_formats'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('event_images_formats'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_event_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('title_event_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_event_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_event_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_event_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_event_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_event_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_event_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>