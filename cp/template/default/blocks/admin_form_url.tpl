<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>").bind("keyup", function() {
        var regexp = /https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?/
        if(regexp.test($(this).val())) {
            $("#<?php echo $name; ?>_link_out").removeAttr('disabled');
            $("#<?php echo $name; ?>_link_out").attr("href",$(this).val());
        } else {
            $("#<?php echo $name; ?>_link_out").attr('disabled','disabled');
        }
    });
    $("#<?php echo $name; ?>").trigger("keyup");
});
</script>
<div class="input-group">
    <input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $this->escape($value); ?>" name="<?php echo $name; ?>"<?php echo $attributes; ?> />
    <span class="input-group-btn">
        <a class="btn btn-default" id="<?php echo $name; ?>_link_out" target="_blank" href="" disabled="disabled"><i class="glyphicon glyphicon-new-window"></i></a>
    </span>
</div>