<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_image_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_image_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_image_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_image_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_thumb_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_thumb_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_thumb_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_thumb_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_description_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_description_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_image_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_image_size'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Resizing</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_image_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_image_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_thumb_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_thumb_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classified_thumb_crop'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classified_thumb_crop'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('title_classified_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('title_classified_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_title_classified_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_title_classified_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_keywords_classified_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_keywords_classified_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('meta_description_classified_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('meta_description_classified_default'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Other</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('classifieds_images_formats'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('classifieds_images_formats'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>