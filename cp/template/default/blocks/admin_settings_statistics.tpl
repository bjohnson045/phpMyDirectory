<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('statistics_disable'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('statistics_disable'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('statistics_purge_months'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('statistics_purge_months'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('statistics_click_view_phone'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('statistics_click_view_phone'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('statistics_click_view_email'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('statistics_click_view_email'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>