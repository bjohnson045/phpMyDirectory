<?php if(isset($latest_version)) { ?>
    <div class="alert alert-success" style="text-align: center">
        <h4>Latest Version Detected</h4>
        phpMyDirectory is up to date and an upgrade is not required.<br />
        Please make sure the /install/ folder is removed.
    </div>
<?php } elseif(isset($upgrade_php)) { ?>
    <div class="alert alert-danger" style="text-align: center">
        <h4>PHP 5.6+ Required</h4>
        phpMyDirectory requires PHP 5.6+ or above.<br />
        Please upgrade the server to PHP 5.6+ or contact your web host to install PHP 5.6+.
    </div>
<?php } elseif(isset($upgrade_ioncube)) { ?>
    <div class="alert alert-danger" style="text-align: center">
        <h4>ionCube 5.0+ Required</h4>
        phpMyDirectory requires ionCube version 5.0+ to be installed.<br />
        Please upgrade the ionCube version on the server to 5.0+.  Contact your web host or phpMyDirectory support for additional assistance.
    </div>
<?php } elseif(isset($cache)) { ?>
    <div class="alert alert-warning" style="text-align: center">
        <h4>Cache Not Writable</h4>
        The /cache/ folder needs to be writable.<br />
        This usually requires the permissions on the folder being set to 755 or 777 depending on your server.<br />
        After changing the permissions, please refresh this page.
    </div>
<?php } else { ?>
    <h3>Upgrade</h3>
    <p>Welcome to the phpMyDirectory <?php echo $upgrade_version; ?> upgrade process.  Please log in.</p>
    <div class="alert alert-warning">
        <h4>Important!</h4>
        Please make sure you have made a backup of your database before proceeding.
    </div>
    <?php echo $content; ?>
<?php } ?>

