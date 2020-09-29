<div>
    <select class="form-control" id="<?php echo $this->escape($name); ?>_select_<?php echo $level; ?>" style="margin-bottom: 5px;">
        <?php echo $options; ?>
    </select>
</div>  <?php if(isset($add_link)) { ?><a id="<?php echo $this->escape($name); ?>_add" href="#"><?php echo $lang['add']; ?></a><?php } ?>