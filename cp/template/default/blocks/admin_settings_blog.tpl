<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend><?php echo $lang['admin_settings_general']; ?></legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_comments'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_comments'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_comments_require_login'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_comments_require_login'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_comments_captcha'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_comments_captcha'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_posts_per_page'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_posts_per_page'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_block_number'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_block_number'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_block_characters'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_block_characters'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>User Blog Posts</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_posts'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_posts'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_delete_posts'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_delete_posts'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_posts_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_posts_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_posts_days_limit'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_posts_days_limit'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_posts_auto_approve'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_posts_auto_approve'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('blog_user_posts_publish_date'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('blog_user_posts_publish_date'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>