<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $id; ?>_add_input").click(function () {
       $("#<?php echo $id; ?>_group .form-control-container").first().clone().
            find(".input-group-btn").
                removeClass('hidden').
                end().
            wrapInner('<div class="input-group"></div>').
            find("input:text").
                val("").
                autocomplete({source: <?php echo $options; ?>}).
                end()
        .insertBefore($(this).parent()).find("input:text").focus();


        <?php if($limit > 1) { ?>
        if($("#<?php echo $id; ?>_group input:text").length == <?php echo $limit; ?>) {
            $("#<?php echo $id; ?>_add_input").hide();
        }
        <?php } ?>
        $("#<?php echo $id; ?>_counter").text(function(i,txt) {
            return(parseInt(txt,10)+1);
        });
        return false;
    });

    $("#<?php echo $id; ?>_group").on('click','.form-control-group-remove',function () {
        $(this).closest(".form-control-container").remove();
        $("#<?php echo $id; ?>_add_input").show();
        $("#<?php echo $id; ?>_counter").text(function(i,txt) {
            return(parseInt(txt,10)-1);
        });
        return false;
    });

    $("#<?php echo $id; ?>_group input:text").autocomplete({
        source: <?php echo $options; ?>
    });
});
</script>
<div id="<?php echo $id; ?>_group" class="form-control-group">
    <div class="form-control-container">
        <input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $first_value; ?>" name="<?php echo $id; ?>[]" />
        <span class="input-group-btn hidden">
            <a class="btn btn-default btn-danger form-control-group-remove" href="#"><i class="fa fa-times"></i></a>
        </span>
    </div>
    <?php foreach($values AS $key=>$value) { ?>
        <div class="form-control-container">
            <div class="input-group">
                <input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $value; ?>" name="<?php echo $id; ?>[]" />
                <span class="input-group-btn">
                    <a class="btn btn-default btn-danger form-control-group-remove" href="#"><i class="fa fa-times"></i></a>
                </span>
            </div>
        </div>
    <?php } ?>
    <?php if($limit > 1) { ?>
    <p class="help-block">
        <a class="btn btn-default btn-xs"<?php if($counter >= $limit) { ?> style="display: none;"<?php } ?> id="<?php echo $id; ?>_add_input" href="#"><?php echo $lang['add_more']; ?></a>
        <span id="<?php echo $id; ?>_counter"><?php echo $counter; ?></span> / <?php echo $limit; ?>
    </p>
    <?php } ?>
</div>