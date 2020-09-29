<span style="display: block; margin-bottom: 5px">
    <?php echo $this->escape_html($selected_content); ?> (<a class="<?php echo $name; ?>_remove_link" href="#"><?php echo $lang['remove']; ?></a>)
    <input type="hidden" id="<?php echo $name; ?>" name="<?php echo $name; ?>[]" value="<?php echo $this->escape($value); ?>" />
</span>