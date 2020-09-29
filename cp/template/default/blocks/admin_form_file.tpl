<div id="<?php echo $id; ?>_cover" class="file-cover"></div>
<span class="btn btn-default file-button">
    <i class="glyphicon glyphicon-hdd"></i>
    <span><?php echo $lang['browse']; ?></span>
    <?php echo $field; ?>
</span>
<?php if($url_allow) { ?>
<span id="<?php echo $id; ?>_url_button" class="btn btn-default">
    <i class="glyphicon glyphicon-globe"></i>
    <?php echo $lang['url']; ?>
</span>
<?php } ?>
<span class="btn btn-default file-button-clear" id="<?php echo $id; ?>_clear">
    <i class="glyphicon glyphicon-remove"></i>
    <span><?php echo $lang['remove']; ?></span>
</span>
<?php if($value AND $delete_url) { ?>
    <a class="btn btn-danger" href="<?php echo $delete_url; ?>"><i class="glyphicon glyphicon-remove"></i> <?php echo $lang['delete']; ?></a>
<?php } ?>
<?php if($url_image) { ?>
    <div class="thumbnail help-block">
        <img src="<?php echo $url_image; ?>">
    </div>
<?php } ?>
<?php if($url_allow) { ?><input type="text" class="form-control" value="<?php echo $value; ?>" id="<?php echo $id; ?>_url" name="<?php echo $id; ?>_url" placeholder="URL" style="margin-top: 5px; display:none"/><?php } ?>
<script type="text/javascript">
$("input[id='<?php echo $id; ?>']").change(function() {
    <?php if($url_allow) { ?>
    $("#<?php echo $id; ?>_url").hide().val('');
    $("#<?php echo $id; ?>_url_button").hide();
    <?php } ?>
    // This fails with non-word characters
    $("#<?php echo $id; ?>_cover").html($(this).val().match(/[^/\\]*[.][\w]+$/i)[0]).css('display', 'inline');
    $("#<?php echo $id; ?>_clear").css('display','inline-block');
});

$("#<?php echo $id; ?>_clear").on("click", function() {
    control = $("input[id='<?php echo $id; ?>']");
    control.replaceWith(control = control.clone(true));
    $("#<?php echo $id; ?>_cover").html('').css('display','none');
    $("#<?php echo $id; ?>_clear").hide();
    <?php if($url_allow) { ?>
    $("#<?php echo $id; ?>_url_button").show();
    <?php } ?>
});
<?php if($url_allow) { ?>
$("#<?php echo $id; ?>_url_button").click(function() {
    $("#<?php echo $id; ?>_url").show();
});
    <?php if(!empty($value)) { ?>
        $(document).ready(function(){
            $("#<?php echo $id; ?>_url_button").trigger("click");
        });
    <?php } ?>
<?php } ?>
</script>