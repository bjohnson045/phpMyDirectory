<?php
// This file was included by the main plugin file (example.php) to separate the Admin Menus code.

// Used for the content of the menu links below
function example_admin_page() {
    echo '<p>This is the admin page that the above menu option will display.</p>' .
	    '<p>There are some <b>settings on the Options page</b> to enable and disable various examples in this plugin.</p>';
}

// This adds a menu link to Administrator->Plugins.  When clicked it displays a page with title "Example Page Title".
// The function 'example_admin_page' will be executed and any output will be displayed as the page content
$PMDR->get('Plugins')->add_admin_menu('example','Example Page Title','Example Plugin','example_admin_page');

// This adds a menu link to the side menu when one of the plugin pages is being displayed.
// When clicked it displays a page with title "Example Page Title".
// The function 'example_admin_page' will be executed and any output will be displayed as the page content
$PMDR->get('Plugins')->add_admin_submenu('example','Example Page Title','Example Plugin','example_admin_page');

// This is an example of a few more side menu links being added
$PMDR->get('Plugins')->add_admin_submenu('example','About','About this Plugin','page_about.php');
$PMDR->get('Plugins')->add_admin_submenu('example','Options','Options','page_options.php');
