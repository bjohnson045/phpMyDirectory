<?php
/*
Plugin Name: Events Calendar Template Block
Plugin URL: http://www.phpmydirectory.com
Description: Events calendar template block.
Version: 1.0
Author: phpMyDirectory
Author URL: http://www.phpmydirectory.com
Compatibility: 1.5.1
*/

require('hooks.php');

function events_calendar_template_block_upgrade($variables) {
    $old_version = $variables['old_version'];
    $new_version = $variables['new_version'];
    return true;
}
?>