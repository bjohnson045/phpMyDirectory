<?php if($value != '') { ?>
    <input type="hidden"<?php echo $attributes; ?> value="<?php echo $this->escape($value); ?>">
<?php } ?>
<?php echo $this->escape($value_display); ?>