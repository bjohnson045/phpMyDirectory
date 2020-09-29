<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>").minicolors({
            position: 'bottom left',
            theme: 'bootstrap'
    });

});
</script>
<input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
