<?php if($image_count) { ?>
    <p><strong><?php echo $lang['public_search_images_results']; ?>:</strong> <?php echo $image_count; ?></p>
    <ul class="media-list">
    <?php foreach($image_results as $image) { ?>
        <li class="media">
            <?php if($image['image']) { ?>
                <a class="image_group pull-left" rel="image_group" href="<?php echo $image['image']; ?>">
                    <img class="media-object thumbnail" src="<?php echo $image['thumb']; ?>">
                </a>
            <?php } ?>
            <div class="media-body">
                <h4 class="media-heading"><?php echo $this->escape($image['title']); ?></h4>
                <p><small>from <a href="<?php echo $this->escape($image['url']); ?>"><?php echo $this->escape($image['listing_title']); ?></a></small></p>
                <?php echo $this->escape($image['description']); ?>
            </div>
        </li>
    <?php } ?>
    </ul>
    <div class="text-center">
        <?php echo $page_navigation; ?>
    </div>
<?php } else { ?>
    <?php echo $lang['public_search_images_no_results']; ?>
<?php } ?>