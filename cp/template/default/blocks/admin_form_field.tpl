<?php if(!isset($hidden) OR !$hidden) { ?>
    <div id="<?php echo $id; ?>-control-group" class="form-group <?php echo $type; ?>"<?php if(isset($wrapper_attributes)) { ?><?php echo $wrapper_attributes; ?><?php } ?>>
        <?php echo $label; ?>
        <div id="<?php echo $id; ?>_controls" class="col-sm-18 col-md-12 col-lg-8 controls">
            <?php echo $field; ?>
            <?php if(isset($picker)) { ?>
                <?php echo $picker; ?>
            <?php } ?>
            <?php if(isset($counter)) { ?>
                <span class="help-block counter"><?php echo $counter; ?></span>
            <?php } ?>
            <?php if(isset($notes)) { ?>
                <span class="help-block"><?php echo $notes; ?></span>
            <?php } ?>
        </div>
    </div>
<?php } else { ?>
    <?php echo $field; ?>
<?php } ?>