<script type="text/javascript">
$(document).ready(function() {
    var <?php echo $name; ?>_value;
    $("#<?php echo $name; ?>-toggle").change(function() {
        if($(this).is(":checked")) {
            $("#<?php echo $name; ?>").val(<?php echo $name; ?>_value).prop('readonly', false);
        } else {
            <?php echo $name; ?>_value = $("#<?php echo $name; ?>").val();
            $("#<?php echo $name; ?>").val("0").prop('readonly',true);
        }
    });
});
</script>
<div class="input-group">
    <span class="input-group-addon">
        <input type="checkbox" id="<?php echo $name; ?>-toggle"<?php if($value != 0) { ?> checked="checked"<?php } ?>> On
    </span>
    <input type="text" class="form-control <?php echo $class; ?>"<?php if($value == 0) { ?> readonly="readonly"<?php } ?> value="<?php echo $value; ?>"<?php echo $attributes; ?> />
</div>
