<h1><?php echo $title; ?></h1>
<script type="text/javascript">
$(document).ready(function() {
    $("#change_marketer").click(function(e) {
        e.preventDefault();
        $("#current_marketer").hide();
        $("#change_form").show();
    });
});
</script>
<?php if($current_marketer) { ?>
<div id="current_marketer">
    <div class="row">
        <div class="col-lg-14">
            <div class="panel panel-default">
                <div class="panel-heading"><?php echo $current_marketer; ?> Settings <a id="change_marketer" href="#" class="btn btn-default btn-xs pull-right">Change Service</a></div>
                <div class="panel-body">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
    <?php if($current_lists) { ?>
        <div class="row">
            <div class="col-lg-14">
                <div class="panel panel-default">
                    <div class="panel-heading">Current Lists<a href="admin_email_marketing.php?sync_lists=true" class="btn btn-default btn-xs pull-right"><span class="glyphicon glyphicon-refresh"></span> Retreive Lists</a></div>
                    <ul class="list-group">
                        <?php foreach($current_lists AS $list) { ?>
                        <li class="list-group-item"><?php echo $list; ?></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php } ?>
</div>
<?php } ?>
<div class="row" id="change_form" style="<?php if($current_marketer) { ?>display: none;<?php } ?>">
    <div class="col-lg-12">
        <?php echo $form->getFormOpenHTML(array('class'=>'form-inline')); ?>
        <?php echo $form->getFieldHTML('email_marketing'); ?><?php echo $form->getFieldHTML('submit_enable'); ?>
        <?php echo $form->getFormCloseHTML(); ?>
    </div>
</div>