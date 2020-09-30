<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $id; ?>_add_input").click(function () {
       $("#<?php echo $id; ?>_group .form-control-container").first().clone().
            find("input:text").
                val("").
                end()
        .insertBefore($(this).parent()).find("input:text").focus();
        return false;
    });

    $("#<?php echo $id; ?>_group").on('click','.form-control-group-remove',function () {
        $(this).closest(".form-control-container").remove();
        return false;
    });
});
</script>
<div id="<?php echo $id; ?>_group" class="form-control-group">
    <div class="form-control-container">
        <input type="text" placeholder="<?php echo $lang['url_title_text']; ?>" class="form-control <?php echo $class; ?>" value="<?php echo $first_value['url_title']; ?>" name="<?php echo $name; ?>_title[]" />
        <input type="text" style="margin-top: 5px;" placeholder="<?php echo $lang['url']; ?>" class="form-control <?php echo $class; ?>" value="<?php echo $first_value['url']; ?>" name="<?php echo $name; ?>[]"<?php echo $attributes; ?> />
    </div>
    <?php foreach($values AS $key=>$value) { ?>
        <div class="form-control-container">
            <input type="text" placeholder="<?php echo $lang['url_title_text']; ?>" class="form-control <?php echo $class; ?>" value="<?php echo $value['url_title']; ?>" name="<?php echo $name; ?>_title[]" />
            <input type="text" style="margin-top: 5px;" placeholder="<?php echo $lang['url']; ?>" class="form-control <?php echo $class; ?>" value="<?php echo $value['url']; ?>" name="<?php echo $name; ?>[]"<?php echo $attributes; ?> />
        </div>
    <?php } ?>
    <p class="help-block">
        <a class="btn btn-default btn-xs" id="<?php echo $id; ?>_add_input" href="#"><?php echo $lang['add_more']; ?></a>
    </p>
</div>