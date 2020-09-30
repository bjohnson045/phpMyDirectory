<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_thumb_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_thumb_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_thumb_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_thumb_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_size'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Resizing</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_thumb_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_thumb_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('image_logo_thumb_crop'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('image_logo_thumb_crop'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Logo Backgrounds</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logo_background_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logo_background_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logo_background_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logo_background_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logo_background_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logo_background_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logo_background_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logo_background_small'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Other</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('logos_formats'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('logos_formats'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('pdf_logo'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('pdf_logo'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>