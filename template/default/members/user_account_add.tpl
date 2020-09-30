<script type="text/javascript">
$(document).ready(function() {
    $("#user_country").change(function() {
        if($(this).val() == 'United States') {
            $("#user_state_select-control-group").show();
            $("#user_state-control-group").hide();
        } else {
            $("#user_state_select-control-group").hide();
            $("#user_state-control-group").show();
        }
    });
    $("#user_state_select").change(function() {
        $("#user_state").val($(this).val());
    });
    $("#user_country").trigger("change");
    $("#user_state_select").val($("#user_state").val());
});
</script>
<?php if($log_in_url) { ?>
    <p><?php echo $lang['user_account_already']; ?> <a href="<?php echo $log_in_url; ?>"><?php echo $lang['user_account_login']; ?></a></p>
<?php } ?>
<?php if($remote_login) { ?>
    <p><a id="remote_login_link" href="#"><?php echo $lang['user_account_remote_login']; ?></a></p>
<?php } ?>
<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('user_details'); ?></legend>
        <?php echo $form->getFieldGroup('login'); ?>
        <?php echo $form->getFieldGroup('user_email'); ?>
        <?php echo $form->getFieldGroup('user_email2'); ?>
        <?php if($form->fieldExists('pass')) { ?>
            <?php echo $form->getFieldGroup('pass'); ?>
            <?php echo $form->getFieldGroup('pass2'); ?>
        <?php } ?>
        <?php if($form->fieldExists('display_name')) { ?>
            <?php echo $form->getFieldGroup('display_name'); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('user_first_name'); ?>
        <?php echo $form->getFieldGroup('user_last_name'); ?>
        <?php echo $form->getFieldGroup('user_organization'); ?>
        <?php echo $form->getFieldGroup('profile_image'); ?>
        <?php echo $form->getFieldGroup('timezone'); ?>
        <?php echo $form->getFieldGroup('user_address1'); ?>
        <?php echo $form->getFieldGroup('user_address2'); ?>
        <?php echo $form->getFieldGroup('user_city'); ?>
        <?php echo $form->getFieldGroup('user_state'); ?>
        <?php echo $form->getFieldGroup('user_state_select'); ?>
        <?php echo $form->getFieldGroup('user_country'); ?>
        <?php echo $form->getFieldGroup('user_zip'); ?>
        <?php echo $form->getFieldGroup('user_phone'); ?>
        <?php echo $form->getFieldGroup('user_fax'); ?>
        <?php foreach($custom_fields as $field) { ?>
        <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('email_lists'); ?>
        <?php if($PMDR->getConfig('reg_terms_checkbox')) { ?>
            <div class="form-group">
                <?php echo $form->getFieldLabel('terms_accepted'); ?>
                <div class="col-lg-4">
                    <?php echo $form->getFieldHTML('terms_text'); ?>
                    <?php echo $form->getFieldHTML('terms_accepted'); ?>
                </div>
            </div>
        <?php } ?>
        <?php echo $form->getFieldGroup('security_code'); ?>
        <?php echo $form->getFieldGroup('ip_address'); ?>
    </fieldset>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>