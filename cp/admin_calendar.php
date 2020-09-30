<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.css" />',100);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/moment.js"></script>',100);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.min.js"></script>',100);

// Load language variables for admin_backup
$PMDR->loadLanguage(array('admin_calendar'));

// Check we are authorized (logged in)
$PMDR->get('Authentication')->authenticate();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_calendar.tpl');
$template_content->set('years',range(date('Y')-5,date('Y')+5));
$months = array();
for($x = 1; $x <= 12; $x++) {
    $months[] = date('F', mktime(0, 0, 0, $x, 1));
}
$template_content->set('months',$months);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>