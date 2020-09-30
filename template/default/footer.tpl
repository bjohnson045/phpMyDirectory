</div>
<div id="footer">
    <div class="container-fluid text-center">
        <?php if(!ADDON_UNBRANDING) { ?>
            <p><a rel="nofollow" href="http://www.phpmydirectory.com/"><?php echo $lang['powered_by']; ?> phpMyDirectory</a> (v. <?php echo $pmd_version; ?>)</p>
        <?php } ?>
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