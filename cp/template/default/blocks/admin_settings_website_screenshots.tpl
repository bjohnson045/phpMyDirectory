<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_service'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_service'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_key'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_key'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_size_small'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_size_small'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_cache_days'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_cache_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('website_screenshot_cron_amount'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('website_screenshot_cron_amount'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>