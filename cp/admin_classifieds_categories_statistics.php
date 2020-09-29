<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_classifieds'));

$PMDR->get('Authentication')->authenticate();

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

if($_GET['action'] == 'download') {
    $PMDR->get('ServeFile')->serve(
        $PMDR->get('Classifieds_Categories')->exportStatistics(
            $PMDR->getLanguage('admin_categories_id'),
            $PMDR->getLanguage('admin_categories_title'),
            $PMDR->getLanguage('admin_categories_impressions'),
            $PMDR->getLanguage('admin_categories_impressions_search')
        )
    );
    exit();
}

if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_classifieds_categories_statistics.tpl');
    $template_content->set('impressions',$PMDR->get('Classifieds_Categories')->getImpressions());
    $template_content->set('impressions_search',$PMDR->get('Classifieds_Categories')->getSearchImpressions());
    $template_content->set('counts',$PMDR->get('Classifieds_Categories')->getStatisticsCounts());
    $template_content->set('title',$PMDR->getLanguage('admin_classifieds_categories_statistics'));
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_classifieds_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>