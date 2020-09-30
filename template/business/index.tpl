<?php if($category_columns) { ?>
    <?php echo $this->block('categories_featured'); ?>
<?php } ?>
<?php echo $this->block('listings_featured'); ?>
<?php if($location_columns) { ?>
    <?php echo $this->block('locations_featured'); ?>
<?php } ?>
<?php if($page) { ?>
    <?php echo $page; ?>
<?php } ?>