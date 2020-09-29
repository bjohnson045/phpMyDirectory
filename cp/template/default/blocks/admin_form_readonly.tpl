<?php if($value != '') { ?>
    <input type="hidden"<?php echo $attributes; ?> value="<?php echo $this->escape($value); ?>">
<?php } ?>
<div class="form-control-static">
    <?php echo $this->escape($value_display); ?>
</div>