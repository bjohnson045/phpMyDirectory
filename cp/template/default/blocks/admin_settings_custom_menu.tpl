<a class="list-group-item" href="./admin_settings_custom.php"><div class="icon icon_tools"></div><?php echo $lang['admin_settings_custom']; ?></a>
<a class="list-group-item" href="./admin_settings_custom.php?action=add"><div class="icon icon_tools_add"></div><?php echo $lang['admin_settings_custom_add']; ?></a>
<?php if($custom_count) { ?>
    <a class="list-group-item" href="./admin_settings.php?group=custom"><div class="icon icon_form"></div><?php echo $lang['admin_settings_custom_view']; ?></a>
<?php } ?>