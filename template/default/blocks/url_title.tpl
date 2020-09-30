<?php if($title != '') { ?>
    <a href="<?php echo $this->escape($url); ?>" title="<?php echo $this->escape($title); ?>" target="_blank"><?php echo $this->escape($title); ?></a><br>
<?php } else { ?>
    <a href="<?php echo $this->escape($url); ?>" target="_blank"><?php echo $this->escape($url); ?></a><br>
<?php } ?>