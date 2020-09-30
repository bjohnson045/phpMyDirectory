<h1><?php echo $lang['admin_maintenance_db_info']; ?></h1>
<p><?php echo $lang['admin_maintenance_db_name']; ?>: <?php echo DB_NAME; ?></p>
<p><?php echo $lang['admin_maintenance_db_version']; ?>: <?php echo $mysql_version; ?></p>
<p>
<a class="btn btn-default" href="#" id="check_status">Check Table Statuses</a>
<?php if($sync_database) { ?>
    <a class="btn btn-default" href="admin_maintenance_database.php?action=sync">Sync Database</a>
<?php } ?>
</p>
<p><?php echo $lang['admin_maintenance_legend']; ?>: <span class="icon icon_gears"></span><?php echo $lang['admin_maintenance_optimize']; ?> <span class="icon icon_tools"></span><?php echo $lang['admin_maintenance_repair']; ?></p>
<?php echo $table_list; ?>
<?php echo $javascript; ?>