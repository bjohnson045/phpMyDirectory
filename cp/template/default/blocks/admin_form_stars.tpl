<div id="<?php echo $name; ?>_stars" class="stars form-control-static">
    <span class="text-warning fa fa-lg fa-star-o" data-rating="1"></span>
    <span class="text-warning fa fa-lg fa-star-o" data-rating="2"></span>
    <span class="text-warning fa fa-lg fa-star-o" data-rating="3"></span>
    <span class="text-warning fa fa-lg fa-star-o" data-rating="4"></span>
    <span class="text-warning fa fa-lg fa-star-o" data-rating="5"></span>
    <input type="hidden" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?>>
</div>
<script type="text/javascript">
var <?php echo $name; ?>_reset = function() {
    $('#<?php echo $name; ?>_stars .fa').each(function() {
        if(parseInt($(this).siblings('#<?php echo $name; ?>').val()) >= parseInt($(this).data('rating'))) {
            return $(this).removeClass('fa-star-o text-danger').addClass('fa-star text-warning');
        } else {
            return $(this).removeClass('fa-star text-danger').addClass('fa-star-o text-warning');
        }
    });
}
<?php echo $name; ?>_reset();
$('#<?php echo $name; ?>_stars .fa').hover(
    function() {
        $(this).prevAll().andSelf().removeClass('fa-star-o').removeClass('text-warning').addClass('fa-star').addClass('text-danger');
        if(parseInt($(this).data('rating')) <= parseInt($('#<?php echo $name; ?>').val())) {
            $(this).nextAll().each(function() {
                if(parseInt($(this).data('rating')) <= parseInt($('#<?php echo $name; ?>').val())) {
                    $(this).removeClass('text-danger').addClass('text-warning');
                } else {
                    $(this).removeClass('fa-star text-danger').addClass('fa-star-o text-warning');
                }
            });
        } else {
            $(this).nextAll().removeClass('fa-star text-danger').addClass('fa-star-o text-warning');
        }
    },
    function() {}
);
$('#<?php echo $name; ?>_stars').hover(function(){},function(){
    <?php echo $name; ?>_reset();
});

$('#<?php echo $name; ?>_stars .fa').on('click', function() {
    $('#<?php echo $name; ?>').val($(this).data('rating'));
    return <?php echo $name; ?>_reset();
});
</script>