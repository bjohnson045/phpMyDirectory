<script type="text/javascript">
$(document).ready(function() {
    var <?php echo $name; ?>_value;
    $("#<?php echo $name; ?>-unlimited").change(function() {
        if($(this).is(":checked")) {
            <?php echo $name; ?>_value = $("#<?php echo $name; ?>").val();
            $("#<?php echo $name; ?>").val("0").prop('disabled',true);
        } else {
            $("#<?php echo $name; ?>").val(<?php echo $name; ?>_value).prop('disabled', false);
        }
    });
});
</script>
<div class="input-group">
    <input type="text" class="form-control <?php echo $class; ?>"<?php if($value == 0) { ?> disabled="disabled"<?php } ?> value="<?php echo $value; ?>"<?php echo $attributes; ?> />
    <span class="input-group-addon">
        <input type="checkbox" id="<?php echo $name; ?>-unlimited"<?php if($value == 0) { ?> checked="checked"<?php } ?>> Unlimited
    </span>
</div>
