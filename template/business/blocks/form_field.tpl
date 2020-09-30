<?php if(!$hidden) { ?>
    <div id="<?php echo $id; ?>-control-group" class="form-group<?php if($error) { ?> has-error<?php } ?>"<?php if($wrapper_attributes) { ?><?php echo $wrapper_attributes; ?><?php } ?>>
        <?php echo $label; ?>
        <div id="<?php echo $id; ?>_controls" class="col-lg-10">
            <?php echo $field; ?>
            <?php if($picker) { ?>
                <?php echo $picker; ?>
            <?php } ?>
            <?php if($counter) { ?>
                <p class="help-block counter"><?php echo $counter; ?></p>
            <?php } ?>
            <?php if($notes) { ?>
                <p class="help-block"><?php echo $notes; ?></p>
            <?php } ?>
        </div>
    </div>
<?php } else { ?>
    <?php echo $field; ?>
<?php } ?>