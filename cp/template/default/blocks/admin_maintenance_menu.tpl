<a class="list-group-item" href="admin_maintenance.php"><div class="icon icon_gears"></div><?php echo $lang['admin_maintenance']; ?></a>
<a class="list-group-item" href="admin_maintenance_database.php"><div class="icon icon_question"></div><?php echo $lang['admin_maintenance_db_info']; ?></a>
<a class="list-group-item" href="admin_maintenance_query.php"><div class="icon icon_arrow_green"></div><?php echo $lang['admin_maintenance_query']; ?></a>
<a class="list-group-item" href="admin_maintenance_db_find.php"><div class="icon icon_search"></div><?php echo $lang['admin_maintenance_db_find']; ?></a>
<a class="list-group-item" href="admin_backup.php"><div class="icon icon_download"></div><?php echo $lang['admin_general_menu_backup_manager']; ?></a>
<a class="list-group-item" href="admin_maintenance_email_test.php"><div class="icon icon_email"></div><?php echo $lang['admin_maintenance_email_test']; ?></a>
<a class="list-group-item" href="admin_maintenance_images.php"><div class="icon icon_image"></div><?php echo $lang['admin_maintenance_images']; ?></a>
<a class="list-group-item" href="admin_maintenance_duplicates.php"><div class="icon icon_duplicate"></div><?php echo $lang['admin_maintenance_duplicates']; ?></a>
<a class="list-group-item" href="admin_maintenance_rebuild.php"><div class="icon icon_sync"></div><?php echo $lang['admin_maintenance_recount']; ?></a>
<a class="list-group-item" href="admin_maintenance_rebuild_search_index.php"><div class="icon icon_search"></div><?php echo $lang['admin_maintenance_rebuild_search_index']; ?></a>
<a class="list-group-item" href="admin_maintenance_coordinates.php"><div class="icon icon_globe"></div>Calculate Coordinates</a>
<a class="list-group-item" href="admin_scheduled_tasks.php"><div class="icon icon_clock"></div><?php echo $lang['admin_maintenance_scheduled_tasks']; ?></a>
<a class="list-group-item" href="admin_maintenance.php?action=integrity_check"><div class="icon icon_notice"></div><?php echo $lang['admin_maintenance_integrity']; ?></a>
<a class="list-group-item" href="admin_error_log.php"><div class="icon icon_error"></div><?php echo $lang['admin_maintenance_error_log']; ?></a>
<a class="list-group-item" href="admin_maintenance_phpinfo.php"><div class="icon icon_php"></div><?php echo $lang['admin_maintenance_phpinfo']; ?></a>
<a class="list-group-item" href="admin_maintenance.php?action=clear_cache"><div class="icon icon_trash"></div><?php echo $lang['admin_maintenance_clear_cache']; ?></a>
<a class="list-group-item" onclick="if(confirm('<?php echo $this->escape_js_string($lang['messages_confirm']); ?>') ) document.location.href='admin_maintenance.php?action=reset'" href="#"><div class="icon icon_arrow_revert"></div><?php echo $lang['admin_maintenance_reset_db']; ?></a>