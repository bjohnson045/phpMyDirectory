<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('product_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('product_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_select'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_select'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('GD_security_reg'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('GD_security_reg'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('email_confirm'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('email_confirm'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('captcha_logged_in'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('captcha_logged_in'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('profile_image_width'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('profile_image_width'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('profile_image_height'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('profile_image_height'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('profile_image_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('profile_image_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('profile_image_enlarge'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('profile_image_enlarge'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('profile_image_types'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('profile_image_types'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('gravatar'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('gravatar'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_search'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_search'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_display_name_format'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_display_name_format'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_display_name'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_display_name'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend><?php echo $lang['admin_settings_registration']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_registration'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_registration'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_groups_user_default'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_groups_user_default'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reg_terms_checkbox'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('reg_terms_checkbox'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_login'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_login'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_pass'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_pass'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_first_name'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_first_name'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_last_name'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_last_name'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_organization'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_organization'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_profile_image'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_profile_image'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_timezone'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_timezone'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_address1'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_address1'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_address2'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_address2'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_city'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_city'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_state'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_state'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_country'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_country'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_zip'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_zip'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_phone'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_phone'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('user_add_user_fax'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('user_add_user_fax'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>