<select class="select <?php echo $class; ?>"<?php echo $attributes; ?>>
<?php if(!empty($first_options)) { ?>
<?php foreach($first_options AS $first_option_value=>$first_option) { ?>
    <option value="<?php echo $this->escape($first_option_value); ?>"><?php echo $first_option; ?></option>
<?php } ?>
<?php } ?>
<?php if(!empty($options)) { ?>
<?php foreach($options AS $option_value=>$option) { ?>
    <option value="<?php echo $this->escape($option_value); ?>"<?php if($option_value == $value) { ?> selected="selected"<?php } ?>>
        <?php echo $option; ?>
    </option>
<?php } ?>
<?php } ?>
<?php if($html) { ?>
    <?php echo $html; ?>
<?php } ?>
</select>
