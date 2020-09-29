<script type="text/javascript">
$(document).ready(function() {
    $("#test_connection").click(function(e) {
        e.preventDefault();
        type = $("#email_preferred_connection").val();
        if(type == 'smtp' && $("#email_smtp_host").val() == '') {
            alert('Please enter the SMTP connection details and save the settings before testing.');
        } else if(type == 'sendmail' && $("#email_sendmail_path").val() == '') {
            alert('Please enter the sendmail path and save the settings before testing.');
        } else {
            window.location.href = "admin_maintenance_email_test.php?connection="+$("#email_preferred_connection").val();
        }
    });

});
</script>
<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend>General</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_preferred_connection'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_preferred_connection'); ?>
            <p class="help-block"><a id="test_connection" class="btn btn-default btn-xs"><?php echo $lang['admin_settings_email_test_connection']; ?></a></p>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_log_expiration_days'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_log_expiration_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_queue_rate'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_queue_rate'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>SMTP</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_host'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_host'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_user'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_user'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_pass'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_pass'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_port'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_port'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_require_auth'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_require_auth'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_smtp_encryption'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_smtp_encryption'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Sendmail</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_sendmail_path'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_sendmail_path'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Default Settings</legend>
    <div class="form-group">
        <label class="col-sm-5 col-lg-4 control-label">Default Signature:</label>
        <div class="form-control-static col-lg-8">
            <a href="<?php echo BASE_URL_ADMIN; ?>/admin_phrases.php?action=edit&id=signature&section=email_templates">Edit signature language phrase</a>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_from_name'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_from_name'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_from_address'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_from_address'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_recipients'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_recipients'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>