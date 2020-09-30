<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage();

$PMDR->get('Authentication')->authenticate();

if(!isset($_GET['submenu'])) {
    $menu_item = $PMDR->get('Plugins')->admin_menu[$_GET['id']]['menu'];
} else {
    $menu_item = $PMDR->get('Plugins')->admin_menu[$_GET['id']]['submenu'][$_GET['submenu']];
}
ob_start();
if(function_exists($menu_item['target'])) {
    $menu_item['target']();
} elseif(strstr($menu_item['target'],'.php')) {
    include(PLUGINS_PATH.$_GET['id'].'/'.$menu_item['target']);
}
$content = ob_get_contents();
ob_end_clean();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
$template_content->set('title',$menu_item['page_title']);
$template_content->set('content', $content);

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_generic_menu.tpl');
$links = array();
if(is_array($PMDR->get('Plugins')->admin_menu[$_GET['id']]['submenu'])) {
    foreach($PMDR->get('Plugins')->admin_menu[$_GET['id']]['submenu'] AS $key=>$link) {
        $links[$key]['url'] = BASE_URL_ADMIN.'/admin_plugin_page.php?id='.$_GET['id'].'&submenu='.$key;
        $links[$key]['text'] = $link['menu_text'];
    }
}
$template_page_menu->set('links',$links);

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>