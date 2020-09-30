<script type="text/javascript">
$(document).ready(function(){
    $("#<?php echo $id; ?>").one("focus",function() {
        $("#<?php echo $id; ?>").qtip({
            show: {
                ready: false,
                when: false,
                effect: {
                    type: "fade"
                }
            },
            hide: {
                when: "mouseout",
                fixed: true
            },
            style: {
                border: {
                    width: 5,
                    color: "#C5DCEC"
                },
                name: "light",
                tip: {
                    corner: "topLeft",
                    size: { x: 20, y: 8 }
                }
            },
            position: {
                corner: {
                    target: "bottomLeft",
                    toolTip: "leftTop"
                }
            },
            api: {
                onContentUpdate: function() {
                    this.elements.content.find('a').click(function(){
                        $("#<?php echo $id; ?>").val($(this).html());
                        $("#<?php echo $id; ?>").qtip("hide");
                        return false;
                    });
                }
            }
        });
    });
    $("#<?php echo $id; ?>").keyup(function () {
        var <?php echo $id; ?>_length = $("#<?php echo $id; ?>").val().length;
        if(<?php echo $id; ?>_length > 2) {
            $.ajax({ data: ({ action: "<?php echo $data; ?>", value: $("#<?php echo $id; ?>").val()}), success:
                function(data) {
                    $("#<?php echo $id; ?>").qtip("api").updateContent(data);
                    $("#<?php echo $id; ?>").qtip("api").updateWidth(263);
                    $("#<?php echo $id; ?>").qtip("show");
                }
            });
        }
    });
});
</script>
<input autocomplete="off" type="text" class="form-control <?php echo $class; ?>" value="<?php echo $this->escape($value); ?>"<?php echo $attributes; ?> />