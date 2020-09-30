<script type="text/javascript">
$(document).ready(function() {
    var exportOnComplete = function(data) {
        $("#status_percent").html(data.percent+"%");
        if(data.percent == 100) {
            setTimeout(function() {
                hideLoadingMessage();
                window.location.replace("<?php echo BASE_URL_ADMIN; ?>/admin_transactions.php?action=download");
            },1000);
        } else {
            exportStart(data.start+data.num,data.num,data.year);
        }
    };

    var exportStart = function(start,num,year) {
        if(start == 0) {
            showLoadingMessage('<?php echo $lang['admin_transactions_export']; ?>');
            $("#status_percent").html("0%");
        }
        $.ajax({ data: ({ action: "admin_transactions_export", start: start, num: num, year: year }), success: exportOnComplete, dataType: "json"});
    };

    $("#transactions_export").click(function(e) {
        if( $('#export_transactions_options').length) {
            e.preventDefault();
            $('#export_transactions_options').modal();
        }
    });

    <?php if(value($_GET,'action') == 'export') { ?>
        $('#export_transactions_options').modal();
    <?php } ?>

    $("#export_start").click(function(e) {
        e.preventDefault();
        $('#export_transactions_options').modal('hide');
        exportStart(0,50,$("#year").val());
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
<?php if(isset($form_export)) { ?>
    <div id="export_transactions_options" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title"><?php echo $lang['admin_transactions_export']; ?></h4>
                </div>
                <?php echo $form_export->getFormOpenHTML(array('class'=>'form')); ?>
                <div class="modal-body">
                    <div class="control-group">
                        <?php echo $form_export->getFieldLabel('year'); ?>
                        <div class="controls">
                            <?php echo $form_export->getFieldHTML('year'); ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-default" data-dismiss="modal" aria-hidden="true">Close</button>
                    <?php echo $form_export->getFieldHTML('export_start'); ?>
                </div>
                <input type="hidden" name="action" value="export">
                <?php echo $form_export->getFormCloseHTML(); ?>
            </div>
        </div>
    </div>
<?php } ?>