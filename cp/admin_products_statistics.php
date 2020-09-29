<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_products','admin_products_pricing'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_products_view');

$PMDR->loadJavascript('<script type="text/javascript" src="https://www.google.com/jsapi"></script>');

if(!isset($_GET['action'])) {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_products_statistics.tpl');
    $impressions = $db->GetAll("SELECT i.*, p.name FROM (SELECT o.pricing_id, SUM(impressions) AS impressions FROM ".T_LISTINGS." l INNER JOIN ".T_ORDERS." o ON l.id=o.type_id AND o.type='listing_membership' GROUP BY o.pricing_id) AS i INNER JOIN ".T_PRODUCTS_PRICING." pp ON i.pricing_id=pp.id INNER JOIN ".T_PRODUCTS." p ON pp.product_id=p.id");
    $template_content->set('impressions',$impressions);
    $counts = $db->GetAll("SELECT p.name, c.count FROM (SELECT pp.id AS pricing_id, COUNT(o.id) AS count FROM ".T_ORDERS." o INNER JOIN ".T_PRODUCTS_PRICING." pp ON o.pricing_id=pp.id AND o.status='active' GROUP BY o.pricing_id) AS c INNER JOIN ".T_PRODUCTS." p ON p.id=c.pricing_id");
    $template_content->set('counts',$counts);
    $template_content->set('title',$PMDR->getLanguage('admin_products_statistics'));
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_products_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>