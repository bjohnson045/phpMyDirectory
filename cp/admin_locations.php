<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_locations'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_locations_view');

$locations = $PMDR->get('Locations');

$locations->checkReset();

if($PMDR->get('Locations')->getSize() == 0) {
    $PMDR->addMessage('warning','We suggest using the <a href="admin_locations_setup.php">Location Setup Wizard</a> to setup and enter your first location.');
}

if($_GET['action'] == 'download') {
    $PMDR->get('ServeFile')->serve(TEMP_UPLOAD_PATH.'locations_export.csv');
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_locations_delete');
    if($PMDR->get('Locations')->getFullCount($_GET['id'])) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_locations_contains_listings'));
    } else {
        $locations->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_locations'))),'delete');
    }
    redirect();
}

if(isset($_POST['table_list_submit'])) {
    if($_POST['action'] == 'delete') {
        $PMDR->get('Authentication')->checkPermission('admin_locations_delete');
        foreach($_POST['table_list_checkboxes'] AS $id) {
            if(!$location = $db->GetRow("SELECT left_, right_ FROM ".T_LOCATIONS." WHERE id=?",array($id))) {
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($id,$PMDR->getLanguage('admin_locations'))),'delete');
                continue;
            }
            $count = $db->GetOne("SELECT COUNT(*) FROM ".T_LISTINGS." l INNER JOIN ".T_LOCATIONS." lc ON l.location_id=lc.id WHERE lc.left_ BETWEEN ".($location['left_'])." AND ".($location['right_']));
            if($count) {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_locations_contains_listings'));
            } else {
                $locations->delete($id);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($id,$PMDR->getLanguage('admin_locations'))),'delete');
            }
        }
    }
    redirect();
}

