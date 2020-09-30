<?php
/*
Plugin Name: Example Plugin
Plugin URL: http://www.phpmydirectory.com
Description: This is an example plugin included with phpMyDirectory to demonstrate how plugins work.
Version: 2.0
Author: phpMyDirectory
Author URL: http://www.phpmydirectory.com
Compatibility: 1.5.0
*/

// Other files can be included
// hooks.php and menus.php are included here as examples.  These files are not required.
require('hooks.php');
require('menus.php');

// This function gets run whenever a new version of the plug in is uploaded where there is a higher version number
function example_upgrade($variables) {
    $old_version = $variables['old_version'];
    $new_version = $variables['new_version'];

    // We can run some code here to update the database if needed.
    // We don't need to increment the version number as this will be done automatically

    // We should return false if there is some problem, so the version number won't increment
    // return false;
    return true;
}
?>