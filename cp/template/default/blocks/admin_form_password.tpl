<?php if($generate) { ?>
    <div class="input-group">
<?php } ?>
    <input type="<?php if($plaintext) { ?>text<?php } else { ?>password<?php } ?>" class="<?php echo $class; ?> form-control" value="<?php echo $value; ?>" autocomplete="off"<?php echo $attributes; ?>/>
<?php if($generate) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#<?php echo $name; ?>_generate_password").click(function() {
            $.ajax({ data: ({ action: "random_string" }), success:
                function(data) {
                    $("#<?php echo $name; ?>").val(data);
                    $("#<?php echo $name; ?>").focus();
                    $("#<?php echo $name; ?>").trigger("keyup");
                }
            });
        });
    });
    </script>
    <span class="input-group-btn">
        <a id="<?php echo $name; ?>_generate_password" target="_blank" class="btn btn-default">Generate</a>
    </span>
</div>
<?php } ?>
<?php if($strength) { ?>
    <div id="<?php echo $name; ?>_strength_container" class="password_strength_container">
        <?php echo $strength_label; ?>: <div id="<?php echo $name; ?>_strength" class="password_strength">&nbsp;</div>
    </div>
    <script type="text/javascript">
        $(document).ready(function(){
            $("#<?php echo $name; ?>").password_strength({container: $("#<?php echo $name; ?>_strength_container")});
        });
    </script>
<?php } ?>