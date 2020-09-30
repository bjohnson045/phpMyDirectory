<?php
define('PMD_SECTION', 'public');

include('./defaults.php');

$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.css" />',100);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/moment.js"></script>',100);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.min.js"></script>',100);

$PMDR->loadLanguage(array('public_events'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_events_events_calendar'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/events_calendar.tpl');
$template_content->set('years',range(date('Y')-5,date('Y')+5));
$months = array();
for($x = 1; $x <= 12; $x++) {
    $months[] = date('F', mktime(0, 0, 0, $x, 1));
}
$template_content->set('months',$months);

include(PMDROOT.'/includes/template_setup.php');
?>