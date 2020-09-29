<div>
    <select id="<?php echo $name; ?>_select_<?php echo $level; ?>" class="form-control" style="margin-bottom: 5px;">
        <?php echo $options; ?>
    </select>
</div>  <?php if(isset($add_link)) { ?><a class="btn btn-default btn-xs" id="<?php echo $name; ?>_add" href="#"><?php echo $lang['add']; ?></a><?php } ?>