if(!isset($_GET['action']) OR $_GET['action'] == 'export' OR $_GET['action'] == 'sort') {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_locations.tpl');

    $template_content->set('title',$PMDR->getLanguage('admin_locations'));
    $table_list = $PMDR->get('TableList');
    $order_checkbox_options = array(
        ''=>'',
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$order_checkbox_options)));
    $table_list->addColumn('id');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_locations_location'));
    $table_list->addColumn('count_total',$PMDR->getLanguage('admin_locations_count_total'));
    $table_list->addColumn('description_short');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_LOCATIONS." WHERE level=1 ORDER BY left_ ASC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = '<a href="admin_locations.php?action=edit&id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['title']).'</a>';
        if(!$PMDR->get('Locations')->isLeaf($record)) {
            $record['title'] = '<div id="'.$record['id'].'" class="collapsed">'.$record['title'].'</div>';
        } else {
            $record['title'] = '<span style="margin-left: 15px;">'.$record['title'].'</span>';
        }
        $record['count_total'] = '<a href="admin_listings.php?location='.$record['id'].'">'.$record['count_total'].'</a>';
        $record['description_short'] = $PMDR->get('Cleaner')->clean_output($record['description_short']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);

    $locations->updateLanguageVariables();
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_locations_edit');

    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_locations_location')));
    $form->addFieldSet('meta');
    $form->addFieldSet('images');
    $form->addField('title','text',array('fieldset'=>'details','onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text',array('fieldset'=>'details'));
    $form->addField('abbreviation','text',array('fieldset'=>'details'));
    $form->addField('description_short','textarea',array('fieldset'=>'details'));
    if($PMDR->getConfig('html_editor_locations')) {
        $form->addField('description','htmleditor',array('fieldset'=>'details'));
    } else {
        $form->addField('description','textarea',array('fieldset'=>'details'));
    }
    $form->addField('small_image_url','text',array('fieldset'=>'images'));
    $form->addField('small_image','file',array('fieldset'=>'images'));
    if($_GET['action'] == 'edit' AND $image_url = get_file_url(LOCATION_IMAGE_PATH.$_GET['id'].'-small.*',true)) {
        $form->addField('current_small_image','custom',array('fieldset'=>'images','html'=>'<img src="'.$image_url.'">'));
        $form->addField('small_image_delete','checkbox',array('fieldset'=>'images'));
    }
    $form->addField('large_image_url','text',array('fieldset'=>'images'));
    $form->addField('large_image','file',array('fieldset'=>'images'));
    if($_GET['action'] == 'edit' AND $image_url = get_file_url(LOCATION_IMAGE_PATH.$_GET['id'].'.*',true)) {
        $form->addField('current_large_image','custom',array('fieldset'=>'images','html'=>'<img src="'.$image_url.'">'));
        $form->addField('large_image_delete','checkbox',array('fieldset'=>'images'));
    }
    $form->addField('keywords','text',array('fieldset'=>'details'));
    $form->addField('meta_title','text',array('fieldset'=>'meta'));
    $form->addField('meta_keywords','text',array('fieldset'=>'meta'));
    $form->addField('meta_description','textarea',array('fieldset'=>'meta'));
    $form->addField('link','text',array('fieldset'=>'details'));
    $form->addField('featured','checkbox',array('fieldset'=>'details'));
    $form->addField('closed','checkbox',array('fieldset'=>'details'));
    $form->addField('hidden','checkbox',array('fieldset'=>'details'));
    $form->addField('no_follow','checkbox',array('fieldset'=>'details'));
    $form->addField('display_columns','text',array('fieldset'=>'details'));
    $form->addField('disable_geocoding','checkbox',array('fieldset'=>'details'));
    $form->addField('placement','radio',array('fieldset'=>'details','options'=>array('before'=>$PMDR->getLanguage('admin_locations_before'),'after'=>$PMDR->getLanguage('admin_locations_after'),'subcategory'=>$PMDR->getLanguage('admin_locations_subcategory_of'))));
    $form->addField('placement_id','tree_select_expanding_radio',array('fieldset'=>'details','options'=>array('type'=>'location_tree','search'=>true,'bypass_setup'=>true)));
    $form->addField('header_template_file','text',array('fieldset'=>'details'));
    $form->addField('footer_template_file','text',array('fieldset'=>'details'));
    $form->addField('wrapper_template_file','text',array('fieldset'=>'details'));
    $form->addField('results_template_file','text',array('fieldset'=>'details'));
    $fields = $PMDR->get('Fields')->addToForm($form,'locations',array('fieldset'=>'details'));

    $PMDR->get('Plugins')->run_hook('admin_locations_form');

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_NonEmpty());
    $form->addValidator('link',new Validate_URL());

    if($_GET['action'] == 'edit') {
        $form->addField('submit_edit','submit',array('label'=>$PMDR->getLanguage('admin_submit_reload'),'fieldset'=>'submit'));
        $template_content->set('title',$PMDR->getLanguage('admin_locations_edit'));
        $location = $locations->getNode($_GET['id']);
        $form->loadValues($locations->getNode($_GET['id']));
    } else {
        $form->addField('submit_add','submit',array('label'=>$PMDR->getLanguage('admin_submit_add'),'fieldset'=>'submit'));
        $template_content->set('title',$PMDR->getLanguage('admin_locations_add'));
        $form->setFieldAttribute('placement','value','subcategory');
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_edit') OR $form->wasSubmitted('submit_add')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                if($locations->insert($data)) {
                    $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_locations'))),'insert');
                } else {
                    $PMDR->addMessage('error','Error adding location.');
                }
            } elseif($_GET['action'] == 'edit') {
                if($data['title'] != $location['title']) {
                    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_location_rebuild_search_index'));
                }
                $locations->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_locations'))),'update');

            }
            $locations->resetChildRowIDs();
            if($form->wasSubmitted('submit_edit') OR $form->wasSubmitted('submit_add')) {
                redirect(URL);
            } else {
                redirect();
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_locations_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>