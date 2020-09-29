<script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>").minicolors({theme: 'bootstrap'});
    $(".minicolors-trigger").css("vertical-align","top");
    $(".<?php echo $name; ?>_colors").click(function(){
         rgb = $(this).css('background-color').match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
         hex = "#"+
         ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
         ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
         ("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
        $("#<?php echo $name; ?>").minicolors('value',hex);
    });
});
</script>
<div style="vertical-align: top">
    <input type="text" class="form-control <?php echo $class; ?>" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
</div>
<?php if($predefined) { ?>
    <?php foreach($predefined_colors AS $color) { ?>
        <div class="<?php echo $name; ?>_colors" style="cursor: pointer; display: inline-block; height: 20px; width: 20px; margin: 10px 2.5px 0 0; background-color: #<?php echo $color; ?>">
        </div>
    <?php } ?>
<?php } ?>