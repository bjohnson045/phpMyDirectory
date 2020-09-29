<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>").tokenfield({
        createTokensOnBlur: true,
        beautify: false,
    });
});
</script>
<input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $value; ?>"<?php echo $attributes; ?> />
