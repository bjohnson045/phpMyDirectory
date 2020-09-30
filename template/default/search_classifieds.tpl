<?php if($classified_count) { ?>
    <p><strong><?php echo $lang['public_search_classifieds_results']; ?>:</strong> <?php echo $classified_count; ?></p>
    <ul class="media-list">
    <?php foreach($classified_results as $classified) { ?>
        <li class="media">
            <?php if($classified['image_url']) { ?>
                <a class="pull-left" href="<?php echo $classified['url']; ?>">
                    <img class="media-object thumbnail" src="<?php echo $classified['image_url']; ?>" alt="<?php echo $this->escape($classified['title']); ?>">
                </a>
            <?php } ?>
            <div class="media-body">
                <h4 class="media-heading"><a href="<?php echo $classified['url']; ?>"><?php echo $this->escape($classified['title']); ?></a></h4>
                <p><small>from <a href="<?php echo $this->escape($classified['listing_url']); ?>"><?php echo $this->escape($classified['listing_title']); ?></a></small></p>
                <?php echo $this->escape($classified['description']); ?>
            </div>
        </li>
    <?php } ?>
    </ul>
    <div class="text-center">
        <?php echo $page_navigation; ?>
    </div>
<?php } else { ?>
    <?php echo $lang['public_search_classifieds_no_results']; ?>
<?php } ?>
