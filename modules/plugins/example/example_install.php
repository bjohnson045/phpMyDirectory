<?php
// This file can be used to execute any SQL queries or any code to be executed when a plugin is "installed".

// In case something strange has happened and the plugin uninstall was never run
// We will first delete the plugin_example settings and then, recreate them.
$db->Execute("DELETE FROM ".T_SETTINGS." WHERE `grouptitle` = 'plugin_example';");

// Add some custom settings that will be used by our plugin.
// Create a grouptitle unique to our plugin (plugin_example)
$db->Execute("
INSERT INTO ".T_SETTINGS." (varname, grouptitle, value, optioncode_type, validationcode)
VALUES
('plugin_example_admin_notice','plugin_example',1,'checkbox',''),
('plugin_example_breadcrumbs_date','plugin_example',1,'checkbox',''),
('plugin_example_user_message','plugin_example',1,'checkbox',''),
('plugin_example_category_count','plugin_example',1,'checkbox','');");
?>