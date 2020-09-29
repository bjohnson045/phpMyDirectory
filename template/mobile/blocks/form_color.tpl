<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>").minicolors({
            position: 'bottom left'
    });
    $(".minicolors-trigger").css("vertical-align","top");
});
</script>
<div style="vertical-align: top">
    <input type="text" class="text <?php echo $class; ?>" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
</div>