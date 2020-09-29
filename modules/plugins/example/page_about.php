<?php

echo '<h2>This Page</h2>';

echo '<p>This page is an example of using a file for a plugin (with php markup) instead of a function.</p><br />';

// PHP Special Variable
$page_name = substr(__FILE__,strpos(__FILE__, 'plugins'));

echo '<p><b>File Name:</b> ' . $page_name . '</p>';

?>
<p>Plugin pages can be created by functions, or as independent files.</p>

<p>Independent files can include html markup, as well as php markup.</p>

<br />