<div class="well clearfix">
    <?php if($user['profile_image']) { ?>
        <img class="pull-left img-thumbnail" src="<?php echo $user['profile_image']; ?>" title="<?php echo $user['display_name']; ?>"/>
    <?php } ?>
    <a class="btn btn-default pull-right" href="<?php echo BASE_URL; ?>/search_results.php?user_id=<?php echo $user['id']; ?>"><?php echo $lang['public_search_users_view_listings']; ?></a>
    <a class="btn btn-default pull-right" href="<?php echo BASE_URL; ?>/blog.php?user_id=<?php echo $user['id']; ?>"><?php echo $lang['public_search_users_view_blog_posts']; ?></a>
    <b><?php echo $this->escape($user['display_name']); ?></b><br />
    <?php if(!empty($user['user_organization'])) { ?>
        <?php echo $this->escape($user['user_organization']); ?><br />
    <?php } ?>
    <?php echo $this->escape($user['user_city']); ?>, <?php echo $this->escape($user['user_state']); ?> <?php echo $this->escape($user['user_zip']); ?><br />
    <?php echo $this->escape($user['user_phone']); ?>
</div>