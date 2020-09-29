<h3 style="margin: 0 0 25px 0;"><?php echo $this->escape($listing['title']); ?></h3>
<ul data-role="listview" style="margin-bottom: 5px;">
    <li data-role="list-divider" role="heading"><?php echo $lang['public_listing_images']; ?></li>
    <?php foreach($images as $key=>$image) { ?>
        <li>
            <a data-rel="popup" href="#image<?php echo $key; ?>" title="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                <h3><?php echo $this->escape($image['title']); ?></h3>
                <p><?php echo $this->escape($image['description']); ?></p>
                <div data-role="popup" id="image<?php echo $key; ?>">
                    <a href="#" data-rel="back" data-role="button" data-theme="a" data-icon="delete" data-iconpos="notext" class="ui-btn-right">Close</a>
                    <img src="<?php echo $image['image']; ?>" alt="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                </div>
            </a>
        </li>
    <?php } ?>
</ul>
<br class="clear" />