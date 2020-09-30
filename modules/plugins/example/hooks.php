<?php
// This file was included by the main plugin file (example.php) to separate the hooks code.

// Add Hooks
$PMDR->get('Plugins')->add_hook('admin_index', 'displayAdminAlert', 10);
$PMDR->get('Plugins')->add_hook('user_index_authenticated', 'addAuthenticatedMessage', 10);
$PMDR->get('Plugins')->add_hook('defaults_setup', 'defaultsSetupExampleHook', 10);
$PMDR->get('Plugins')->add_hook('template_setup_begin', 'browseCategoriesCount', 10);


// This function is called on all pages, during the template setup. (includes/template_setup.php)
// But, due to the on_page function, it only runs on the browse_categories.php page.
function browseCategoriesCount() {
	global $PMDR, $template_content;

	// Make sure we're on a Browse Categories page
	if (!on_page('browse_categories.php'))
    	return;

	// Check Config Option
	if (!$PMDR->getConfig('plugin_example_category_count'))
    	return;

	// Access Template Variables
	$category_count = $template_content->vars['results_amount'];
	$category_title = $template_content->vars['category_title'];

	// Create "listings" string.
	$s_listings = ' listing' . ($category_count == 1 ? '' : 's');

	// Override Current Title
	$template_content->set('category_title', $category_title . ' (' . $category_count . $s_listings.')');
}


// Defaults Setup is also run on every page. Including Admin pages. So use this carefully.
function defaultsSetupExampleHook() {
    global $PMDR;

	// We run a check to make sure this is not an Admin page
	// Since the admin pages do not have the #breadcrumb div, and already have a date.
	if (PMD_SECTION == 'admin')
    	return;

	// Check Config Option
	if (!$PMDR->getConfig('plugin_example_breadcrumbs_date'))
    	return;

	$s_date = $PMDR->get('Dates_Local')->dateNow('l, F jS \of Y');

	// This script will be added to public and members pages to hook into the #breadcrumb div.
	$PMDR->loadJavascript('<script type="text/javascript">
        $(function() {
			$("#breadcrumbs").prepend(\'<span>' . $s_date . ' &nbsp; </span>\');
		});</script>', 25);
}

// This function is used as a Hook on the main admin index page.
function displayAdminAlert() {
	global $PMDR;

	// Check Config Option
	if (!$PMDR->getConfig('plugin_example_admin_notice'))
    	return;

	// Create a sample alert for the Admin Summary page to warn that the plugin is enabled.
	$PMDR->loadJavascript('<script type="text/javascript">
        $(function() {
			$("body").append(\'<div id="example_dialog" title="Example Plugin Reminder">\' +
                \'<h1>REMINDER:</h1><p>You should disable the Example Plugin after you are done investigating the features.</p>\' +
				\'<br /><p>You can disable the plugin <a href="'.BASE_URL_ADMIN.'/admin_plugins.php"><b>HERE</b></a>.</p>\' +
                \'</div>\');
			$( "#example_dialog" ).dialog();
         });
	</script>', 25);
}

// Adds a custom message on the User Index page.
function addAuthenticatedMessage() {
	global $PMDR;

	// Check Config Option
	if (!$PMDR->getConfig('plugin_example_user_message'))
    	return;

	// Some Custom CSS for our custom example message.
	$PMDR->loadCSS('<style>
	    #messages div.example {
            -moz-border-radius: 5px;
			-webkit-border-radius: 5px;
			border-radius: 5px;
			margin-bottom: 10px;
			padding: 10px 5px 10px 35px;
			border-width: 5px;
			border-style: solid;
			font-size: 1.2em;
			border-color: #7caedf;
			background: #c8e2fb url(../template/default/images/icon_listing_suggest.gif) no-repeat 10px center;
		}
    </style>');

	// Add a message using a custom "example" class.
    $PMDR->addMessage('example','Welcome to '.$PMDR->getConfig('title').'! Please take a look at the <b>Advertise</b> link on the left menu.');
}

?>