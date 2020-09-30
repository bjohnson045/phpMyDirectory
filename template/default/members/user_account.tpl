<?php if(isset($remote_login)) { ?>
    <p><a id="remote_login_link" href="#"><?php echo $lang['user_account_link_account']; ?></a> <?php echo $lang['user_account_link_account_suffix']; ?></p>
<?php } ?>
<?php if($user['login_providers']) { ?>
    <p><?php echo $lang['user_account_currently_linked']; ?>: <?php echo $user['login_providers']; ?></p>
<?php } ?>
<?php echo $form->getFormOpenHTML(); ?>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('user_details'); ?></legend>
        <?php echo $form->getFieldGroup('id'); ?>
        <?php echo $form->getFieldGroup('login'); ?>
        <?php echo $form->getFieldGroup('user_email'); ?>
        <?php if($form->fieldExists('display_name')) { ?>
            <?php echo $form->getFieldGroup('display_name'); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('user_first_name'); ?>
        <?php echo $form->getFieldGroup('user_last_name'); ?>
        <?php echo $form->getFieldGroup('user_organization'); ?>
        <?php echo $form->getFieldGroup('profile_image'); ?>
        <?php echo $form->getFieldGroup('current_profile_image'); ?>
        <?php echo $form->getFieldGroup('delete_profile_image'); ?>
        <?php foreach($custom_fields as $field) { ?>
            <?php echo $form->getFieldGroup('custom_'.$field['id']); ?>
        <?php } ?>
        <?php echo $form->getFieldGroup('email_lists'); ?>
        <?php echo $form->getFieldGroup('timezone'); ?>
        <?php if($form->fieldExists('terms_accepted')) { ?>
            <div class="form-group">
                <?php echo $form->getFieldLabel('terms_accepted'); ?>
                <div class="col-lg-4">
                    <?php echo $form->getFieldHTML('terms_text'); ?>
                    <?php echo $form->getFieldHTML('terms_accepted'); ?>
                </div>
            </div>
        <?php } ?>
    </fieldset>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('address'); ?></legend>
        <?php echo $form->getFieldGroup('user_address1'); ?>
        <?php echo $form->getFieldGroup('user_address2'); ?>
        <?php echo $form->getFieldGroup('user_city'); ?>
        <?php echo $form->getFieldGroup('user_state'); ?>
        <?php echo $form->getFieldGroup('user_country'); ?>
        <?php echo $form->getFieldGroup('user_zip'); ?>
        <?php echo $form->getFieldGroup('user_phone'); ?>
        <?php echo $form->getFieldGroup('user_fax'); ?>
    </fieldset>
    <fieldset>
        <legend><?php echo $form->getFieldSetLabel('notifications'); ?></legend>
        <?php echo $form->getFieldGroup('favorites_notify'); ?>
    </fieldset>
    <?php if($form->fieldExists('pass')) { ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('password_change'); ?></legend>
            <?php echo $form->getFieldGroup('pass'); ?>
            <?php echo $form->getFieldGroup('pass_new'); ?>
            <?php echo $form->getFieldGroup('pass_new_confirm'); ?>
        </fieldset>
    <?php } ?>
    <?php echo $form->getFormActions(); ?>
<?php echo $form->getFormCloseHTML(); ?>