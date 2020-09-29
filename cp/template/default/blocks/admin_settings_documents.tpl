<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('documents_allow'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('documents_allow'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('documents_desc_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('documents_desc_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('documents_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('documents_size'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>