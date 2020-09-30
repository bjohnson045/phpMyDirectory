<div class="well">
    <a class="btn btn-default pull-right" href="<?php echo BASE_URL; ?>/search_results.php?user_id=<?php echo $user['id']; ?>"><?php echo $lang['public_search_users_view_listings']; ?></a>
    <?php echo $this->escape($user['user_first_name']); ?> <?php echo $this->escape($user['user_last_name']); ?><br />
    <?php if(!empty($user['user_organization'])) { ?>
        <?php echo $this->escape($user['user_organization']); ?><br />
    <?php } ?>
    <?php echo $this->escape($user['user_city']); ?>, <?php echo $this->escape($user['user_state']); ?> <?php echo $this->escape($user['user_zip']); ?><br />
    <?php echo $this->escape($user['user_phone']); ?>
</div>