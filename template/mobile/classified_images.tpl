<h1><a href="<?php echo $classified_url; ?>"><?php echo $this->escape($classified_title) ?></a></h1>
<?php echo $lang['public_classified_from']; ?> <a href="<?php echo $listing['url']; ?>"><?php echo $this->escape($listing['title']); ?></a>
<h2><?php echo $lang['public_classified_images']; ?></h2>
<div class="row">
    <?php foreach($classified_images as $key=>$image) { ?>
        <div class="col-md-3 col-sm-4 col-xs-6">
        <a class="image_group thumbnail" rel="image_group" href="<?php echo $image['image']; ?>" title="<?php echo $this->escape($classified_title) ?>">
            <img src="<?php echo $image['thumb']; ?>" alt="<?php echo $this->escape($classified_title) ?>">
        </a>
    <?php } ?>
<</div>