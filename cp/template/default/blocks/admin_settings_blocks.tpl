<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_login_show'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_login_show'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_categories_show'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_categories_show'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_classifieds_featured_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_classifieds_featured_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_classifieds_featured_filter'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_classifieds_featured_filter'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_classifieds_new_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_classifieds_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_listings_featured_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_listings_featured_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_listings_featured_filter'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_listings_featured_filter'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_listings_new_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_listings_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_listings_popular_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_listings_popular_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_categories_popular_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_categories_popular_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_documents_new_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_documents_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_images_new_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_images_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_reviews_new_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_reviews_new_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('block_description_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('block_description_size'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>