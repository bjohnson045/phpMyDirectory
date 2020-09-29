<?php
// This file can be used to execute any SQL queries or any code to be executed when a plugin is "uninstalled".

// Delete our custom settings. (Group: plugin_example)
$db->Execute("DELETE FROM ".T_SETTINGS." WHERE `grouptitle` = 'plugin_example';");
?>