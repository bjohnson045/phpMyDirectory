<div>
    <select id="<?php echo $name; ?>_select_<?php echo $level; ?>" class="select" style="margin-bottom: 5px;">
        <?php echo $options; ?>
    </select>
</div>  <?php if(isset($add_link)) { ?><a id="<?php echo $name; ?>_add" href="#"><?php echo $lang['add']; ?></a><?php } ?>