<?php
define('PMD_SECTION', 'public');

include ('./defaults.php');

$PMDR->loadLanguage(array('public_sitemap'));

$PMDR->set('canonical_url',BASE_URL.'/sitemap.php');

$menu = $PMDR->get('CustomLinks');

$PMDR->setAdd('page_title',$PMDR->getLanguage('public_sitemap'));
$PMDR->set('meta_title',coalesce($PMDR->getConfig('sitemap_meta_title'),$PMDR->getLanguage('public_sitemap')));
$PMDR->set('meta_description',coalesce($PMDR->getConfig('sitemap_meta_description'),$PMDR->getLanguage('public_sitemap')));

$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.'/sitemap.php','text'=>$PMDR->getLanguage('public_sitemap')));

$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/dynatree/skin/ui.dynatree.css" />',20);
$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/dynatree/jquery.dynatree.min.js"></script>',20);

if(!isset($_GET['id']) OR !isset($_GET['letter']) OR !isset($_GET['type'])) {
    if(!$content = $PMDR->get('Cache')->get('categories_sitemap', 1800)) {
        $categories = $PMDR->get('Categories')->getSitemap();
        $categories_count = count($categories);
        // Loop through categories to get rid of hidden and count = 0
        for($x=0; $x < $categories_count; $x++) {
            $categories[$x]['url'] = $PMDR->get('Categories')->getURL($categories[$x]['id'],$categories[$x]['friendly_url_path']);
            $categories[$x]['children'] = !$PMDR->get('Categories')->isLeaf($categories[$x]);
            if($categories[$x]['hidden'] OR ($PMDR->getConfig('cat_empty_hidden') AND $categories[$x]['count_total'] == 0)) {
                for($y=$x+1; $y < $categories_count; $y++) {
                    if($categories[$y]['level'] <= $categories[$x]['level']) {
                        break;
                    } else {
                        unset($categories[$y]);
                    }
                }
                unset($categories[$x]);
                $x = $y-1;
            }
        }
        $content = $PMDR->get('HTML')->toList($categories,'children','folder','url','sitemap_category_');
        $PMDR->get('Cache')->write('categories_sitemap',$content);
    }

    if(!$content2 = $PMDR->get('Cache')->get('locations_sitemap', 1800)) {
        $locations = $db->GetAll("SELECT id, title, level, hidden, count_total, left_, right_, friendly_url_path FROM ".T_LOCATIONS." WHERE id!=1 ORDER BY left_");
        $locations_count = count($locations);
        // Loop through locations to get rid of hidden and count = 0
        for($x=0; $x < $locations_count; $x++) {
            $locations[$x]['url'] = $PMDR->get('Locations')->getURL($locations[$x]['id'],$locations[$x]['friendly_url_path']);
            $locations[$x]['children'] = !$PMDR->get('Locations')->isLeaf($locations[$x]);
            if($locations[$x]['hidden'] OR ($PMDR->getConfig('loc_empty_hidden') AND $locations[$x]['count_total'] == 0)) {
                for($y=$x+1; $y < $locations_count; $y++) {
                    if($locations[$y]['level'] <= $locations[$x]['level']) {
                        break;
                    } else {
                        unset($locations[$y]);
                    }
                }
                unset($locations[$x]);
                $x = $y-1;
            }
        }
        $content2 = $PMDR->get('HTML')->toList($locations,'children','folder','url','sitemap_location_');
        $PMDR->get('Cache')->write('locations_sitemap',$content2);
    }
} else {
    if($_GET['type'] == 'locations') {
        $datasource = $PMDR->get('Locations');
        $variable_name = 'content2';
    } else {
        $datasource = $PMDR->get('Categories');
        $variable_name = 'content';
    }
    if(empty($_GET['id'])) {
        $_GET['id'] = 1;
    }
    $title_field = $PMDR->get('Languages')->getFieldName('title');
    if($_GET['letter'] == "0-9") {
        $sitemap_cats = $datasource->getChildren(intval($_GET['id']),1,null,"AND title REGEXP '^[[:digit:]].*$'",array('id','level','friendly_url_path','title'));
    } else {
        $sitemap_cats = $datasource->getChildren(intval($_GET['id']),1,null,"AND title LIKE ".$PMDR->get('Cleaner')->clean_db($_GET['letter']."%"),array('id','level','friendly_url_path','title'));
    }
    foreach($sitemap_cats as &$category) {
        $category['url'] = $datasource->getURL($category['id'],$category['friendly_url_path']);
        $category['children'] = !$datasource->isLeaf($category);
    }
    ${$variable_name} = $PMDR->get('HTML')->toList($sitemap_cats,'children','folder','url');
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'sitemap.tpl');
$template_content->set('sitemap_categories',$content);
$template_content->set('sitemap_locations',$content2);
$template_content->set('links',$PMDR->get('HTML')->toList($menu->getLinksFlat(1),'children','','url'));

include(PMDROOT.'/includes/template_setup.php');
?>