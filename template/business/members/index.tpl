<p><?php echo $lang['user_index_not_a_members']; ?> <a href="<?php echo $this->escape($create_account_url); ?>"><?php echo $lang['user_index_create_account']; ?></a></p>
<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('login_form'); ?></legend>
        <div class="form-group">
            <?php echo $form->getFieldLabel('user_login'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('user_login'); ?>
            </div>
        </div>
        <div class="form-group">
            <?php echo $form->getFieldLabel('user_pass'); ?>
            <div class="col-lg-10">
                <?php echo $form->getFieldHTML('user_pass'); ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?php echo $form->getFieldHTML('remember'); ?>
            </div>
        </div>
        <?php if($remote_login) { ?>
            <div class="form-group">
                <div class="col-lg-offset-2 col-lg-10">
                    <a id="remote_login_link" href="#"><?php echo $lang['user_index_remote_login']; ?></a>
                </div>
            </div>
        <?php } ?>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?php echo $form->getFieldHTML('submit_login'); ?><a style="padding-left: 75px;" href="<?php echo $password_reminder_url; ?>"><?php echo $lang['user_index_password_reminder']; ?></a>
            </div>
        </div>
    </fieldset>
<?php echo $form->getFormCloseHTML(); ?>
<div id="social_login_container"></div>
<script type="text/javascript">
<!-- document.login.login.focus(); //-->
</script>