<script type="text/javascript">
$(document).ready(function(){
    $('a[id^="email_log_message_link"]').click(function() {
        var dialog = $('<div style="display:none"></div>').appendTo('body');
        $.ajax({
            data:({
                action:'admin_email_log_view',
                id:$(this).attr('id')
            }),
            success:function(data) {
                dialog.html(data);
                dialog.dialog({
                    title: '<?php echo $lang['admin_email_log_view_message']; ?>',
                    width: 650,
                    height: 450,
                    modal: true,
                    resizable: false,
                    buttons: {
                        "Close": function() {
                            $(this).dialog("close");
                            dialog.remove();
                        }
                    },
                });
            }
        });
        return false;
    });
});
</script>
<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <h2><?php echo $title ?></h2>
<?php } else { ?>
    <h1><?php echo $title ?></h1>
<?php } ?>
<?php echo $content; ?>