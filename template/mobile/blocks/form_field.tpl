<?php if(!$hidden) { ?>
    <li id="<?php echo $id; ?>_row" <?php if($wrapper_attributes) { ?><?php echo $wrapper_attributes; ?><?php } ?>>
        <?php echo $label; ?>
        <?php echo $field; ?>
        <?php if($picker) { ?>
            <?php echo $picker; ?>
        <?php } ?>
        <?php if($counter) { ?>
            <p class="note counter"><?php echo $counter; ?></p>
        <?php } ?>
        <?php if($notes) { ?>
            <p class="note"><?php echo $notes; ?></p>
        <?php } ?>
    </li>
<?php } else { ?>
    <?php echo $field; ?>
<?php } ?>