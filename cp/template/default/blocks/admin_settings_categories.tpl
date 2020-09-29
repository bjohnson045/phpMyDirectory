<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_num_columns'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_num_columns'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_vertical_sort'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_vertical_sort'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('cat_empty_hidden'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('cat_empty_hidden'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_indexes'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_indexes'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_subs_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_subs_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_category_title'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_category_title'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('show_category_description'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('show_category_description'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_other']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_setup'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_setup'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_browse_children'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_browse_children'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('html_editor_categories'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('html_editor_categories'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('category_select_type'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('category_select_type'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_category_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_category_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_category_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_category_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_category_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_category_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_category_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_category_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_category_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_category_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_category_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_category_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_category_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_category_location_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_category_location_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_category_location_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>