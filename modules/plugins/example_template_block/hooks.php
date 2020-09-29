<?php
// This file was included by the main plugin file (example_template_block.php) to separate the hooks code.

// Add Hooks
$PMDR->get('Plugins')->add_hook('template_block', 'exampleTemplateBlock', 1);

// Defaults Setup is also run on every page. Including Admin pages. So use this carefully.
function exampleTemplateBlock() {
    global $PMDR;
    include(dirname(__FILE__).'/example_block.php');
}
?>