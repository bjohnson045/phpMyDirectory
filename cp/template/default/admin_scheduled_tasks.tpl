<?php if($cron) { ?>
<script type="text/javascript">
$(document).ready(function(){
    $.ajax({url: '<?php echo BASE_URL; ?>/cron.php?type=javascript', type: 'GET', async: false, cache: false, timeout: 30000, error: function() { return true; }, success: function() { return true; }});
});
</script>
<?php } ?>
<h1><?php echo $lang['admin_scheduled_tasks']; ?></h1>
<div class="alert alert-info">
    <h4>Suggested server CRON job:</h4>
    <p><?php if(defined('PHP_BINARY')) { echo PHP_BINARY; } else { echo PHP_BINDIR.'/php'; } ?> -q <?php echo PMDROOT;?>/cron.php <?php echo md5(SECURITY_KEY); ?></p>
</div>
<a class="btn btn-info" href="admin_scheduled_tasks.php?run=true"><i class="glyphicon glyphicon-time"></i> <?php echo $lang['admin_scheduled_tasks_run']; ?></a><br /><br />
<?php echo $content; ?>