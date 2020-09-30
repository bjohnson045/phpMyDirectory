<?php
/*
Plugin Name: Yelp Reviews
Plugin URL: http://www.phpmydirectory.com
Description: Display Yelp reviews for a listing based on phone number.
Version: 1.0
Author: phpMyDirectory
Author URL: http://www.phpmydirectory.com
Compatibility: 1.5.1
*/

require('hooks.php');
require('class_yelp.php');

function yelp_reviews_upgrade($variables) {
    $old_version = $variables['old_version'];
    $new_version = $variables['new_version'];
    return true;
}

$PMDR->get('Plugins')->add_admin_menu('yelp_reviews','Yelp Reviews','Yelp Reviews','page_options.php');
?>