<li>
    <a href="<?php echo $link; ?>">
        <h3><?php echo $this->escape($title); ?></h3>
        <p><?php echo $this->escape($address); ?></p>
        <p><?php echo $rating; ?></p>
        <?php if($phone) { ?><p><?php echo $this->escape($phone); ?></p><?php } ?>
        <?php if($zip_distance) { ?>
            <?php //echo $zip_distance; ?>
        <?php } ?>
    </a>
</li>