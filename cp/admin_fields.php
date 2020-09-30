<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('fields','admin_fields'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_field_editor');

$fields = $PMDR->get('Fields');
$fields_groups = $PMDR->get('Fields_Groups');

if($_GET['action'] == 'delete') {
    $fields->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_fields'))),'delete');
    redirect(array('group_id'=>$_GET['group_id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $filter = array();
    if(isset($_GET['group_id'])) $filter['group_id'] = $_GET['group_id'];

    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('id',$PMDR->getLanguage('admin_fields_id'));
    $table_list->addColumn('name',$PMDR->getLanguage('admin_fields_name'));
    $table_list->addColumn('type',$PMDR->getLanguage('admin_fields_type'));
    $table_list->addColumn('options',$PMDR->getLanguage('admin_fields_options'));
    $table_list->addColumn('selected',$PMDR->getLanguage('admin_fields_selected'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_fields_order').' [<a href="" onclick="updateOrdering(\''.T_FIELDS.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('required',$PMDR->getLanguage('admin_fields_required'));
    $table_list->addColumn('admin_only',$PMDR->getLanguage('admin_fields_administrator_only'));
    $table_list->addColumn('search',$PMDR->getLanguage('admin_fields_search'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($fields->getCount($filter));
    $records = $fields->getRows($filter,array('ordering'=>'ASC'),$table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        $record['selected'] = $PMDR->get('Cleaner')->clean_output($record['selected']);
        $record['type'] = $PMDR->getLanguage('admin_fields_'.$record['type']);
        $record['required'] = $PMDR->get('HTML')->icon($record['required']);
        $record['search'] = $PMDR->get('HTML')->icon($record['search']);
        $record['admin_only'] = $PMDR->get('HTML')->icon($record['admin_only']);
        $record['options'] = $PMDR->get('Cleaner')->clean_output($record['options']);
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['id'].'&group_id='.$record['group_id']));
    }
    $table_list->addRecords($records);

    $field_group = $db->GetRow("SELECT type, title FROM ".T_FIELDS_GROUPS." WHERE id=?",array($_GET['group_id']));

    $template_content->set('title',$field_group['title'].' '.$PMDR->getLanguage('admin_fields').' ('.$PMDR->getLanguage('admin_fields_type').': '.$PMDR->getLanguage('admin_fields_type_'.$field_group['type']).')');
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('field_details',array('legend'=>$PMDR->getLanguage('admin_fields_information')));

    if($_GET['action'] == 'add') {
        $type = $db->GetOne("SELECT type FROM ".T_FIELDS_GROUPS." WHERE id=?",array($_GET['group_id']));
        $form->addField('group_id','hidden',array('label'=>$PMDR->getLanguage('admin_fields_group'),'fieldset'=>'field_details','value'=>$_GET['group_id']));
    } else {
        $field = $db->GetRow("SELECT f.*, g.type AS field_type FROM ".T_FIELDS." f, ".T_FIELDS_GROUPS." g WHERE f.group_id=g.id AND f.id=?",array($_GET['id']));
        $type = $field['field_type'];
        if(in_array($type,array('listings','reviews','send_message','send_message_friend','classifieds','classifieds_email'))) {
            $field['categories'] = $PMDR->get('Fields')->getFieldCategories($field['id'],$type);
        }
        if(in_array($type,array('listings','reviews','send_message','send_message_friend'))) {
            $field['products'] = $db->GetCol("SELECT p.id FROM ".T_PRODUCTS." p INNER JOIN ".T_MEMBERSHIPS." m ON p.type_id=m.id AND p.type='listing_membership' WHERE m.custom_".intval($_GET['id'])."_allow = 1");
        }
        $groups = $db->GetAssoc("SELECT id, title FROM ".T_FIELDS_GROUPS." WHERE type=?",array($type));
        $form->addField('group_id','select',array('label'=>$PMDR->getLanguage('admin_fields_group'),'fieldset'=>'field_details','options'=>$groups));
    }

    $types = array(
        'text'=>$PMDR->getLanguage('admin_fields_text'),
        'textarea'=>$PMDR->getLanguage('admin_fields_textarea'),
        'htmleditor'=>$PMDR->getLanguage('admin_fields_htmleditor'),
        'checkbox'=>$PMDR->getLanguage('admin_fields_checkbox'),
        'radio'=>$PMDR->getLanguage('admin_fields_radio'),
        'select'=>$PMDR->getLanguage('admin_fields_select'),
        'select_multiple'=>$PMDR->getLanguage('admin_fields_select_multiple'),
        'text_select'=>$PMDR->getLanguage('admin_fields_text_select'),
        'hidden'=>$PMDR->getLanguage('admin_fields_hidden'),
        'date'=>$PMDR->getLanguage('admin_fields_date'),
        'number'=>$PMDR->getLanguage('admin_fields_number'),
        'decimal'=>$PMDR->getLanguage('admin_fields_decimal'),
        'currency'=>$PMDR->getLanguage('admin_fields_currency'),
        'color'=>$PMDR->getLanguage('admin_fields_color'),
        'rating'=>$PMDR->getLanguage('admin_fields_rating'),
        'hours'=>$PMDR->getLanguage('admin_fields_hours'),
        'url_title'=>$PMDR->getLanguage('url')
    );
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_fields_type'),'fieldset'=>'field_details','value'=>'text','options'=>$types));
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_fields_name'),'fieldset'=>'field_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_fields_description'),'fieldset'=>'field_details','help'=>$PMDR->getLanguage('admin_fields_help_description')));
    $form->addField('options','textarea',array('label'=>$PMDR->getLanguage('admin_fields_options'),'fieldset'=>'field_details','no_trim'=>true));
    $form->addDependency('options',array('type'=>'display','field'=>'type','value'=>array('checkbox','radio','select','select_multiple','text_select')));
    $form->addField('selected','text',array('label'=>$PMDR->getLanguage('admin_fields_selected'),'fieldset'=>'field_details'));
    $form->addField('character_limit','text',array('label'=>$PMDR->getLanguage('admin_fields_character_limit'),'fieldset'=>'field_details'));
    $form->addDependency('character_limit',array('type'=>'display','field'=>'type','value'=>array('text','textarea','htmleditor','hidden','number','decimal')));
    $form->addField('select_limit','text',array('label'=>$PMDR->getLanguage('admin_fields_select_limit'),'fieldset'=>'field_details'));
    $form->addDependency('select_limit',array('type'=>'display','field'=>'type','value'=>array('checkbox','select','select_multiple','text_select')));

    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_fields_order'),'fieldset'=>'field_details'));
    $form->addField('regex','text',array('label'=>$PMDR->getLanguage('admin_fields_regex'),'fieldset'=>'field_details'));
    $form->addField('regex_error','text',array('label'=>$PMDR->getLanguage('admin_fields_regex_error'),'fieldset'=>'field_details'));

    $form->addField('required','checkbox',array('label'=>$PMDR->getLanguage('admin_fields_required'),'fieldset'=>'field_details'));
    if($type == 'listings') {
        $form->addField('search','checkbox',array('label'=>$PMDR->getLanguage('admin_fields_search'),'fieldset'=>'field_details'));
    }
    if(in_array($type,array('listings','reviews','users','blog','documents','images','classifieds'))) {
        $form->addField('hidden','checkbox',array('label'=>$PMDR->getLanguage('admin_fields_hidden'),'fieldset'=>'field_details'));
        $form->addField('admin_only','checkbox',array('label'=>$PMDR->getLanguage('admin_fields_administrator_only'),'fieldset'=>'field_details'));
        $form->addField('editable','checkbox',array('label'=>$PMDR->getLanguage('admin_fields_editable'),'fieldset'=>'field_details','value'=>1));
    }
    if(in_array($type,array('listings','reviews','send_message','send_message_friend'))) {
        $form->addField('categories','tree_select_expanding_checkbox',array('fieldset'=>'field_details','options'=>array('type'=>'category_tree','select_mode'=>3,'bypass_setup'=>true,'search'=>true)));
        $form->addField('products','tree_select_expanding_checkbox',array('fieldset'=>'field_details','label'=>'Products','value'=>'','options'=>array('type'=>'products_tree','hidden'=>true,'hide_pricing'=>true)));
        $form->addField('products_update','checkbox',array('fieldset'=>'field_details'));
    }
    if(in_array($type,array('classifieds','classifieds_email'))) {
        $form->addField('categories','tree_select_expanding_checkbox',array('fieldset'=>'field_details','options'=>array('type'=>'classifieds_category_tree','select_mode'=>2,'bypass_setup'=>true,'search'=>true)));
    }
    $form->addField('submit_field','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('name',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_fields_edit'));
        $form->loadValues($field);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_fields_add'));
    }

    if($form->wasSubmitted('submit_field')) {
        $data = $form->loadValues();
        if(!empty($data['regex']) AND empty($data['regex_error'])) {
            $form->addValidator('regex_error',new Validate_NonEmpty());
        }
        $data['group_type'] = $type;
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $fields->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_fields'))),'insert');
                redirect(array('group_id'=>$_GET['group_id']));
            } elseif($_GET['action'] == 'edit') {
                $fields->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_fields'))),'update');
                redirect(array('group_id'=>$field['group_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_fields_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>