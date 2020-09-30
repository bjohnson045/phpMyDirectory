<p>
<?php echo $lang['public_classified_images_for']; ?> <a href="<?php echo $classified_url; ?>"><?php echo $this->escape($classified_title) ?></a>
<?php echo $lang['public_classified_from']; ?> <a href="<?php echo $listing['url']; ?>"><?php echo $this->escape($listing['title']); ?></a>
</p>
<div class="row">
    <?php foreach($classified_images as $key=>$image) { ?>
        <div class="col-md-3 col-sm-4 col-xs-6">
            <a class="image_group thumbnail" rel="image_group" href="<?php echo $image['image']; ?>" title="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
                <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $this->escape($image['title']); ?><?php if($image['description']) { ?> - <?php } ?><?php echo $this->escape($image['description']); ?>">
            </a>
        </div>
    <?php } ?>
</div>