<h3 style="margin: 0 0 25px 0;"><?php echo $this->escape($listing['title']); ?></h3>
<ul data-role="listview" style="margin-bottom: 5px;">
    <li data-role="list-divider" role="heading"><?php echo $lang['public_classified']; ?></li>
    <?php if(!empty($classifieds)) { ?>
        <?php foreach($classifieds AS $classified) { ?>
            <li>
                <a href="<?php echo $classified['url']; ?>">
                    <?php if(!empty($classified['image_url'])) { ?>
                        <img src="<?php echo $classified['image_url']; ?>" alt="<?php echo $this->escape($classified['title']); ?>">
                    <?php } ?>
                    <h3><?php echo $this->escape($classified['title']); ?></h3>
                    <p><?php echo $this->escape($classified['description']); ?></p>
                    <span class="ui-li-count"><?php echo $classified['price']; ?></span>
                </a>
            </li>
        <?php } ?>
    <?php } ?>
</ul>