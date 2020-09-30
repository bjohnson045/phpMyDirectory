<div id="<?php echo $name; ?>_stars" class="stars form-control-static">
    <span class="star deactive" data-rating="1"></span>
    <span class="star deactive" data-rating="2"></span>
    <span class="star deactive" data-rating="3"></span>
    <span class="star deactive" data-rating="4"></span>
    <span class="star deactive" data-rating="5"></span>
    <input type="hidden" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
</div>
<script type="text/javascript">
// Reset the stars based on the hidden input value
var <?php echo $name; ?>_reset = function() {
    // Cycle through each star
    $('#<?php echo $name; ?>_stars .star').each(function() {
        // Remove the hover class and reapply the standard class
        $(this).removeClass('hover');
        // If the star is less than or equal to the current value, fill in the star
        if(parseInt($('#<?php echo $name; ?>').val()) >= parseInt($(this).data('rating'))) {
            return $(this).removeClass('deactive').addClass('active');
        } else {
            return $(this).removeClass('active').addClass('deactive');
        }
    });
}
$('#<?php echo $name; ?>_stars .star').on({
    // When hovering over each star
    mouseenter: function() {
        // Fill in the star and apply the hover class to the star and any stars before it
        $(this).prevAll().andSelf().removeClass('deactive active').addClass('hover');
        // For each star after the one being hovered on
        $(this).nextAll().each(function() {
            // Remove the hover class and reapply the standard class
            $(this).removeClass('hover').addClass('active');
            // If the star is greater than the current value empty the star
            if(parseInt($(this).data('rating')) > parseInt($('#<?php echo $name; ?>').val())) {
                $(this).removeClass('active').addClass('deactive');
            }
        });
    },
    // Set the value when a star is clicked, and reset the stars based on the new value
    click: function() {
        $('#<?php echo $name; ?>').val($(this).data('rating'));
        return <?php echo $name; ?>_reset();
    }
});
// When hovering completely out of the stars element, reset the stars based on the current value
$('#<?php echo $name; ?>_stars').hover(function(){},function(){
    <?php echo $name; ?>_reset();
});
// Initially reset the stars based on the current value
<?php echo $name; ?>_reset();
</script>