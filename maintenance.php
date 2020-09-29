<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

if(!$PMDR->getConfig('maintenance') OR (isset($_SESSION['admin_permissions']) AND in_array('admin_login',$_SESSION['admin_permissions']))) {
    redirect(BASE_URL.'/index.php');
}

header("HTTP/1.1 503 Service Unavailable");
header("Retry-After: 3600");

$PMDR->loadLanguage(array('public_maintenance'));
$PMDR->setAdd('page_title',$PMDR->getLanguage('public_maintenance'));
$PMDR->set('header_file','maintenance_header.tpl');
$PMDR->set('footer_file','maintenance_footer.tpl');
$PMDR->set('wrapper_file','wrapper_blank.tpl');

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/maintenance.tpl');

include(PMDROOT.'/includes/template_setup.php');
?>