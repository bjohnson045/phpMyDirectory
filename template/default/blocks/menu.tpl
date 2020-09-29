<?php if($level == 0) { ?>
    <div class="list-group">
        <?php echo $items; ?>
    </div>
<?php } else { ?>
    <span id="sub_menu<?php echo $parent_id; ?>" class="collapse">
        <?php echo $items; ?>
    </span>
<?php } ?>
