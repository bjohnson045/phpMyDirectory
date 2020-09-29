<div class="panel panel-success">
    <div class="panel-heading">
        <h3 class="panel-title"><?php echo $lang['block_listings_featured']; ?><a class="pull-right" href="<?php echo BASE_URL; ?>/xml.php?type=rss_featured_listings"><i class="fa fa-rss"></i></a></h3>
    </div>
    <div class="panel-body">
    <?php if(sizeof($listings) < 1) { ?>
        <p class="text-center"><?php echo $lang['no_listings']; ?></p>
    <?php } else { ?>
        <div class="row">
            <?php foreach($listings as $key=>$listing) { ?>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <?php if(!empty($listing['logo_thumb_url'])) { ?>
                        <img class="img-thumbnail" alt="<?php echo $this->escape($listing['title']); ?>" src="<?php echo $listing['logo_thumb_url']; ?>">
                    <?php } ?>
                    <h5><a href="<?php echo $listing['link']; ?>" title="<?php echo $this->escape($listing['title']); ?>"><?php echo $this->escape($listing['title']); ?></a></h5>
                    <?php if(!empty($listing['address'])) { ?>
                        <p class="text-muted"><small><?php echo $this->escape($listing['address']); ?></small></p>
                    <?php } ?>
                    <?php if(!empty($listing['description_short'])) { ?>
                        <p><?php echo $this->escape_html($listing['description_short']); ?></p>
                    <?php } ?>
                    <?php if(!empty($listing['details'])) { ?>
                        <p><small class="text-muted tiny"><?php echo $this->escape($listing['details']); ?></small></p>
                    <?php } ?>
                    <?php if(($key+1)%4 == 0) { ?>
                        <div class="clearfix"></div>
                    <?php } ?>
                </div>
                <?php if(($key+1)%2 == 0) { ?>
                    <div class="clearfix visible-sm-block"></div>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    </div>
</div>