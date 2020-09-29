<div class="input-group">
    <?php if($prepend) { ?>
        <span class="input-group-addon"><?php echo $prepend; ?></span>
    <?php } ?>
    <input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $value; ?>"<?php echo $attributes; ?> />
    <?php if($append) { ?>
        <span class="input-group-addon"><?php echo $append; ?></span>
    <?php } ?>
</div>