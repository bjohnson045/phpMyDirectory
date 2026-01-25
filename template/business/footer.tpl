</div>
<div id="footer" class="well">
    <div class="container-fluid text-center">
        <?php echo $this->block('options'); ?>
    </div>
</div>
<?php if(!$disable_cron) { ?>
<noscript>
    <img src="<?php echo BASE_URL; ?>/cron.php?type=image" alt="" />
</noscript>
<?php } ?>
</body>
</html>