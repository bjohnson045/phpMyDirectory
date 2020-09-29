<input class="form-control <?php echo $class; ?>" type="<?php if($plaintext) { ?>text<?php } else { ?>password<?php } ?>" value="<?php echo $value; ?>" autocomplete="off"<?php echo $attributes; ?>>
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
    <a id="<?php echo $name; ?>_generate_password" target="_blank" class="btn btn-small"><?php echo $lang['generate']; ?></a>
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