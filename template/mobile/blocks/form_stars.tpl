<div class="controlset">
    <ul id="<?php echo $name; ?>_stars" class="star-rating">
        <li id="<?php echo $name; ?>_current_rating" class="current-rating" style="width:<?php echo $image_width; ?>px;"><?php echo $this->escape($value); ?></li>
        <li><a href="" title="1" class="star one-star">1</a></li>
        <li><a href="" title="2" class="star two-stars">2</a></li>
        <li><a href="" title="3" class="star three-stars">3</a></li>
        <li><a href="" title="4" class="star four-stars">4</a></li>
        <li><a href="" title="5" class="star five-stars">5</a></li>
    </ul>
    <input type="hidden" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
</div><script type="text/javascript">
$(document).ready(function() {
    $("#<?php echo $name; ?>_stars .star").click(function(e) {
        e.preventDefault();
        var rating = parseInt($(this).attr('title'));
        $("#<?php echo $name; ?>").val(rating);
        $("#<?php echo $name; ?>_current_rating").css('width',(<?php echo $width; ?>*rating)+"px");
    });
});
</script>