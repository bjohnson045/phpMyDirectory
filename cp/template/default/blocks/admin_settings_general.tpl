<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logo'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logo'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('admin_email'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('admin_email'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('template'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('template'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('mobile_template'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('mobile_template'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('language'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('language'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('language_admin'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('language_admin'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('browse_index_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('browse_index_type'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('maintenance'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('maintenance'); ?>
            <p class="help-block"><a class="btn btn-default btn-xs" href="./admin_phrases.php?action=edit&id=public_maintenance_message&section=public_maintenance"><?php echo $lang['admin_settings_edit_maintenance_message']; ?></a></p>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('head_javascript'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('head_javascript'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('gzip'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('gzip'); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
            <?php echo $form->getFieldHTML('submit'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Date and Time</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('timezone'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('timezone'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('date_format'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('date_format'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('time_format'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('time_format'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('date_format_input'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('date_format_input'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('date_format_input_seperator'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('date_format_input_seperator'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('time_format_input'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('time_format_input'); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
            <?php echo $form->getFieldHTML('submit'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Social Integration</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('follow_links'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('follow_links'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('facebook_page_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('facebook_page_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('facebook_app_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('facebook_app_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('twitter_site_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('twitter_site_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('google_page_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('google_page_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('linkedin_company_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('linkedin_company_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('youtube_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('youtube_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('pinterest_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('pinterest_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
            <?php echo $form->getFieldHTML('submit'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Search Engine Optimization</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('mod_rewrite'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('mod_rewrite'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('mod_rewrite_listings_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('mod_rewrite_listings_id'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('rewrite_characters'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('rewrite_characters'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_mod_rewrite'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_mod_rewrite'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('location_mod_rewrite'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('location_mod_rewrite'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_url_path'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_url_path'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_suffix'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_suffix'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('google_verification_code'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('google_verification_code'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('bing_verification_code'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('bing_verification_code'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_archive_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_archive_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_archive_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_archive_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_categories_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_categories_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_categories_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_categories_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('browse_categories_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('browse_categories_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('browse_categories_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('browse_categories_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('browse_locations_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('browse_locations_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('browse_locations_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('browse_locations_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('compare_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('compare_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('compare_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('compare_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('contact_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('contact_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('contact_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('contact_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('faq_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('faq_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('faq_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('faq_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('map_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('map_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_users_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_users_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_users_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_users_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('site_links_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('site_links_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('site_links_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('site_links_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('sitemap_meta_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('sitemap_meta_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('sitemap_meta_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('sitemap_meta_description'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_listing_review_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_listing_review_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_listing_review_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_listing_review_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>