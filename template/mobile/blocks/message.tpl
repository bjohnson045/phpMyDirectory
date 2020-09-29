<?php if(count($message_types) > 0) { ?>
    <script type="text/javascript">
    $(document).ready(function(){
        $("#messages").find("div").each(function (i) {
              $(this).click(function() {
                $(this).fadeOut("slow");
              });
        });
    });
    </script>
    <div id="messages">
    <?php foreach($message_types AS $type=>$messages) { ?>
        <div class="<?php echo $type; ?>">
            <?php foreach($messages AS $message) { ?>
                <?php echo $message; ?><br />
            <?php } ?>
        </div>
    <?php } ?>
    </div>
<?php } ?>