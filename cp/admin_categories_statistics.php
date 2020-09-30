<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_categories'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_categories_view');

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

if($_GET['action'] == 'download') {
    $PMDR->get('ServeFile')->serve(
        $PMDR->get('Categories')->exportStatistics(
            $PMDR->getLanguage('admin_categories_id'),
            $PMDR->getLanguage('admin_categories_title'),
            $PMDR->getLanguage('admin_categories_impressions'),
            $PMDR->getLanguage('admin_categories_impressions_search')
        )
    );
    exit();
}

if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_categories_statistics.tpl');
    $template_content->set('impressions',$PMDR->get('Categories')->getImpressions());
    $template_content->set('impressions_search',$PMDR->get('Categories')->getSearchImpressions());
    $template_content->set('counts',$PMDR->get('Categories')->getStatisticsCounts());
    $template_content->set('title',$PMDR->getLanguage('admin_categories_statistics'));
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_categories_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>