<?php foreach($hours AS $hour) { ?>
    <?php echo $this->escape($hour['day']); ?> <?php echo $this->escape($hour['start']); ?> - <?php echo $this->escape($hour['end']); ?><br />
<?php } ?>