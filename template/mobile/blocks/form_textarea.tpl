<textarea class="textarea <?php echo $class; ?>" cols="10" rows="10"<?php echo $attributes; ?>>
<?php echo $value; ?>
</textarea>
<?php if($fullscreen) { ?>
    <p class="note">
        <a id="<?php echo $id; ?>_fullscreen_link" href="#">Fullscreen</a>
    </p>
    <div id="<?php echo $id; ?>_fullscreen">
        <textarea style="width: 100%; height: 95%;<?php if($spellcheck) { ?> spellcheck="false"<?php } ?>">
            <?php echo $value; ?>
        </textarea>
    </div>
    <script type="text/javascript">
    $(document).ready(function() {
        $("#<?php echo $id; ?>_fullscreen textarea").css("font-family",$("#<?php echo $id; ?>").css("font-family"));
        $("#<?php echo $id; ?>_fullscreen").dialog({
             close: function(event, ui) {
                $("#<?php echo $id; ?>").val($("#<?php echo $id; ?>_fullscreen textarea").val());
             },
             buttons: {
                "Close": function() { $(this).dialog("close"); }
             },
             width: $(window).width()-20,
             height: $(window).height()-20,
             zIndex: 10000,
             autoOpen: false,
             modal: true,
             title: "<?php echo $label; ?>"
        });
        $("#<?php echo $id; ?>_fullscreen_link").click(function() {
            $("#<?php echo $id; ?>_fullscreen").dialog("option", "width", $(window).width()-20);
            $("#<?php echo $id; ?>_fullscreen").dialog("option", "height", $(window).height()-20);
            $("#<?php echo $id; ?>_fullscreen").dialog("open");
            return false;
        });
    });
    </script>
<?php } ?>