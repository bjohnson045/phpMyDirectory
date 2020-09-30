<?php
$PMDR->get('Plugins')->add_hook('template_setup_begin', 'eventsCalendarTemplateBlock_init', 1);
$PMDR->get('Plugins')->add_hook('template_block', 'eventsCalendarTemplateBlock', 1);

function eventsCalendarTemplateBlock_init() {
    global $PMDR;
    $PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.css" />',100);
    $PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/moment.js"></script>',100);
    $PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/fullcalendar/fullcalendar.min.js"></script>',100);
}

function eventsCalendarTemplateBlock() {
    global $PMDR;
    include(dirname(__FILE__).'/events_calendar_block.php');
}
?>