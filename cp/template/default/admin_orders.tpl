<?php if($users_summary_header) { ?>
    <?php echo $users_summary_header; ?>
    <?php if($listing_header) { ?>
        <?php echo $listing_header; ?>
    <?php } else { ?>
        <h2><?php echo $lang['admin_orders']; ?></h2>
    <?php } ?>
<?php } else { ?>
    <h1><?php echo $lang['admin_orders']; ?></h1>
<?php } ?>
<?php if($form_search) { ?>
    <script type="text/javascript">
    $(document).ready(function() {
        <?php if($_GET['action'] == 'search') { ?>
            $("#order_search_container").slideToggle();
        <?php } ?>
        $("#order_search_container .close").click(function() {
            $("#order_search_container").slideToggle();
            return false;
        });
        $("#order_search_link").click(function() {
            $("#order_search_container").slideToggle();
            return false;
        });
    });
    </script>
    <div id="order_search_container" class="panel panel-default" style="display: none; margin-top: 20px;">
        <div class="panel-heading"><?php echo $lang['admin_orders_search']; ?><button type="button" class="close">×</button></div>
        <div class="panel-body">
            <?php echo $form_search->getFormOpenHTML(array('class'=>'form-horizontal')); ?>
            <div class="row">
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('id'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('id'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('order_id'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('order_id'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('pricing_ids'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('pricing_ids'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-offset-4 col-lg-10">
                            <?php echo $form_search->getFieldHTML('submit'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('status'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('status'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('date'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('date'); ?>
                        </div>
                    </div>
                    <div class="form-group">
                        <?php echo $form_search->getFieldLabel('user_id'); ?>
                        <div class="col-lg-18">
                            <?php echo $form_search->getFieldHTML('user_id'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php echo $form_search->getFormCloseHTML(); ?>
        </div>
    </div>
<?php } ?>
<?php echo $content; ?>