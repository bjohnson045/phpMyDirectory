<?php if($html) { ?><?php echo $html; ?><?php } ?>
<div class="checkbox"<?php if(isset($wrapper_id)) { ?> id="<?php echo $wrapper_id; ?>"<?php } ?>>
    <label>
        <?php echo $field; ?> <?php echo $option; ?>
    </label>
</div>