<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_listing_images']; ?></h2>
<div class="row">
    <?php foreach($images as $key=>$image) { ?>
        <div class="col-md-3 col-sm-4 col-xs-6">
            <a class="image_group thumbnail" rel="image_group" href="<?php echo $image['image']; ?>" title="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
            </a>
        </div>
        <?php if($key+1 % 4 == 0) { ?>
            <div class="clearfix visible-md-block"></div>
        <?php } ?>
        <?php if($key+1 % 3 == 0) { ?>
            <div class="clearfix visible-sm-block"></div>
        <?php } ?>
        <?php if($key+1 % 2 == 0) { ?>
            <div class="clearfix visible-xs-block"></div>
        <?php } ?>
    <?php } ?>
</div>
<div class="text-center">
    <?php echo $page_navigation; ?>
</div>