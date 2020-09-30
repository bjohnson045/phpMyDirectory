<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend>Display</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_display_all'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_display_all'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('count_search'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('count_search'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_short_desc_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_short_desc_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('alpha_index_search'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('alpha_index_search'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('count_directory'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('count_directory'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Options</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_restriction_time'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_restriction_time'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_require_values'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_require_values'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('spell_checker'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('spell_checker'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_category_children'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_category_children'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_location_children'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_location_children'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_allow_partial_zip'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_allow_partial_zip'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_partial_zip_format'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_partial_zip_format'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_category_matches'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_category_matches'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_short_word_max'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_short_word_max'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_short_word_min'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_short_word_min'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_boolean_mode'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_boolean_mode'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_match_all'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_match_all'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_boolean_partial_match'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_boolean_partial_match'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_title_weight'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_title_weight'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_filter_stop_words'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_filter_stop_words'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_distance_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_distance_type'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_total_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_total_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_search_order_1'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_search_order_1'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_search_order_2'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_search_order_2'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_browse_order_1'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_browse_order_1'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_browse_order_2'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_browse_order_2'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('listing_search_radius_autosort'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('listing_search_radius_autosort'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_exclude_words'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_exclude_words'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Ad Code</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_ad_code'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_ad_code'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('search_ad_code_frequency'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('search_ad_code_frequency'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>