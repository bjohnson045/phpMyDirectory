<?php
if(isset($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] == 'on') {
    define('SSL_ON',true);
}

include('../defaults.php');

$PMDR->loadLanguage(array('admin_general'));

// Need to add charset to all ajax responses where we don't use common_header.php
header('Content-Type: text/html; charset='.CHARSET);

$PMDR->get('Cleaner')->clean_input($_GET);

switch($_GET['type']) {
    case 'get_map':
        header("Content-type: text/html; charset=".CHARSET);
        $map = $PMDR->get('Map');
        $map->setCenterCoords('0','0');
        $map->setZoomLevel('1');
        $map->addCoordinatesSelector('latitude','longitude');
        $map->showInfoWindow = false;
        if($_GET['latitude'] != '0.0000000000' AND $_GET['longitude'] != '0.0000000000' AND !empty($_GET['longitude']) AND !empty($_GET['longitude'])) {
            $map->setZoomLevel('10');
            if(!isset($_GET['marker']) OR $_GET['marker'] != false) {
                $map->addMarkerByCoords($PMDR->get('Cleaner')->clean_output($_GET['longitude']),$PMDR->get('Cleaner')->clean_output($_GET['latitude']));
            } else {
                $map->setCenterCoords($PMDR->get('Cleaner')->clean_output($_GET['longitude']),$PMDR->get('Cleaner')->clean_output($_GET['latitude']));
            }
        } else {
            $map->setZoomLevel('1');
        }
        if(isset($_GET['zoomLevel'])) {
            $map->setZoomLevel($PMDR->get('Cleaner')->clean_output($_GET['zoomLevel']));
        }
        $html .= '<html>';
        $html .= '<head>';
        $html .= '<style>* { margin: 0; padding: 0; } #map { width: 500px; height: 350px }</style>';
        $html .= $map->getHeaderJS();
        $html .= $map->getMapJS();
        $html .= '<script type="text/javascript">';
        $html .= 'function onLoad() {';
        $html .= 'mapOnLoad();';
        $html .= '}';
        $html .= 'window.onload=onLoad;';
        $html .= '</script>';
        $html .= '</head>';
        $html .= $map->getMap();
        $html .= '</html>';
        echo $html;
        break;
    case 'category_tree':
    case 'location_tree':
    case 'classifieds_category_tree':
        header("Content-type: text/json; charset=".CHARSET);

        if($_GET['type'] == 'category_tree') {
            $tree_pointer = $PMDR->get('Categories');
            $table = T_CATEGORIES;
            $cache_prefix = 'categories';
            $setup_variable = 'category_setup';
        } elseif($_GET['type'] == 'location_tree') {
            $tree_pointer = $PMDR->get('Locations');
            $table = T_LOCATIONS;
            $cache_prefix = 'locations';
            $setup_variable = 'location_setup';
        } elseif($_GET['type'] == 'classifieds_category_tree') {
            $tree_pointer = $PMDR->get('Classifieds_Categories');
            $table = T_CLASSIFIEDS_CATEGORIES;
            $cache_prefix = 'classifieds_categories';
            $setup_variable = 'category_setup';
        } else {
            return;
        }

        if(!$tree = $PMDR->get('Cache')->get(URL,$cache_prefix)) {
            // Get the root ID
            $root_id = isset($_GET['id']) ? $_GET['id'] : 1;
            $root_id = isset($_POST['id']) ? $_POST['id'] : $root_id;
            $tree_id = ($root_id == 1) ? 0 : $root_id;
            $value = array_filter(explode(',',$_POST['value']));
            $parents = array();

            // If we want the entire tree set the number of levels to -1
            $root_node = $db->GetRow("SELECT id, left_, right_, level FROM ".$table." WHERE id=?",array($root_id));

            $where = "left_ BETWEEN '".($root_node["left_"]+1)."' AND '".$root_node["right_"]."'";

            //if($_GET['roots_only']) {
            //    $load_levels = -1;
            //    $where .= ' level = 1';
            if($_GET['load_full'] == 'true' OR $_POST['checkall'] == 'true') {
                $load_levels = -1;
                $where .= ' AND level > 0';
            } else {
                $load_levels = 1;
                $where .= ' AND (level BETWEEN '.($root_node['level']+1).' AND '.($root_node['level']+1);
                if(count($value)) {
                    $where .= ' OR id IN('.implode(',',$value).')';
                    $parents = $tree_pointer->getParentIDArray($value);
                    if(!empty($parents)) {
                        $where .= ' OR parent_id IN ('.implode(',',$parents).')';
                    }
                }
                $where .= ')';
            }

            if(!value($_GET,'hidden',true)) {
                $where .= ' AND hidden=0';
            }
            if(!value($_GET,'closed',true)) {
                $where .= ' AND closed=0';
            }

            $filter = value($_GET,'filter',false);
            if(!empty($filter)) {
                $where .= ' AND id IN('.$PMDR->get('Cleaner')->clean_output(value($_GET,'filter',false)).')';
            }

            // Get the results to display
            $results = $tree_pointer->getTreeSelect($where);

            // Set up our initial data
            $tree = array();
            $levels = array();
            $previous_result = array('children'=>&$tree);
            $current_level = $results[0]['level']-1;

            // Build our tree
            foreach($results AS $key=>$result) {
                $result['key'] = $result['id'];
                // If not a leaf set up as a folder and lazy loading
                if(!$tree_pointer->isLeaf($result)) {
                    $result['isFolder'] = true;
                    if($load_levels) {
                        $result['isLazy'] = true;
                    }

                    // Do not let them select non-leaf categories according to settings
                    if(!value($_GET,'bypass_setup') AND $PMDR->getConfig($setup_variable) == 0) {
                            $result['hideCheckbox'] = true;
                            $result['unselectable'] = true;
                        }
                    }
                // This is a work-a-around until we can get selectKey to work in the javascript
                if($_POST['checkall'] == 'true') {
                    $result['select'] = true;
                }
                if(in_array($result['key'],$parents)) {
                    $result['expand'] = true;
                }
                if(in_array($result['key'],$value)) {
                    $result['select'] = true;
                }
                unset($result['id']);

                // Build our nested set results into an multi-dimensional array
                if($result['level'] > $current_level) {
                    if(isset($levels[$result['level']])) unset($levels[$result['level']]);
                    if(!isset($previous_result['children'])) $previous_result['children'] = array();
                    $levels[$result['level']] = &$previous_result['children'];
                }
                if($result['level'] != $current_level) unset($previous_result);
                $current_level = $result['level'];
                $levels[$current_level][] = &$result;
                $previous_result = &$result;
                unset($result);
            }
            unset($levels);
            unset($previous_result);

            $PMDR->get('Cache')->write(URL,$tree,$cache_prefix);
        }
        echo json_encode($tree);
        break;
    case 'products_tree':
        header("Content-type: text/json; charset=".CHARSET);
        $value = array_filter(explode(',',$_POST['value']));
        if(isset($_GET['product_type'])) {
            $products = $PMDR->get('Products')->getProductsArray($_GET['product_type'],((isset($_GET['hidden']) AND $_GET['hidden'] == true) ? true : false),((isset($_GET['inactive']) AND $_GET['inactive'] == false) ? false : true));
        } else {
            $products = $PMDR->get('Products')->getProductsArray(null,((isset($_GET['hidden']) AND $_GET['hidden'] == true) ? true : false),((isset($_GET['only_active']) AND $_GET['only_active'] == false) ? false : true));
        }
        if(isset($_GET['pricing_ids'])) {
            $pricing_ids = array_filter(explode(',',$_GET['pricing_ids']));
            foreach($products as $group_id=>$group) {
                foreach($group['products'] AS $product_key=>$product) {
                    foreach($product['pricing'] as $price_key=>$price) {
                        if(!in_array($price['id'],$pricing_ids)) {
                            unset($products[$group_id]['products'][$product_key]['pricing'][$price_key]);
                            if(!count($products[$group_id]['products'][$product_key]['pricing'])) {
                                unset($products[$group_id]['products'][$product_key]);
                                if(!count($products[$group_id]['products'])) {
                                    unset($products[$group_id]);
                                }
                            }
                        }
                    }
                }
            }
        }
        foreach($products as $group_id=>&$group) {
            $group['key'] = $group_id;
            $group['title'] = $group['name'];
            $group['children'] = $group['products'];
            $group['isFolder'] = true;
            $group['hideCheckbox'] = true;
            $group['unselectable'] = true;
            if($_POST['checkall'] == 'true' OR $_GET['expandall'] == 'true') {
                $group['expand'] = true;
            }
            unset($group['products'],$group['name']);
            $group['children'] = array_values($group['children']);
            foreach($group['children'] AS $product_key=>&$product) {
                $product['title'] = $product['name'];
                $product['key'] = $product['product_id'];
                $product['children'] = $product['pricing'];
                unset($product['pricing'],$product['name']);
                if(isset($_GET['hide_pricing']) AND $_GET['hide_pricing'] == true) {
                    if($_POST['checkall'] == 'true' OR in_array($product['key'],$value)) {
                        $product['select'] = true;
                        $group['expand'] = true;
                    }
                    unset($product['children']);
                } else {
                    $product['isFolder'] = true;
                    $product['hideCheckbox'] = true;
                    $product['unselectable'] = true;
                    if($_POST['checkall'] == 'true' OR $_GET['expandall'] == 'true') {
                        $product['expand'] = true;
                    }
                    $product['children'] = array_values($product['children']);
                    foreach($product['children'] as $price_key=>&$price) {
                        $price['key'] = $price['id'];
                        // We remove these as dynatree considers these when rendering the tree due to the same key names
                        unset($price['active']);
                        unset($price['activate']);
                        if($_POST['checkall'] == 'true' OR in_array($price['key'],$value)) {
                            $group['expand'] = true;
                            $product['expand'] = true;
                            $price['select'] = true;
                        }
                        if(!empty($price['label'])) {
                            $price['title'] = $price['label'];
                        } else {
                            $price['title'] = $price['period_count'].' '.$PMDR->getLanguage($price['period']).'<br /><span style="font-size: 80%; vertical-align: top;">'.format_number_currency($price['price']).' - '.$PMDR->getLanguage('setup').': '.format_number_currency($price['setup_price']).'</span>';
                        }
                    }
                }
            }
        }
        echo json_encode(array_values($products));
        break;
    case 'custom_fields':
        header("Content-type: text/json; charset=".CHARSET);
        $value = array_filter(explode(',',$_POST['value']));
        if(isset($_GET['groups'])) {
            $groups = $PMDR->get('Cleaner')->clean_db($_GET['groups']);
            $groups = implode(',',$groups);
            $field_groups = $db->GetAll("SELECT g.id, g.title FROM ".T_FIELDS_GROUPS." g WHERE g.type IN($groups) ORDER BY g.ordering");
        } else {
            $field_groups = $db->GetAll("SELECT g.id, g.title FROM ".T_FIELDS_GROUPS." g WHERE g.type IN('listings','reviews','send_message','send_message_friend') ORDER BY g.ordering");
        }
        foreach($field_groups as &$group) {
            $group['key'] = $group['id'];
            $group['children'] = $db->GetAll("SELECT f.id, f.name FROM ".T_FIELDS." f WHERE group_id=? ORDER BY f.ordering ASC",array($group['id']));
            $group['isFolder'] = true;
            $group['hideCheckbox'] = true;
            $group['unselectable'] = true;
            if($_POST['checkall'] == 'true') {
                $group['expand'] = true;
            }
            $group['children'] = array_values($group['children']);
            foreach($group['children'] AS &$field) {
                $field['title'] = $field['name'];
                $field['key'] = $field['id'];
                unset($field['name']);
                if($_POST['checkall'] == 'true' OR in_array($field['id'],$value)) {
                    $field['select'] = true;
                    $group['expand'] = true;
                }
            }
        }
        echo json_encode(array_values($field_groups));
        break;
    case 'tree_select_cascading':
        // Validate the template input as a security measure
        if(!is_valid_path($_GET['template_path'])) {
            break;
        }

        if($_GET['data_type'] == 'category' OR $_GET['data_type'] == 'category_tree') {
            $tree_pointer = $PMDR->get('Categories');
            $setup_variable = 'category_setup';
        } elseif($_GET['data_type'] == 'location_tree') {
            $tree_pointer = $PMDR->get('Locations');
            $setup_variable = 'location_setup';
        } elseif($_GET['data_type'] == 'classifieds_category') {
            $tree_pointer = $PMDR->get('Classifieds_Categories');
            $setup_variable = 'category_setup';
        } else {
            return;
        }

        if(isset($_GET['require_leaf']) AND $_GET['require_leaf'] == 'true') {
            $require_leaf = true;
        } else {
            $require_leaf = false;
        }
        $where = '';
        if(!value($_GET,'hidden',true)) {
            $where .= ' AND hidden=0';
        }
        if(!value($_GET,'closed',true)) {
            $where .= ' AND closed=0';
        }
        $filter = value($_GET,'filter',false);
        if(!empty($filter)) {
            $where .= ' AND id IN('.$PMDR->get('Cleaner')->clean_output(value($_GET,'filter',false)).')';
        }
        if(!empty($_GET['value'])) {
            if($path = $tree_pointer->getPath($_GET['value'])) {
                if($PMDR->getConfig($setup_variable) == 0 AND !value($_GET,'bypass_setup') AND !isset($_GET['require_leaf'])) {
                        $require_leaf = true;
                    }
                }
            }
        if(!isset($path) or !$path) {
            $path = array();
            $path[] = array('parent_id'=>1,'level'=>0);
        }
        $select = '';
        foreach($path AS $key=>$item) {
            $records = $tree_pointer->getCascadingSelect($item['parent_id'],$where);
            if($records) {
                $options = '<option value="">'.$PMDR->getLanguage('select_one').'</option>';
                foreach($records AS $record) {
                    $options .= '<option value="'.$record['id'].'"';
                    if($record['id'] == $item['id']) {
                        $options .= ' selected="selected"';
                    }
                    $options .= '>'.$PMDR->get('Cleaner')->clean_output($record['title']).'</option>';
                }
                if($template_select_cascading = $PMDR->getNew('Template',$_GET['template_path'].'form_select_cascading.tpl')) {
                    $template_select_cascading->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
                    $template_select_cascading->set('level',$item['level']);
                    $template_select_cascading->set('options',$options);
                    unset($options);
                }
                $select .= $template_select_cascading->render();
                unset($template_select_cascading);
            }
        }
        $selected_children = $tree_pointer->getCascadingSelect($path[(count($path)-1)]['id'],$where);
        if($selected_children) {
            if(isset($_POST['select_text_secondary'])) {
                $options = '<option value="">'.$PMDR->get('Cleaner')->clean_output($_POST['select_text_secondary']).'</option>';
            } else {
                $options = '<option value="">'.$PMDR->getLanguage('select_one').'</option>';
            }
            foreach($selected_children AS $child) {
                $options .= '<option value="'.$child['id'].'">'.$PMDR->get('Cleaner')->clean_output($child['title']).'</option>';
            }
            if($template_select_cascading = $PMDR->getNew('Template',$_GET['template_path'].'form_select_cascading.tpl')) {
                $template_select_cascading->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
                $template_select_cascading->set('level',$path[0]['level']+1);
                $template_select_cascading->set('options',$options);
                unset($options);
            }
            $select .= $template_select_cascading->render();
            unset($template_select_cascading);
        }
        if(!$require_leaf OR (!$selected_children AND $require_leaf)) {
            $select .= '<input type="hidden" id="'.$PMDR->get('Cleaner')->clean_output($_GET['name']).'" name="'.$PMDR->get('Cleaner')->clean_output($_GET['name']).'" value="'.$PMDR->get('Cleaner')->clean_output($_GET['value']).'" />';
        }
        echo $select;
        break;
    case 'tree_select_cascading_multiple':
        // Validate the template input as a security measure
        if(!is_valid_path($_GET['template_path'])) {
            break;
        }

        if($_GET['data_type'] == 'category' OR $_GET['data_type'] == 'category_tree') {
            $tree_pointer = $PMDR->get('Categories');
            $setup_variable = 'category_setup';
        } elseif($_GET['data_type'] == 'location_tree') {
            $tree_pointer = $PMDR->get('Locations');
            $setup_variable = 'location_setup';
        } elseif($_GET['data_type'] == 'classifieds_category') {
            $tree_pointer = $PMDR->get('Classifieds_Categories');
            $setup_variable = 'category_setup';
        } else {
            return;
        }

        $require_leaf = false;
        $select = '';
        $where = '';
        if(!value($_GET,'hidden',true)) {
            $where .= ' AND hidden=0';
        }
        if(!value($_GET,'closed',true)) {
            $where .= ' AND closed=0';
        }
        $filter = value($_GET,'filter',false);
        if(!empty($filter)) {
            $where .= ' AND id IN('.$PMDR->get('Cleaner')->clean_output(value($_GET,'filter',false)).')';
        }
        if($_GET['select_value'] == '') {
            $path[] = array('parent_id'=>1,'level'=>0);
        } else {
            $path = $tree_pointer->getPath($_GET['select_value']);
            if($PMDR->getConfig($setup_variable) == 0 AND !value($_GET,'bypass_setup')) {
                $require_leaf = true;
            }
        }
        $values = array();
        if(!empty($_GET['value'])) {
            $values = array_filter(array_unique(explode(',',$_GET['value'])));
            $selected_content = '';
            foreach($values AS $value) {
                if($template_select_cascading = $PMDR->getNew('Template',$_GET['template_path'].'form_select_cascading_selected.tpl')) {
                    $template_select_cascading->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
                    $template_select_cascading->set('value',$PMDR->get('Cleaner')->clean_output($value));
                    $template_select_cascading->set('selected_content',$tree_pointer->getPathDisplay($tree_pointer->getPath($value),' &raquo; ',false));
                    $select .= $template_select_cascading->render();
                }
                unset($template_select_cascading);
            }
        }
        if(count($values) < $_GET['limit']) {
            foreach($path AS $key=>$item) {
                $records = $tree_pointer->getCascadingSelect($item['parent_id'],$where);
                if($records) {
                    $options = '<option value="">'.$PMDR->getLanguage('select_one').'</option>';
                    foreach($records AS $record) {
                        $options .= '<option value="'.$record['id'].'"';
                        if($record['id'] == $item['id']) {
                            $options .= ' selected="selected"';
                        }
                        $options .= '>'.$PMDR->get('Cleaner')->clean_output($record['title']).'</option>';
                    }
                    if(isset($template_select_cascading)) {
                        $select .= $template_select_cascading->render();
                    }
                    if($template_select_cascading = $PMDR->getNew('Template',$_GET['template_path'].'form_select_cascading.tpl')) {
                        $template_select_cascading->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
                        $template_select_cascading->set('level',$item['level']);
                        $template_select_cascading->set('options',$options);
                        unset($options);
                    }
                }
            }
            $selected_children = $tree_pointer->getCascadingSelect($path[(count($path)-1)]['id'],$where);
            if($selected_children) {
                $options = '<option value="">'.$PMDR->getLanguage('select_one').'</option>';
                foreach($selected_children AS $child) {
                    $options .= '<option value="'.$child['id'].'">'.$PMDR->get('Cleaner')->clean_output($child['title']).'</option>';
                }
                if(isset($template_select_cascading)) {
                    $select .= $template_select_cascading->render();
                }
                if($template_select_cascading = $PMDR->getNew('Template',$_GET['template_path'].'form_select_cascading.tpl')) {
                    $template_select_cascading->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
                    $template_select_cascading->set('level',($path[0]['level']+1));
                    $template_select_cascading->set('options',$options);
                    unset($options);
                }
            }
            if((!$require_leaf OR (!$selected_children AND $require_leaf)) AND !empty($_GET['select_value'])) {
                $template_select_cascading->set('add_link',true);
            }
            $select .= $template_select_cascading->render();
            unset($template_select_cascading);
        }
        echo $select;
        break;
    case 'hours':
        // Validate the template input as a security measure
        if(!is_valid_path($_GET['template_path'])) {
            break;
        }
        if($template_hours_selected = $PMDR->getNew('Template',$_GET['template_path'].'form_hours_selected.tpl')) {
            $template_hours_selected->set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
            $template_hours_selected->set('day',$PMDR->get('Cleaner')->clean_output($_GET['day']));
            $template_hours_selected->set('time1',$PMDR->get('Cleaner')->clean_output($_GET['time1']));
            $template_hours_selected->set('time2',$PMDR->get('Cleaner')->clean_output($_GET['time2']));
            $template_hours_selected->set('hour',$PMDR->get('Cleaner')->clean_output($_GET['value']));
            echo $template_hours_selected->render();
        }
        break;
    case 'qrcode':
        $qr_code = $PMDR->get('QRCode');
        $qr_code->setBarcode(urldecode($PMDR->get('Cleaner')->clean_output($_GET['content'])), 'QRCODE');
        $qr_code->getBarcodePNG(3, 3, array(0,0,0));
        break;
    case 'admin_category_tree_search':
        if(!is_valid_path($_GET['template_path'])) {
            break;
        }
        $categories = $db->GetAll("SELECT id, title FROM ".T_CATEGORIES." WHERE title LIKE ".$PMDR->get('Cleaner')->clean_db($_GET['value'].'%').' LIMIT 30');
        $template = $PMDR->getNew('Template',$_GET['template_path'].'form_select_tree_search_results.tpl');
        foreach($categories AS &$category) {
            $category_path = $PMDR->get('Categories')->getPath($category['id']);
            $path_id_array = array();
            foreach($category_path AS $path) {
                $path_id_array[] = $path['id'];
                $path['id_path'] = ','.implode(',',$path_id_array);
                $category['path'][] = $path;
            }
        }
        $template->set('results',$categories);
        $template->Set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
        echo $template->render();
        break;
    case 'admin_location_tree_search':
        if(!is_valid_path($_GET['template_path'])) {
            break;
        }
        $locations = $db->GetAll("SELECT id, title FROM ".T_LOCATIONS." WHERE title LIKE ".$PMDR->get('Cleaner')->clean_db($_GET['value'].'%').' LIMIT 30');
        $template = $PMDR->getNew('Template',$_GET['template_path'].'form_select_tree_search_results.tpl');
        foreach($locations AS &$location) {
            $location_path = $PMDR->get('Locations')->getPath($location['id']);
            $path_id_array = array();
            foreach($location_path AS $path) {
                $path_id_array[] = $path['id'];
                $path['id_path'] = ','.implode(',',$path_id_array);
                $location['path'][] = $path;
            }
        }
        $template->set('results',$locations);
        $template->Set('name',$PMDR->get('Cleaner')->clean_output($_GET['name']));
        echo $template->render();
        break;
}
?>