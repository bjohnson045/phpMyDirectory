<script type="text/javascript">
$(document).ready(function() {
    $("#follow").click(function(e) {
        e.preventDefault();
        follow = $(this);
        $.ajax({
            data: ({
                action: 'blog_follow',
                id: <?php echo $id; ?>,
                follow: follow.data("follow")
            }),
            success: function() {
                if(follow.data("follow") == 0) {
                    follow.text("<?php echo $lang['public_blog_unfollow_post']; ?>");
                    follow.data("follow",1);
                } else {
                    follow.text("<?php echo $lang['public_blog_follow_post']; ?>");
                    follow.data("follow",0);
                }
            }
        });
    });
});
</script>
<div class="row">
    <div class="col-lg-8 col-md-6 col-sm-6 col-xs-7">
        <p class="text-muted"><small><?php echo $lang['public_blog_by']; ?> <?php echo $user; ?> <?php echo $lang['public_blog_on']; ?> <?php echo $date_publish; ?><?php if($categories) { ?> <?php echo $lang['public_blog_in']; ?> <?php echo $categories; ?><?php } ?></small></p>
    </div>
    <div class="col-lg-4 col-md-6 col-sm-6 col-xs-5">
        <a href="#" id="follow" class="pull-right btn btn-default btn-sm" data-follow="<?php echo $followed; ?>"><?php if($followed) { ?><?php echo $lang['public_blog_unfollow_post']; ?><?php } else { ?><?php echo $lang['public_blog_follow_post']; ?><?php } ?></a>
    </div>
</div>
<?php if($image_url) { ?>
    <p><img id="blog_post_img" src="<?php echo $image_url; ?>" alt="<?php echo $title; ?>" /></p>
<?php } ?>
<?php echo $share; ?>
<?php echo $this->escape_html($content); ?>
<?php if($config['blog_comments']) { ?>
    <?php if($comments_count) { ?>
        <a name="comments"></a>
        <h3><?php echo $comments_count; ?> <?php echo $lang['public_blog_comments']; ?></h3>
        <?php echo $comments; ?>
    <?php } ?>
    <?php if(!$login_url) { ?>
    <div id="blog_comment_form">
        <?php echo $form->getFormOpenHTML(); ?>
        <fieldset>
            <legend><?php echo $form->getFieldSetLabel('input_default'); ?></legend>
            <?php if($form->fieldExists('name')) { ?>
                <div class="form-group">
                    <?php echo $form->getFieldLabel('name'); ?>
                    <div class="col-lg-10">
                        <?php echo $form->getFieldHTML('name'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->getFieldLabel('email'); ?>
                    <div class="col-lg-10">
                        <?php echo $form->getFieldHTML('email'); ?>
                    </div>
                </div>
                <div class="form-group">
                    <?php echo $form->getFieldLabel('website'); ?>
                    <div class="col-lg-10">
                        <?php echo $form->getFieldHTML('website'); ?>
                    </div>
                </div>
            <?php } ?>
            <div class="form-group">
                <?php echo $form->getFieldLabel('comment'); ?>
                <div class="col-lg-10">
                    <?php echo $form->getFieldHTML('comment'); ?>
                </div>
            </div>
            <?php if($form->fieldExists('follow')) { ?>
                <div class="form-group">
                    <?php echo $form->getFieldLabel('follow'); ?>
                    <div class="col-lg-10">
                        <?php echo $form->getFieldHTML('follow'); ?>
                    </div>
                </div>
            <?php } ?>
            <?php if($form->fieldExists('security_code')) { ?>
                <div class="form-group">
                    <?php echo $form->getFieldLabel('security_code'); ?>
                    <div class="col-lg-10">
                        <?php echo $form->getFieldHTML('security_code'); ?>
                    </div>
                </div>
            <?php } ?>
        </fieldset>
        <div class="form-group">
            <div class="col-lg-offset-2 col-lg-10">
                <?php echo $form->getFieldHTML('submit'); ?>
            </div>
        </div>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
    <?php } else { ?>
        <p><a class="btn btn-default" href="<?php echo $login_url; ?>"><?php echo $lang['public_blog_login_comment']; ?></a></p>
    <?php } ?>
<?php } ?>
<?php if($previous_url) { ?>
    <div class="pull-left"><a href="<?php echo $previous_url; ?>" title="<?php echo $previous_title; ?>">&laquo; <?php echo $previous_title; ?></a></div>
<?php } ?>
<?php if($next_url) { ?>
    <div class="pull-right"><a href="<?php echo $next_url; ?>" title="<?php echo $next_title; ?>"><?php echo $next_title; ?> &raquo;</a></div>
<?php } ?>