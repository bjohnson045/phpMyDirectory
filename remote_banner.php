<?php
// Serve banner to remote HTML call
// <script type="text/javascript" src="http://www.domain.com/remote_banner.php?type=1"></script>
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->get('Plugins')->run_hook('remote_banner');

// Set the header as javascript
header("content-type: application/x-javascript");

// Output the banner code in a document.write statment so the banner code gets written to the page
echo "document.write('".Strings::strip_new_lines($PMDR->get('Banner_Display')->getBanner($_GET['type'],true))."');";
exit();
?>