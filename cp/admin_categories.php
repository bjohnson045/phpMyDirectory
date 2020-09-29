<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_categories'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_categories_view');

$categories = $PMDR->get('Categories');

$categories->checkReset();

if($_GET['action'] == 'download') {
    $PMDR->get('ServeFile')->serve($PMDR->get('Categories')->getExportFileName());
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_categories_delete');
    if(!$category = $db->GetRow("SELECT left_, right_ FROM ".T_CATEGORIES." WHERE id=?",array($_GET['id']))) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_categories'))),'delete');
    } elseif($PMDR->get('Categories')->getRawCount($_GET['id'])) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_categories_delete_has_listings'));
    } else {
        $categories->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_categories'))),'delete');
    }
    redirect();
}

if(isset($_POST['table_list_submit'])) {
    if($_POST['action'] == 'delete') {
        $PMDR->get('Authentication')->checkPermission('admin_categories_delete');
        foreach($_POST['table_list_checkboxes'] AS $id) {
            if(!$category = $db->GetRow("SELECT left_, right_ FROM ".T_CATEGORIES." WHERE id=?",array($id))) {
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($id,$PMDR->getLanguage('admin_categories'))),'delete');
                continue;
            }
            if($PMDR->get('Categories')->getRawCount($id)) {
                $PMDR->addMessage('error',$PMDR->getLanguage('admin_categories_delete_has_listings'));
            } else {
                $categories->delete($id);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($id,$PMDR->getLanguage('admin_categories'))),'delete');
            }
        }
    }
    redirect();
}

