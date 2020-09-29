<?php echo $form->getFormOpenHTML(); ?>
<fieldset>
    <legend>Reviews</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reviews_status'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('reviews_status'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reviews_require_login'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('reviews_require_login'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reviews_captcha'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('reviews_captcha'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('review_size'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('review_size'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('reviews_comments_status'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('reviews_comments_status'); ?>
        </div>
    </div>
</fieldset>
<fieldset>
    <legend>Ratings</legend>
    <div class="form-group">
        <?php echo $form->getFieldLabel('ratings_require_login'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('ratings_require_login'); ?>
        </div>
    </div>
    <div class="form-group">
        <?php echo $form->getFieldLabel('ratings_require_review'); ?>
        <div class="col-sm-18 col-md-12 col-lg-8">
            <?php echo $form->getFieldHTML('ratings_require_review'); ?>
        </div>
    </div>
</fieldset>
<div class="form-group">
    <div class="form-actions col-sm-offset-5 col-lg-offset-4 col-sm-18 col-lg-8">
        <?php echo $form->getFieldHTML('submit'); ?>
    </div>
</div>
<?php echo $form->getFormCloseHTML(); ?>