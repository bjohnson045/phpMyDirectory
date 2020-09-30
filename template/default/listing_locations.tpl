<a class="btn btn-default" href="<?php echo $listing_url; ?>"><span class="fa fa-arrow-left"></span> <?php echo $lang['public_listing_return']; ?></a>
<h2><?php echo $lang['public_listing_locations']; ?></h2>
<?php foreach($locations AS $location) { ?>
    <h4><?php echo $this->escape($location['title']); ?></h4>
    <?php echo $this->escape($location['formatted']); ?>
    <?php if(!empty($location['phone'])) { ?>
        <br><a href="tel:<?php echo $this->escape($location['phone']); ?>"><?php echo $this->escape($location['phone']); ?></a>
    <?php } ?>
    <?php if(!empty($location['url'])) { ?>
        <br><a target="_blank" rel="nofollow" href="<?php echo $this->escape($location['url']); ?>"><?php echo $this->escape($location['url']); ?></a>
    <?php } ?>
    <?php if(!empty($location['email'])) { ?>
        <br><a href="mailto:<?php echo $this->escape($location['email']); ?>"><?php echo $this->escape($location['email']); ?></a>
    <?php } ?>
    <br><a target="_blank" rel="nofollow" href="<?php echo $this->escape($location['map_url']); ?>"><?php echo $lang['public_listing_map']; ?></a>
<?php } ?>