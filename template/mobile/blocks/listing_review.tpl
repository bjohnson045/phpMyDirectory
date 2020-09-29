<li>
    <?php if(!isset($_GET['review_id'])) { ?><a href="<?php echo BASE_URL; ?>/listing_reviews.php?review_id=<?php echo $id; ?>" title="<?php echo $this->escape($title); ?>"><?php } ?>
    <p class="ui-li-aside ui-li-desc">
        <strong><?php echo $lang['public_listing_reviews_by']; ?>: <?php echo $this->escape($login); ?></strong><br />
        <?php echo $date; ?><br />
        <?php if($comment_count AND !$comments) { ?>
            <?php echo $lang['public_listing_reviews_comments']; ?>: <?php echo $comment_count; ?>
        <?php } ?>
    </p>
    <?php if($rating > 0) { ?>
        <div><?php echo $rating_static; ?></div>
    <?php } ?>
    <h2 style="white-space:normal"><?php echo $this->escape($title); ?></h2>
    <?php foreach($custom_fields_groups as $custom_fields_group) { ?>
        <strong><?php echo $this->escape($custom_fields_group['title']); ?></strong><br />
        <?php foreach($custom_fields_group['fields'] AS $field) { ?>
            <?php if(${"custom_".$field['id']} != '') { ?>
                <?php if($field['type'] == 'htmleditor') { ?>
                    <?php echo $this->escape($field['name']); ?>: <?php echo $this->escape_html(${"custom_".$field['id']}); ?><br />
                <?php } else { ?>
                    <?php echo $this->escape($field['name']); ?>: <?php echo $this->escape(${"custom_".$field['id']}); ?><br />
                <?php } ?>
            <?php } ?>
        <?php } ?>
        <br /><br />
    <?php } ?>
    <?php if($helpful_total) { ?>
        <p><?php echo $helpful_count; ?> <?php echo $lang['public_listing_reviews_of']; ?> <?php echo $helpful_total; ?> <?php echo $lang['public_listing_reviews_helpful']; ?>.</p>
    <?php } ?>
    <p><strong><?php echo $lang['public_listing_reviews_review']; ?>:</strong></p>
    <p style="white-space:normal"><?php echo $this->escape_html($review); ?></p>
    <?php if(!isset($_GET['review_id'])) { ?></a><?php } ?>
</li>
<?php if(count($comments) > 0) { ?>
    <li data-role="list-divider" role="heading">
        <?php echo $lang['public_listing_reviews_comments']; ?>:
    </li>
        <?php foreach($comments as $comment) { ?>
            <li>
                <h2 style="white-space:normal"><?php echo $lang['public_listing_reviews_by']; ?>: <?php echo $this->escape($comment['login']); ?></h2>
                <p><strong><?php echo $comment['date']; ?></strong></p>
                <p style="white-space:normal"><?php echo $this->escape($comment['comment']); ?></p>
            </li>
        <?php } ?>
<?php } ?>

