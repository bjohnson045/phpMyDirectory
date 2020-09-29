<div class="row">
    <div class="col-lg-12">
        <h1><?php echo $this->escape($title); ?></h1>
        <?php if($listing_title) { ?>
            <p><?php echo $lang['public_classified_from']; ?> <?php echo $this->escape($listing_title); ?></p>
        <?php } ?>
        <?php if($price) { ?>
            <p><strong><?php echo $lang['public_classified_price']; ?>:</strong> <?php echo $this->escape($price); ?></p>
        <?php } ?>
        <p><strong><?php echo $lang['public_classified_date']; ?>:</strong> <?php echo $this->escape($date); ?></p>
        <?php if($expire_date) { ?>
            <p><strong><?php echo $lang['public_classified_expire_date']; ?>:</strong> <?php echo $this->escape($expire_date); ?></p>
        <?php } ?>
        <?php if($description) { ?>
            <h2><?php echo $lang['public_classified_description']; ?></h2>
            <?php echo $this->escape_html($description); ?>
        <?php } ?>
        <?php echo $custom_fields; ?>
        <h2><?php echo $lang['public_classified_images']; ?></h2>
        <?php if(is_array($classified_images)) { ?>
            <?php foreach($classified_images AS $image_key=>$image) { ?>
                <img alt="<?php echo $this->escape($title); ?>" class="thumbnail" src="<?php echo $image['thumbnail_url']; ?>" />
            <?php } ?>
        <?php } ?>
        <p><a href="#" class="btn btn-default" onclick="window.print()"><span class="fa fa-print"></span> Print Page</a></p>
        <p><?php echo $classified_url; ?></p>
    </div>
</div>