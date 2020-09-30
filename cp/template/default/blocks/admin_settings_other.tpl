<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend>Login and Sessions</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('session_timeout'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('session_timeout'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_cookie_timeout'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_cookie_timeout'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('failed_login_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('failed_login_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('failed_login_lock_time'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('failed_login_lock_time'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Login Integration</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_db_host'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_db_host'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_db_name'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_db_name'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_db_user'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_db_user'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_db_password'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_db_password'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_db_prefix'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_db_prefix'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_registration_url'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_registration_url'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('login_module_password_reminder_url'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('login_module_password_reminder_url'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Database Backup</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('backup_path'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('backup_path'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('backup_cron_days'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('backup_cron_days'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('backup_cron_compress'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('backup_cron_compress'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>API Keys</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('google_apikey'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('google_apikey'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('google_server_apikey'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('google_server_apikey'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('yahoo_apikey'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('yahoo_apikey'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('bing_apikey'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('bing_apikey'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('addthis_pub_id'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('addthis_pub_id'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Other</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('allowed_html_tags'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('allowed_html_tags'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('js_click_counting'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('js_click_counting'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('out_warning'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('out_warning'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('traffic_bot_check'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('traffic_bot_check'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('use_remote_libraries'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('use_remote_libraries'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('banned_words'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('banned_words'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('banned_ips'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('banned_ips'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('banned_urls'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('banned_urls'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('affiliate_program_code'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('affiliate_program_code'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('affiliate_program_cookie'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('affiliate_program_cookie'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('skype_field'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('skype_field'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('curl_proxy_url'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('curl_proxy_url'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('disable_cron'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('disable_cron'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('api_ip_addresses'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('api_ip_addresses'); ?>
        </div>
    </div>
</fieldset>
<?php if(ADDON_LINK_CHECKER) { ?>
<fieldset>
    <legend>Other</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reciprocal_field'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('reciprocal_field'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reciprocal_url'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('reciprocal_url'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reciprocal_per_hour'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('reciprocal_per_hour'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reciprocal_buffer'); ?>
        <div class="col-lg-8">
            <?php echo $form->getFieldHTML('reciprocal_buffer'); ?>
        </div>
    </div>
</fieldset>
<?php } ?>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>