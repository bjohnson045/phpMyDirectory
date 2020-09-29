<div class="custom">
    <?php if($value != '') { ?>
        <input type="hidden"<?php echo $attributes; ?> value="<?php echo $this->escape($value); ?>">
    <?php } ?>
    <?php if(isset($html)) { ?>
        <?php echo $html; ?>
    <?php } else { ?>
        <?php echo $this->escape($value); ?>
    <?php } ?>
</div>