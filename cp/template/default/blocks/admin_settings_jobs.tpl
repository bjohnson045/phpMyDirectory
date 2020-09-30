<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_status'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_status'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_description_size'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_description_size'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_search_engine_optimization']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_title_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_title_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_meta_title_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_meta_title_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_meta_keywords_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_meta_keywords_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('jobs_meta_description_default'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('jobs_meta_description_default'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>