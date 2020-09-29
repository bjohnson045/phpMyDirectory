<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_users','admin_permissions','admin_users_merge','admin_contact_requests','admin_messages'));

$PMDR->get('Authentication')->checkPermission('admin_users_groups_view');

/** @var UsersGroups */
$usergroups = $PMDR->get('UsersGroups');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_users_groups_delete');
    // Make sure the user group does not have users, and that it is not a default user group
    if(!$usergroups->hasUsers($_GET['id']) AND $_GET['id'] > 5) {
        $usergroups->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_users_groups'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_users_groups_delete_failed'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_users_groups.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('name',$PMDR->getLanguage('admin_users_groups_name'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_users_groups_description'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($usergroups->getCount());
    $records = $usergroups->getRowsLimit($pageArray['limit1'],$pageArray['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['name'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        if($PMDR->getConfig('user_groups_user_default') == $record['id']) {
            $records[$key]['name'] .= ' <a href="'.BASE_URL_ADMIN.'/admin_settings.php?group=users&varname=user_groups_user_default"><span class="label label-info">Default Group</span></a>';
        }
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if($record['id'] > 5) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);

    $template_content->set('title',$PMDR->getLanguage('admin_users_groups'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_users_groups_edit');
    /** @var Form */
    $form = $PMDR->get('Form');
    $form->addFieldSet('group',array('legend'=>$PMDR->getLanguage('admin_users_groups_information')));
    $form->addFieldSet('permissions',array('legend'=>$PMDR->getLanguage('admin_users_groups_permissions')));
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_users_groups_name'),'fieldset'=>'group'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_users_groups_description'),'fieldset'=>'group'));
    $form->addField('administrator','checkbox',array('label'=>$PMDR->getLanguage('admin_users_administrator'),'help'=>$PMDR->getLanguage('admin_users_administrator_help'),'fieldset'=>'group'));
    $form->addField('advertiser','checkbox',array('label'=>$PMDR->getLanguage('admin_users_advertiser'),'help'=>$PMDR->getLanguage('admin_users_advertiser_help'),'fieldset'=>'group'));
    $form->addField('user','checkbox',array('label'=>$PMDR->getLanguage('admin_users_user'),'help'=>$PMDR->getLanguage('admin_users_user_help'),'fieldset'=>'group'));

    $group_permissions = $db->GetCol("SELECT id FROM ".T_USERS_PERMISSIONS." WHERE id NOT IN('admin_administrator','user_advertiser','user_user')");
    $administrator_permissions = array();
    $user_permissions = array();
    foreach($group_permissions as $permission) {
        if(strstr($permission,'admin_')) {
            $administrator_permissions[$permission] = $PMDR->getLanguage('admin_permissions_'.$permission.'_title');
        } else {
            $user_permissions[$permission] = $PMDR->getLanguage('admin_permissions_'.$permission.'_title');
        }
    }
    $form->addField('administrator_permissions','checkbox',array('fieldset'=>'permissions','value'=>'','options'=>$administrator_permissions,'checkall'=>true,'columns'=>2));
    $form->addField('user_permissions','checkbox',array('fieldset'=>'permissions','value'=>'','options'=>$user_permissions,'checkall'=>true,'columns'=>2));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('name',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_users_groups_edit'));
        $form->loadValues($usergroups->getRow($_GET['id']));
        $existing_permissions = $db->GetCol("SELECT permission_id FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." WHERE group_id=?",array($_GET['id']));
        $form->setFieldAttribute('administrator_permissions','value',$existing_permissions);
        $form->setFieldAttribute('user_permissions','value',$existing_permissions);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_users_groups_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $group_id = $usergroups->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_users_groups'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $usergroups->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_users_groups'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>