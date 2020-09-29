<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('approve_update'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('approve_update'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('approve_update_pending'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('approve_update_pending'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('GD_security_send_message'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('GD_security_send_message'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('send_message_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('send_message_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_email_ip_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_email_ip_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_email_ip_limit_hours'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_email_ip_limit_hours'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_attach_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_attach_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('print_window_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('print_window_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('print_window_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('print_window_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('contact_requests_messages'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('contact_requests_messages'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('contact_requests_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('contact_requests_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listings_reviews_display_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listings_reviews_display_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listings_images_display_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listings_images_display_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listings_events_display_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listings_events_display_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listings_linked'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listings_linked'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_listing_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_listing_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_listing_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_listing_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_listing_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_listing_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_listing_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_listing_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_listing_subpage_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_listing_subpage_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_listing_subpage_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_listing_subpage_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_listing_subpage_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_listing_subpage_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_listing_subpage_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_listing_subpage_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>