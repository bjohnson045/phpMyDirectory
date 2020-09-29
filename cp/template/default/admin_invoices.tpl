<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <h2><?php echo $title ?></h2>
<?php } else { ?>
    <h1><?php echo $title ?></h1>
<?php } ?>
<?php if($form_search) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
        <?php if($_GET['action'] == 'search') { ?>
            $("#invoice_search_container").slideToggle();
        <?php } ?>
        $("#invoice_search_container .close").click(function() {
            $("#invoice_search_container").slideToggle();
            return false;
        });
        $("#invoice_search_link").click(function() {
            $("#invoice_search_container").slideToggle();
            return false;
        });
    });
    </script>
    <div id="invoice_search_container" class="panel panel-default" style="display: none; margin-top: 20px;">
        <div class="panel-heading"><?php echo $lang['admin_invoices_search']; ?><button type="button" class="close">×</button></div>
        <div class="panel-body">
            <?php echo $form_search->getFormOpenHTML(array('class'=>'form-horizontal')); ?>
            <div class="row">
                <div class="col-md-11">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('id'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('id'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('status'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('status'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('total'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('total'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-4 col-lg-10">
                            <?php echo $form_search->getFieldHTML('submit'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-11">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('date'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('date'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('date_due'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('date_due'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('gateway_id'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('gateway_id'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $form_search->getFormCloseHTML(); ?>
        </div>
    </div>
<?php } ?>
<?php echo $content; ?>