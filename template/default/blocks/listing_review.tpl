<?php if(isset($javascript)) { ?>
<script type="text/javascript">
$(document).ready(function() {
    // Reviews
    $('.comment-add').click(function(e){
        e.preventDefault();
        $('#comment-form-'+$(this).data('review-id')).slideDown('slow');
        $('#comment-'+$(this).data('review-id')).focus();
    });
    <?php if($_GET['action'] == 'respond') { ?>
        $('.comment-add').trigger('click');
    <?php } ?>
    $('.comment-cancel').click(function(e){
        e.preventDefault();
        $('#comment-form-'+$(this).data('review-id')).slideUp('slow');
    });
    $('.comment-submit').click(function(e) {
        e.preventDefault();
        review_id = $(this).data('review-id');
        $.ajax({
            data: ({
                action: 'add_comment',
                id: review_id,
                comment: $('#comment-'+review_id).val()
            }),
            success: function() {
                $('#comment-form-'+review_id).slideUp('slow');
                $('#comment-response-'+review_id).addClass('alert-warning').text('<?php echo $lang['public_listing_reviews_comment_pending']; ?>').show();
            }
        });
    });
    $('.helpful').click(function(e) {
        e.preventDefault();
        review_id = $(this).data('review-id');
        $.ajax({
            data: ({
                action: 'add_quality',
                id: review_id,
                helpful: $(this).data('helpful')
            }),
            success: function(){
                $('#helpful-'+review_id).hide();
                $('#helpful-message-'+review_id).show();
            }
        });
    });
});
</script>
<?php } ?>
<div class="row row-spaced">
    <div class="<?php if($categories) { ?>col-lg-8<?php } else { ?>col-lg-12<?php } ?>">
        <?php if(isset($profile_image_url)) { ?>
        <div class="pull-left" style="margin: 0 10px 10px 0">
            <img class="img-thumbnail" style="width: 100px" src="<?php echo $profile_image_url; ?>" alt="<?php echo $this->escape($login); ?>" /><br />
        </div>
        <?php } ?>
        <?php if($helpful_total) { ?>
            <p><small><?php echo $helpful_count; ?> <?php echo $lang['public_listing_reviews_of']; ?> <?php echo $helpful_total; ?> <?php echo $lang['public_listing_reviews_helpful']; ?></small></p>
        <?php } ?>
        <?php if($rating > 0) { ?><div class="pull-left"><?php echo $rating_static; ?></div>&nbsp;<?php } ?><b><a href="<?php echo BASE_URL; ?>/listing_reviews.php?review_id=<?php echo $id; ?>"><?php echo $this->escape($title); ?></a></b>, <?php echo $date; ?> <?php echo $time; ?>
        <p><?php echo $lang['public_listing_reviews_by']; ?>: <?php echo $this->escape($login); ?></p>
        <p><?php echo $this->escape_html($review); ?></p>
        <?php echo $custom_fields; ?>
    </div>
    <?php if($categories) { ?>
        <div class="col-lg-4">
            <p><strong><?php echo $lang['public_listing_reviews_ratings_categories']; ?></strong></p>
            <?php foreach($categories AS $category) { ?>
                <div class="clear-left">
                    <div class="pull-left" style="margin-right: 5px;"><?php echo $category['rating_static']; ?></div>
                    <div><?php echo $this->escape($category['title']); ?></div>
                </div>
            <?php } ?>
        </div>
    <?php } ?>
</div>
<div class="row">
    <div class="col-sm-12 col-lg-5">
        <p>
        <?php if($comment_count AND !$comments) { ?>
            <a class="btn btn-default btn-sm" href="<?php echo BASE_URL; ?>/listing_reviews.php?review_id=<?php echo $id; ?>"><span class="fa fa-comment"></span> <?php echo $lang['public_listing_reviews_view_comments']; ?> (<?php echo $comment_count; ?>)</a>
        <?php } ?>
        <?php if(LOGGED_IN) { ?>
            <a href="" data-review-id="<?php echo $id; ?>" class="btn btn-sm btn-default comment-add"><span class="fa fa-plus"></span> <?php echo $lang['public_listing_reviews_add_comment']; ?></a>
            </p>
            </div>
            <div class="col-sm-12 col-lg-7">
            <p>
            <span id="helpful-<?php echo $id; ?>">
                <?php echo $lang['public_listing_reviews_was_helpful']; ?>
                <a href="" class="btn btn-default btn-xs helpful" data-review-id="<?php echo $id; ?>" data-helpful="1"><span class="fa fa-thumbs-o-up"></span><span class="hidden-xs"> <?php echo $lang['public_listing_reviews_yes']; ?></span></a> <?php echo $lang['public_listing_reviews_or']; ?>
                <a href="" class="btn btn-default btn-xs helpful" data-review-id="<?php echo $id; ?>" data-helpful="0"><span class="fa fa-thumbs-o-down"></span><span class="hidden-xs"> <?php echo $lang['public_listing_reviews_no']; ?></span></a>
            </span>
            <span style="display: none;" id="helpful-message-<?php echo $id; ?>">
                <b><?php echo $lang['public_listing_reviews_voted']; ?></b>
            </span>
        <?php } ?>
        </p>
    </div>
</div>
<?php echo $share; ?>
<?php if(LOGGED_IN) { ?>
    <div style="display: none;" id="comment-form-<?php echo $id; ?>">
        <h4><?php echo $lang['public_listing_reviews_add_comment']; ?></h4>
        <div class="form-group">
            <textarea data-review-id="<?php echo $id; ?>" id="comment-<?php echo $id; ?>" name="comment" class="form-control"></textarea>
        </div>
        <a href="" class="btn btn-default comment-submit" data-review-id="<?php echo $id; ?>"><?php echo $lang['public_submit']; ?></a>
        <a href="" class="btn btn-default btn-danger comment-cancel" data-review-id="<?php echo $id; ?>"><?php echo $lang['public_listing_reviews_cancel']; ?></a>
    </div>
    <div id="comment-response-<?php echo $id; ?>" class="alert" style="display: none;"></div>
<?php } ?>
<?php if(is_array($comments) AND count($comments) > 0) { ?>
    <h4><?php echo $lang['public_listing_reviews_comments']; ?>:</h4>
    <?php foreach($comments as $comment) { ?>
        <blockquote>
            <p><?php echo $this->escape($comment['comment']); ?></p>
            <small>
                <?php echo $lang['public_listing_reviews_by']; ?>: <?php echo $this->escape($comment['login']); ?>, <?php echo $comment['date']; ?>
                <?php if($comment['owner']) { ?>
                    <span class="label label-success"><?php echo $lang['public_listing_reviews_owner']; ?></span>
                <?php } ?>
            </small>
        </blockquote>
    <?php } ?>
<?php } ?>