if(!isset($_GET['action']) OR $_GET['action'] == 'export' OR $_GET['action'] == 'sort') {
    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_categories.tpl');

    $template_content->set('title',$PMDR->getLanguage('admin_categories'));
    $table_list = $PMDR->get('TableList');
    $order_checkbox_options = array(
        ''=>'',
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$order_checkbox_options)));
    $table_list->addColumn('id',$PMDR->getLanguage('admin_categories_id'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_categories_category'));
    $table_list->addColumn('count_total',$PMDR->getLanguage('admin_categories_count_total'));
    $table_list->addColumn('description_short');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $records = $PMDR->get('Categories')->getAdmin($paging->limit1,$paging->limit2);
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = '<a href="admin_categories.php?action=edit&id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['title']).'</a>';
        if(!$PMDR->get('Categories')->isLeaf($record)) {
            $record['title'] = '<div id="'.$record['id'].'" class="collapsed">'.$record['title'].'</div>';
        } else {
            $record['title'] = '<span style="margin-left: 15px;">'.$record['title'].'</span>';
        }
        $record['count_total'] = '<a href="admin_listings.php?category='.$record['id'].'">'.$record['count_total'].'</a>';
        $record['description_short'] = $PMDR->get('Cleaner')->clean_output($record['description_short']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);

    $categories->updateLanguageVariables();
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_categories_edit');

    $template_content = $PMDR->getNew('Template', PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

    $languages = $db->GetAll("SELECT languageid, title FROM ".T_LANGUAGES." WHERE languageid!=1");

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_categories_category')));
    $form->addFieldSet('meta');
    $form->addFieldSet('images');
    $form->addField('title','text',array('fieldset'=>'details','onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    foreach($languages AS $language) {
        $form->addField('title_'.$language['languageid'],'text',array('fieldset'=>'details','label'=>'Title ('.$language['title'].')'));
    }
    $form->addField('friendly_url','text',array('fieldset'=>'details'));
    $form->addField('description_short','textarea',array('fieldset'=>'details'));
    if($PMDR->getConfig('html_editor_categories')) {
        $form->addField('description','htmleditor',array('fieldset'=>'details'));
    } else {
        $form->addField('description','textarea',array('fieldset'=>'details'));
    }
    $form->addField('small_image_url','text',array('fieldset'=>'images'));
    $form->addField('small_image','file',array('fieldset'=>'images'));
    if($_GET['action'] == 'edit' AND $image_url = get_file_url(CATEGORY_IMAGE_PATH.$_GET['id'].'-small.*',true)) {
        $form->addField('current_small_image','custom',array('fieldset'=>'images','value'=>'','options'=>'','html'=>'<img src="'.$image_url.'">'));
        $form->addField('small_image_delete','checkbox',array('fieldset'=>'images'));
    }
    $form->addField('large_image_url','text',array('fieldset'=>'images'));
    $form->addField('large_image','file',array('fieldset'=>'images'));
    if($_GET['action'] == 'edit' AND $image_url = get_file_url(CATEGORY_IMAGE_PATH.$_GET['id'].'.*',true)) {
        $form->addField('current_large_image','custom',array('fieldset'=>'images','value'=>'','options'=>'','html'=>'<img src="'.$image_url.'">'));
        $form->addField('large_image_delete','checkbox',array('fieldset'=>'images'));
    }
    $form->addField('map_image','file',array('fieldset'=>'images'));
    if($_GET['action'] == 'edit' AND $image_url = get_file_url(CATEGORY_IMAGE_PATH.$_GET['id'].'-map.*',true)) {
        $form->addField('current_map_image','custom',array('fieldset'=>'images','value'=>'','options'=>'','html'=>'<img src="'.$image_url.'">'));
        $form->addField('map_image_delete','checkbox',array('fieldset'=>'images'));
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
    $form->addField('placement','radio',array('fieldset'=>'details','options'=>array('before'=>$PMDR->getLanguage('admin_categories_before'),'after'=>$PMDR->getLanguage('admin_categories_after'),'subcategory'=>$PMDR->getLanguage('admin_categories_subcategory_of'))));
    $form->addField('placement_id','tree_select_expanding_radio',array('fieldset'=>'details','options'=>array('type'=>'category_tree','search'=>true,'bypass_setup'=>true)));
    $form->addField('related','tree_select_expanding_checkbox',array('fieldset'=>'details','options'=>array('type'=>'category_tree','search'=>true,'bypass_setup'=>true)));
    $form->addField('header_template_file','text',array('fieldset'=>'details'));
    $form->addField('footer_template_file','text',array('fieldset'=>'details'));
    $form->addField('wrapper_template_file','text',array('fieldset'=>'details'));
    $form->addField('results_template_file','text',array('fieldset'=>'details'));
    $fields = $PMDR->get('Fields')->addToForm($form,'categories',array('fieldset'=>'details'));
    $form->addField('fields','tree_select_expanding_checkbox',array('label'=>'Custom Fields','fieldset'=>'details','options'=>array('type'=>'custom_fields')));

    $PMDR->get('Plugins')->run_hook('admin_categories_form');

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_NonEmpty());
    $form->addValidator('link',new Validate_URL());

    if($_GET['action'] == 'edit') {
        $form->addField('submit_edit','submit',array('label'=>$PMDR->getLanguage('admin_submit_reload'),'fieldset'=>'submit'));
        $template_content->set('title',$PMDR->getLanguage('admin_categories_edit'));
        $category = $categories->getNode($_GET['id']);
        $category['fields'] = $db->GetCol("SELECT field_id FROM ".T_CATEGORIES_FIELDS." WHERE category_id=?",array($_GET['id']));
        $form->loadValues($category);
        $form->setFieldAttribute('related','value',$db->GetCol("SELECT related_category_id FROM ".T_CATEGORIES_RELATED." WHERE category_id=?",array($_GET['id'])));
    } else {
        $form->addField('submit_add','submit',array('label'=>$PMDR->getLanguage('admin_submit_add'),'fieldset'=>'submit'));
        $template_content->set('title',$PMDR->getLanguage('admin_categories_add'));
        $form->setFieldAttribute('placement','value','subcategory');
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_edit') OR $form->wasSubmitted('submit_add')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $categories->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_categories'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $categories->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_categories'))),'update');
            }
            $categories->resetChildRowIDs();
            if($form->wasSubmitted('submit_edit') OR $form->wasSubmitted('submit_add')) {
                redirect(URL);
            } else {
                redirect();
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_categories_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>