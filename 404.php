<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadLanguage(array('public_404'));

$PMDR->set('page_header',null);

$PMDR->get('Error',404);
?>