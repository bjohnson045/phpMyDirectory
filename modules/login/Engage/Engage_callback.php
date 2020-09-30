<?php
define('PMD_SECTION','members');

include('../../../defaults.php');

$PMDR->get('Authentication')->authenticate(array('redirect'=>BASE_URL.MEMBERS_FOLDER.'index.php'));

redirect();
?>