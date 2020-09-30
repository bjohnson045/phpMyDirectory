<span id="<?php echo $id; ?>_display"></span>
<div id="<?php echo $id; ?>_window" style="padding-top: 10px;">
    Search: <input type="text" class="text" id="<?php echo $id; ?>_window_search" style="width: 200px">
    <div id="<?php echo $id; ?>_window_content" style="padding-top: 10px;"></div>
</div>
<?php if(isset($icon)) { ?>
    <?php echo $icon; ?>
<?php } ?>
<a id="<?php echo $id; ?>_window_link" href="#">Select <?php echo $label ; ?> &raquo;</a></div>
<?php echo $field; ?>