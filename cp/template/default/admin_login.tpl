<h1><?php echo $form->getFieldSetLabel('login_form'); ?></h1>
<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <div class="form-group">
        <?php echo $form->getFieldLabel('admin_login'); ?>
        <div class="col-sm-10 col-lg-5">
            <?php echo $form->getFieldHTML('admin_login'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('admin_pass'); ?>
        <div class="col-sm-10 col-lg-5">
            <?php echo $form->getFieldHTML('admin_pass'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('remember'); ?>
        <div class="col-sm-10">
            <?php echo $form->getFieldHTML('remember'); ?>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-5 col-sm-10 col-lg-offset-4">
            <?php echo $form->getFieldHTML('submit_login'); ?>
            <a class="btn btn-default" href="admin_password_reset.php"><?php echo $lang['admin_login_password_reminder']; ?></a>
        </div>
    </div>
</fieldset>
<?php echo $form->getFormCloseHTML(); ?>
<script type="text/javascript">
$(document).ready(function() {
    $("admin_login").focus();
});
</script>