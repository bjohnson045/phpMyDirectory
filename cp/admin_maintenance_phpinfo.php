<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_maintenance'));

$PMDR->get('Authentication')->checkPermission('admin_maintenance_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$template_content->set('title',$PMDR->getLanguage('admin_maintenance_phpinfo'));

if(DEMO_MODE) {
    $PMDR->addMessage('error','PHP information is disabled in the demo.');
} else {
    ob_start();
    if(phpinfo()) {
        $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', ob_get_contents()) ;
    } else {
        $phpinfo = false;
    }
    ob_end_clean();
    if(!$phpinfo) {
        $phpinfo = 'Unable to obtain PHP information.  phpinfo() function may be disabled or restricted in php configuration';
    }

    $template_content->set('content',$phpinfo);
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_maintenance_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>