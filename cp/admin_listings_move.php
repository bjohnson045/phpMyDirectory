<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_listings','admin_listings_move'));

$PMDR->get('Authentication')->checkPermission('admin_listings_move');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$form = $PMDR->get('Form');
if(!isset($_GET['action'])) {
    $form->method = 'get';
    $template_content->set('title',$PMDR->getLanguage('admin_listings_move'));
    $form->addFieldSet('move',array('legend'=>$PMDR->getLanguage('admin_listings_move')));
    $form->addField('action','select',array('label'=>$PMDR->getLanguage('admin_listings_type'),'fieldset'=>'move','options'=>array('categories_move'=>$PMDR->getLanguage('admin_listings_move_categories'),'categories_copy'=>$PMDR->getLanguage('admin_listings_move_categories_copy'),'locations_move'=>$PMDR->getLanguage('admin_listings_move_locations'))));
} else {
    if($_GET['action'] == 'categories_move' OR $_GET['action'] == 'categories_copy') {
        if($_GET['action'] == 'categories_copy') {
            $form->addFieldSet('move',array('legend'=>$PMDR->getLanguage('admin_listings_move_categories_copy')));
        } else {
            $form->addFieldSet('move',array('legend'=>$PMDR->getLanguage('admin_listings_move_categories')));
        }
        $form->addField('category_from','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_listings_move_from_categories'),'fieldset'=>'move','value'=>'','options'=>array('type'=>'category_tree','bypass_setup'=>true)));
        $form->addField('category_to','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_move_to_category'),'fieldset'=>'move','value'=>'','options'=>array('type'=>'category_tree','bypass_setup'=>true)));
        if($_GET['action'] == 'categories_copy') {
            $template_content->set('title',$PMDR->getLanguage('admin_listings_move_categories_copy'));
            $form->addField('override','checkbox',array('label'=>$PMDR->getLanguage('admin_listings_move_override_limits'),'fieldset'=>'move'));
        } else {
            $template_content->set('title',$PMDR->getLanguage('admin_listings_move_categories'));
            $form->addField('remove','checkbox',array('label'=>$PMDR->getLanguage('admin_listings_move_remove_old_categories'),'fieldset'=>'move'));
        }
        $form->addValidator('category_from',new Validate_NonEmpty());
        $form->addValidator('category_to',new Validate_NonEmpty());
    } elseif($_GET['action'] == 'locations_move') {
        $template_content->set('title',$PMDR->getLanguage('admin_listings_move_locations'));
        $form->addFieldSet('move',array('legend'=>$PMDR->getLanguage('admin_listings_move_locations')));
        $form->addField('location_from','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_listings_move_from_locations'),'fieldset'=>'move','value'=>'','options'=>array('type'=>'location_tree','bypass_setup'=>true)));
        $form->addField('location_to','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_listings_move_to_location'),'fieldset'=>'move','value'=>'','options'=>array('type'=>'location_tree','bypass_setup'=>true)));
        $form->addField('remove','checkbox',array('label'=>$PMDR->getLanguage('admin_listings_move_remove_old_locations'),'fieldset'=>'move'));
        $form->addValidator('location_from',new Validate_NonEmpty());
        $form->addValidator('location_to',new Validate_NonEmpty());
    }
    $form->addField('action','hidden',array('label'=>'Action','fieldset'=>'move','value'=>$_GET['action']));

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            switch($_POST['action']) {
                case "locations_move":
                    $db->Execute ("UPDATE ".T_LISTINGS." SET location_id='".$data['location_to']."' WHERE location_id IN(".implode(',',$data['location_from']).")");
                    $change_count = $db->Affected_Rows();
                    if($data['remove']){
                        foreach($data['location_from'] as $id) {
                            $PMDR->get('Locations')->delete($id);
                        }
                    }
                    $PMDR->addMessage('success',$PMDR->getLanguage('admin_listings_move_locations_moved',array($change_count)));
                    break;
                case "categories_move":
                    $db->Execute("UPDATE ".T_LISTINGS." SET primary_category_id=? WHERE primary_category_id IN (".implode(',',$data['category_from']).")",array($data['category_to']));
                    $change_count = 0;
                    foreach($data['category_from'] as $id) {
                        $category = $db->GetRow("SELECT left_, right_ FROM ".T_CATEGORIES." WHERE id=?",array($id));
                        $categories = array($id);
                        if($subcategories = $db->GetCol("SELECT id FROM ".T_CATEGORIES." WHERE left_ > ".$category['left_']." AND right_ < ".$category['right_'])) {
                            $categories = array_merge($categories,$subcategories);
                        }
                        unset($subcategories);
                        $db->Execute("UPDATE IGNORE ".T_LISTINGS_CATEGORIES." SET cat_id=? WHERE cat_id IN (".implode(',',$categories).")",array($data['category_to']));
                        $change_count += $db->Affected_Rows();
                        $db->Execute("DELETE FROM ".T_LISTINGS_CATEGORIES." WHERE cat_id IN (".implode(',',$categories).")");
                        if($data['remove']) {
                            $PMDR->get('Categories')->delete($id);
                        }
                    }
                    $PMDR->addMessage('success',$PMDR->getLanguage('admin_listings_move_categories_moved',array($change_count)));
                    break;
                case "categories_copy":
                    $failedCt = 0;
                    $post = $data['override'];
                    $listings_from = $db->GetAll("
                        SELECT
                            list_id,
                            COUNT(cat_id) as categories,
                            category_limit
                        FROM ".T_LISTINGS_CATEGORIES." lc
                        INNER JOIN ".T_LISTINGS." l ON lc.list_id=l.id
                        WHERE cat_id IN(".implode(',',$data['category_from']).") GROUP BY list_id");
                    foreach($listings_from as $key=>$fa) {
                        if(!$data['override']) {
                            if($fa['categories'] < $fa['category_limit']) {
                                $id_string .= $fa['list_id'].',';
                                $values .= '(' . $fa['list_id'] . ',' . $data['category_to'] . '),';
                            }
                        } else {
                            $id_string .= $fa['list_id'].',';
                            $values .= '(' . $fa['list_id'] . ',' . $data['category_to'] . '),';
                        }
                    }
                    $values = rtrim($values,',');
                    if($id_string != "") {
                        $values = rtrim($values,',');
                        $db->Execute("DELETE FROM ".T_LISTINGS_CATEGORIES." WHERE cat_id=? AND list_id IN (".rtrim($id_string,',').")",array(rtrim($data['category_to'],',')));
                        $db->Execute("INSERT INTO ".T_LISTINGS_CATEGORIES." (list_id, cat_id) VALUES $values");
                        $totalCt = $db->Affected_Rows();
                    } else {
                        $totalCt = 0;
                    }
                    $PMDR->addMessage('success',$PMDR->getLanguage('admin_listings_move_categories_copied',array($totalCt,(count($listings_from)-$totalCt))));
                    break;
                default:
                    break;
            }
        }
    }

}
$form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
$template_content->set('content',$form->toHTML());

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_listings_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>