<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

// Load specific language groups
$PMDR->loadLanguage(array('fields','admin_fields'));

// Check that we are authorized
$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_field_editor');

/** @var Fields_Groups */
$fields_groups = $PMDR->get('Fields_Groups');

if(!isset($_GET['action']) AND !$fields_groups->getCount()) {
    $PMDR->addMessage('info',$PMDR->getLanguage('admin_fields_group_add_error'));
    redirect(null,array('action'=>'add'));
}

// Delete a field group
if($_GET['action'] == 'delete') {
    $fields_groups->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_fields'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('type',$PMDR->getLanguage('admin_fields_type'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_fields_name'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_fields_order').' [<a href="" onclick="updateOrdering(\''.T_FIELDS_GROUPS.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($fields_groups->getCount());
    $records = $fields_groups->getRows(array(),array('ordering'=>'ASC'),$table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['type'] = $PMDR->getLanguage('admin_fields_type_'.$record['type']);
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" size="2" class="form-control" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('fields',array('label'=>$PMDR->getLanguage('admin_fields'),'href'=>'admin_fields.php?group_id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('fields_add',array('label'=>$PMDR->getLanguage('admin_fields_add'),'href'=>'admin_fields.php?action=add&group_id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_fields_groups'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_fields_groups')));
    $types = array(
        'users'=>$PMDR->getLanguage('admin_fields_type_users'),
        'listings'=>$PMDR->getLanguage('admin_fields_type_listings'),
        'classifieds'=>$PMDR->getLanguage('admin_fields_type_classifieds'),
        'classifieds_email'=>$PMDR->getLanguage('admin_fields_type_classifieds_email'),
        'reviews'=>$PMDR->getLanguage('admin_fields_type_reviews'),
        'contact'=>$PMDR->getLanguage('admin_fields_type_contact'),
        'send_message'=>$PMDR->getLanguage('admin_fields_type_send_message'),
        'send_message_friend'=>$PMDR->getLanguage('admin_fields_type_send_message_friend'),
        'claim_listing'=>$PMDR->getLanguage('admin_fields_type_claim_listing'),
        'categories'=>$PMDR->getLanguage('admin_fields_type_categories'),
        'locations'=>$PMDR->getLanguage('admin_fields_type_locations'),
        'events'=>$PMDR->getLanguage('admin_fields_type_events'),
        'jobs'=>$PMDR->getLanguage('admin_fields_type_jobs'),
        'blog'=>$PMDR->getLanguage('admin_fields_type_blog'),
        'contact_requests'=>$PMDR->getLanguage('admin_fields_type_contact_requests'),
        'documents'=>$PMDR->getLanguage('admin_fields_type_documents'),
        'images'=>$PMDR->getLanguage('admin_fields_type_images')
    );
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_fields_type'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_fields_groups_type_help'),'options'=>$types));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_fields_name'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_fields_groups_name_help')));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_fields_order'),'fieldset'=>'information','value'=>0,'help'=>$PMDR->getLanguage('admin_fields_groups_order_help')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('ordering',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_fields_groups_edit'));
        if($db->GetOne("SELECT COUNT(*) FROM ".T_FIELDS." WHERE group_id=?",array($_GET['id']))) {
            $form->deleteField('type');
        }
        $form->loadValues($fields_groups->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_fields_groups_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $fields_groups->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_fields_groups'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $fields_groups->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_fields_groups'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_fields_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>