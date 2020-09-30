<span style="display: block; margin-bottom: 5px">
    <?php echo $this->escape_html($selected_content); ?> (<a class="<?php echo $this->escape($name); ?>_remove_link" href="#"><?php echo $lang['remove']; ?></a>)
    <input type="hidden" id="<?php echo $this->escape($name); ?>" name="<?php echo $this->escape($name); ?>[]" value="<?php echo $value; ?>" />
</span>