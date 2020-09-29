<?php if(sizeof($listings) < 1) { ?>
    <div class="panel-body">
        <p class="text-center"><?php echo $lang['no_listings']; ?></p>
    </div>
<?php } else { ?>
    <ul class="list-group">
        <?php foreach($listings as $listing) { ?>
            <li class="list-group-item">
                <h5 class="list-group-item-heading"><a href="<?php echo $listing['link']; ?>" title="<?php echo $this->escape($listing['title']); ?>"><?php echo $this->escape($listing['title']); ?></a></h5>
                <?php if(!empty($listing['address'])) { ?>
                    <p class="text-muted"><small><?php echo $this->escape($listing['address']); ?></small></p>
                <?php } ?>
                <?php if(!empty($listing['description_short'])) { ?>
                    <p><?php echo $this->escape_html($listing['description_short']); ?></p>
                <?php } ?>
                <?php if(!empty($listing['details'])) { ?>
                    <p><small class="text-muted tiny"><?php echo $this->escape($listing['details']); ?></small></p>
                <?php } ?>
            </li>
        <?php } ?>
    </ul>
<?php } ?>