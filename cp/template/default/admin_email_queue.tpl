<script type="text/javascript">
$(document).ready(function(){
    $('a[id^="email_queue_message_link"]').click(function() {
        var dialog = $('<div style="display:none"></div>').appendTo('body');
        var email_id = $(this).attr('id').match(/[0-9]+/g)[0];
        $.ajax({
            data:({
                action:'admin_email_queue_view',
                id: email_id
            }),
            success:function(data) {
                dialog.html(data);
                dialog.dialog({
                    title: '<?php echo $lang['admin_email_log_view_message']; ?>',
                    width: 750,
                    height: 550,
                    modal: true,
                    resizable: false,
                    autoOpen: false,
                    buttons: {
                        "Approve": function() {
                            window.location.href = $("#email_queue_approve_link"+email_id).attr('href');
                            $(this).dialog("close");
                            dialog.remove();
                        },
                        "Close": function() {
                            $(this).dialog("close");
                            dialog.remove();
                        }
                    },
                });
                if($("#email_queue_approve_link"+email_id).length == 0) {
                    dialog.dialog("option","buttons",{"Close": function() { $(this).dialog("close"); dialog.remove(); }});
                }
                dialog.dialog("open");
            }
        });
        return false;
    });
});
</script>
<h1><?php echo $lang['admin_email_queue']; ?></h1>
<div class="row">
    <div class="col-lg-12">
        <div class="btn-toolbar">
            <div class="btn-group">
                <a class="btn btn-default<?php if(!isset($_GET['action'])) { ?> active<?php } ?>" href="<?php echo URL_NOQUERY; ?>"><i class="glyphicon glyphicon-envelope"></i> <?php echo $lang['admin_email_queue_all'] ;?></a>
                <a class="btn btn-default<?php if($_GET['action'] == 'moderated') { ?> active<?php } ?>" href="<?php echo URL_NOQUERY; ?>?action=moderated"><i class="glyphicon glyphicon-flag"></i> <?php echo $lang['admin_email_queue_moderated_emails'] ;?></a>
            </div>
            <div class="btn-group">
                <a class="btn btn-warning" href="<?php echo URL_NOQUERY; ?>?action=empty"><i class="glyphicon glyphicon-trash"></i> <?php echo $lang['admin_email_queue_empty']; ?></a>
            </div>
            <div  class="btn-group">
                <?php echo $form->getFormOpenHTML(array('class'=>'form-inline')); ?>
                    <div class="form-group">
                        <div class="controls">
                            <div class="input-group">
                                <?php echo $form->getFieldHTML('process_number',array('placeholder'=>'Number to process')); ?>
                                <span class="input-group-btn"><?php echo $form->getFieldHTML('send'); ?></span>
                            </div>
                        </div>
                    </div>
                <?php echo $form->getFormCloseHTML(); ?>
            </div>
        </div>
    </div>
</div>
<?php echo $table_list; ?>