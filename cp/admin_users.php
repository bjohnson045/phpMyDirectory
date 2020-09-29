<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

/** @var AuthenticationAdmin */
$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_users','admin_users_merge','email_templates','admin_contact_requests','admin_messages'));

$PMDR->get('Authentication')->checkPermission('admin_users_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_users_delete');
    if($_GET['id'] == 1) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_users_administrator_delete_error'));
    } else {
        $PMDR->get('Users')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_users'))),'delete');
    }
    redirect();
}

if(isset($_POST['table_list_submit']) AND isset($_POST['table_list_checkboxes'])) {
    if($_POST['action'] == 'delete') {
        $PMDR->get('Authentication')->checkPermission('admin_users_delete');
        if(in_array(1,$_POST['table_list_checkboxes'])) {
            $PMDR->addMessage('error',$PMDR->getLanguage('admin_users_administrator_delete_error'));
            redirect();
        }
        foreach($_POST['table_list_checkboxes'] AS $key=>$id) {
            $PMDR->get('Users')->delete($id);
        }
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array(implode(', ',$_POST['table_list_checkboxes']),$PMDR->getLanguage('admin_users'))),'delete');
    } elseif($_POST['action'] == 'send_email') {
        redirect('admin_email_send.php',array('user_id'=>implode(',',$_POST['table_list_checkboxes'])));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_users.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $form_search = $PMDR->getNew('Form');
    $form_search->method = 'GET';
    $form_search->label_suffix = '';
    $form_search->addFieldSet('user_search',array('legend'=>'Users Search'));
    $users_search_fields = array (
        'id'=>$PMDR->getLanguage('admin_users_id'),
        'login'=>$PMDR->getLanguage('admin_users_username'),
        'user_first_name'=>$PMDR->getLanguage('admin_users_first_name'),
        'user_last_name'=>$PMDR->getLanguage('admin_users_last_name'),
        'user_organization'=>$PMDR->getLanguage('admin_users_organization'),
        'user_email'=>$PMDR->getLanguage('admin_users_email'),
        'user_address1'=>$PMDR->getLanguage('admin_users_address1'),
        'user_address2'=>$PMDR->getLanguage('admin_users_address2'),
        'user_city'=>$PMDR->getLanguage('admin_users_city'),
        'user_state'=>$PMDR->getLanguage('admin_users_state'),
        'user_country'=>$PMDR->getLanguage('admin_users_country'),
        'user_zip'=>$PMDR->getLanguage('admin_users_zipcode'),
        'user_phone'=>$PMDR->getLanguage('admin_users_phone'),
        'user_fax'=>$PMDR->getLanguage('admin_users_fax'),
        'user_comment'=>$PMDR->getLanguage('admin_users_comments')
    );
    if($PMDR->getConfig('user_display_name')) {
        $user_search_fields['display_name'] = $PMDR->getLanguage('admin_users_display_name');
    }
    $users_search_fields = array_merge($users_search_fields,$db->GetAssoc("SELECT CONCAT('custom_',f.id) AS id, name FROM ".T_FIELDS." f INNER JOIN ".T_FIELDS_GROUPS." fg ON f.group_id=fg.id WHERE fg.type='users' ORDER BY f.ordering"));

    $form_search->addField('field','select',array('label'=>$PMDR->getLanguage('admin_users_search_in'),'fieldset'=>'user_search','value'=>$_GET['field'],'options'=>$users_search_fields));
    $form_search->addField('keyword','text',array('label'=>$PMDR->getLanguage('admin_users_search_for'),'fieldset'=>'user_search','value'=>$_GET['keyword']));
    $form_search->addField('group_id','select',array('label'=>$PMDR->getLanguage('admin_users_search_in_group'),'fieldset'=>'user_search','value'=>$_GET['group_id'],'first_option'=>array(''=>'All'),'options'=>$db->GetAssoc("SELECT id, name FROM ".T_USERS_GROUPS)));
    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_search',$form_search);

    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_users_id'),true);
    $table_list->addColumn('login',$PMDR->getLanguage('admin_users_username'),true);
    $table_list->addColumn('user_first_name',$PMDR->getLanguage('admin_users_first_name'),true);
    $table_list->addColumn('user_last_name',$PMDR->getLanguage('admin_users_last_name'),true);
    $table_list->addColumn('user_organization',$PMDR->getLanguage('admin_users_organization'),true);
    $table_list->addColumn('user_email',$PMDR->getLanguage('admin_users_email'),true);
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));

    $order_checkbox_options = array(
        ''=>'',
        'send_email'=>$PMDR->getLanguage('admin_users_send_email'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$order_checkbox_options)));

    if(isset($_GET['keyword'])) {
        if(isset($_GET['compare_type']) AND $_GET['compare_type'] == 'equal') {
            $where[] = $db->CleanIdentifier($_GET['field'])." = ".$PMDR->get('Cleaner')->clean_db($_GET['keyword']);
        } else {
            $where[] = $db->CleanIdentifier($_GET['field'])." LIKE ".$PMDR->get('Cleaner')->clean_db($_GET['keyword']."%");
        }
    }
    if(isset($_GET['keyword2'])) {
        if(isset($_GET['compare_type']) AND $_GET['compare_type'] == 'equal') {
            $where[] = $db->CleanIdentifier($_GET['field2'])." = ".$PMDR->get('Cleaner')->clean_db($_GET['keyword2']);
        } else {
            $where[] = $db->CleanIdentifier($_GET['field2'])." LIKE ".$PMDR->get('Cleaner')->clean_db($_GET['keyword2']."%");
        }
    }
    $user_group_join = '';
    if($_GET['group_id'] != '') {
        $user_group_join = "INNER JOIN ".T_USERS_GROUPS_LOOKUP." ug ON u.id=ug.user_id";
        $where[] = "ug.group_id = ".$PMDR->get('Cleaner')->clean_db($_GET['group_id']);
    }
    $order_join = '';
    if($_GET['status'] == 'no_order') {
        $order_join = "LEFT JOIN ".T_ORDERS." o ON u.id=o.user_id";
        $where[] = "o.order_id IS NULL";
    }
    if($_GET['status'] == 'this_week') {
        $where[] = 'u.created > DATE_SUB(NOW(),INTERVAL 7 DAY)';
    }
    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    }

    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("
        SELECT SQL_CALC_FOUND_ROWS u.*
        FROM ".T_USERS." u $user_group_join $order_join
        $where
        ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'login ASC')."
        LIMIT ?,?",array($paging->limit1,$paging->limit2)
    );
    $table_list->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['login'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['login']).'</a>';
        $records[$key]['user_first_name'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['user_first_name']).'</a>';
        $records[$key]['user_last_name'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['user_last_name']).'</a>';
        $records[$key]['user_organization'] = $PMDR->get('Cleaner')->clean_output($record['user_organization']);
        $records[$key]['user_email'] = '<a href="admin_email_send.php?template=new&user_id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['user_email']).'</a>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('users',array('href'=>'admin_users_summary.php?id='.$record['id'],'label'=>'Summary'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('page_add',array('href'=>'admin_orders_add.php?user_id='.$record['id'],'label'=>'Add New Order'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('page',array('href'=>'admin_orders.php?user_id='.$record['id'],'label'=>'Orders'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('login_lock_arrow',array('target'=>'_blank','href'=>BASE_URL.MEMBERS_FOLDER.'index.php?user_login_field=login&user_login='.$PMDR->get('Cleaner')->clean_output($record['login']),'label'=>$PMDR->getLanguage('admin_users_login')));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_users'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_users_edit');

    $form = $PMDR->getNew('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('user_details',array('legend'=>$PMDR->getLanguage('admin_users_information')));
    $form->addFieldSet('address',array('legend'=>$PMDR->getLanguage('admin_users_address')));
    $form->addFieldSet('notifications',array('legend'=>$PMDR->getLanguage('admin_users_notifications')));
    $form->addFieldSet('groups',array('legend'=>$PMDR->getLanguage('admin_users_groups')));
    $form->addFieldSet('comments',array('legend'=>$PMDR->getLanguage('admin_users_comments')));
    if($_GET['action'] == 'edit') $form->addField('id','custom',array('label'=>$PMDR->getLanguage('admin_users_id'),'fieldset'=>'user_details'));
    $form->addField('login','text',array('label'=>$PMDR->getLanguage('admin_users_username'),'fieldset'=>'user_details'));
    $form->addField('pass','password',array('label'=>$PMDR->getLanguage('admin_users_password'),'fieldset'=>'user_details','strength'=>true,'strength_label'=>$PMDR->getLanguage('admin_users_strength'),'plaintext'=>true,'generate'=>true));
    $form->addField('user_email','text',array('label'=>$PMDR->getLanguage('admin_users_email'),'fieldset'=>'user_details'));
    if($PMDR->getConfig('user_display_name')) {
        $form->addField('display_name','text',array('label'=>$PMDR->getLanguage('admin_users_display_name'),'fieldset'=>'user_details'));
        $form->addValidator('display_name',new Validate_NonEmpty());
    }
    $form->addField('user_first_name','text',array('label'=>$PMDR->getLanguage('admin_users_first_name'),'fieldset'=>'user_details'));
    $form->addField('user_last_name','text',array('label'=>$PMDR->getLanguage('admin_users_last_name'),'fieldset'=>'user_details'));
    $form->addField('user_organization','text',array('label'=>$PMDR->getLanguage('admin_users_organization'),'fieldset'=>'user_details'));
    $form->addField('profile_image','file',array('fieldset'=>'user_details','options'=>array('url_allow'=>true)));
    $form->addFieldNote('profile_image',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('profile_image_size')));
    if($_GET['action'] == 'edit') {
        if($image_url = get_file_url(PROFILE_IMAGES_PATH.$_GET['id'].'.*')) {
            $form->addField('current_profile_image','custom',array('label'=>$PMDR->getLanguage('admin_users_current_image'),'fieldset'=>'user_details','html'=>'<img src="'.$image_url.'">'));
            $form->addField('delete_profile_image','checkbox',array('label'=>$PMDR->getLanguage('admin_users_profile_image_delete'),'fieldset'=>'user_details'));
        }
    }
    if(!$PMDR->getConfig('disable_billing')) {
        $form->addField('disable_overdue_notices','checkbox',array('fieldset'=>'user_details'));
        $form->addField('tax_exempt','checkbox',array('fieldset'=>'user_details'));
    }
    $form->addField('moderate_disable','checkbox',array('fieldset'=>'user_details'));
    if($email_lists = $db->GetAssoc("SELECT id, title FROM ".T_EMAIL_LISTS)) {
        $form->addField('email_lists','checkbox',array('fieldset'=>'user_details','value'=>$db->GetCol("SELECT id FROM ".T_EMAIL_LISTS." WHERE optout=1"),'options'=>$email_lists));
    }
    unset($email_lists);
    $form->addField('timezone','select',array('label'=>$PMDR->getLanguage('admin_users_timezone'),'fieldset'=>'user_details','first_option'=>'','options'=>include(PMDROOT.'/includes/timezones.php')));
    if($_GET['action'] == 'add') {
        $form->addField('send_registration_email','checkbox',array('fieldset'=>'user_details'));
    }

    $form->addField('user_address1','text',array('label'=>$PMDR->getLanguage('admin_users_address1'),'fieldset'=>'address'));
    $form->addField('user_address2','text',array('label'=>$PMDR->getLanguage('admin_users_address2'),'fieldset'=>'address'));
    $form->addField('user_city','text',array('label'=>$PMDR->getLanguage('admin_users_city'),'fieldset'=>'address'));
    $form->addField('user_state','text',array('label'=>$PMDR->getLanguage('admin_users_state'),'fieldset'=>'address'));
    $form->addField('user_state_select','select',array('label'=>$PMDR->getLanguage('admin_users_state'),'fieldset'=>'address','first_option'=>'','options'=>get_states_array()));
    $form->addField('user_country','select',array('label'=>$PMDR->getLanguage('admin_users_country'),'fieldset'=>'address','first_option'=>'','value'=>$PMDR->getConfig('user_default_country'),'options'=>get_countries_array()));
    $form->addField('user_zip','text',array('label'=>$PMDR->getLanguage('admin_users_zipcode'),'fieldset'=>'address'));
    $form->addField('user_phone','text',array('label'=>$PMDR->getLanguage('admin_users_phone'),'fieldset'=>'address'));
    $form->addField('user_fax','text',array('label'=>$PMDR->getLanguage('admin_users_fax'),'fieldset'=>'address'));
    $form->addField('signature','textarea',array('fieldset'=>'user_details'));
    $form->addField('user_comment','textarea',array('label'=>$PMDR->getLanguage('admin_users_comments'),'fieldset'=>'comments'));

    $form->addField('favorites_notify','checkbox',array('label'=>$PMDR->getLanguage('admin_users_favorites_notify'),'fieldset'=>'notifications'));

    $user_groups = $db->GetAssoc("SELECT id, name FROM ".T_USERS_GROUPS);
    $user_attributes = array('label'=>$PMDR->getLanguage('admin_users_groups'),'fieldset'=>'groups','value'=>'','options'=>$user_groups);
    $form->addField('user_groups','checkbox',$user_attributes);

    if(!value($_GET,'addorder')) {
        $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'
            ,'onclick'=>"confirm('Are you sure?')"
        ));
    }
    if($_GET['action'] == 'add') {
        $form->addField('submit_order','submit',array('label'=>$PMDR->getLanguage('admin_users_submit_add_order'),'fieldset'=>'submit'));
    }

    $fields = $PMDR->get('Fields')->addToForm($form,'users',array('fieldset'=>'user_details'));

    $form->addValidator('login',new Validate_NonEmpty());
    $form->addValidator('login',new Validate_Username());
    $form->addValidator('user_groups',new Validate_NonEmpty());
    $form->addValidator('user_email',new Validate_Email());
    $form->addValidator('user_email',new Validate_NonEmpty());
    $form->addValidator('profile_image',new Validate_Image($PMDR->getConfig('profile_image_width'),$PMDR->getConfig('profile_image_height'),$PMDR->getConfig('profile_image_size'),$PMDR->getConfig('profile_image_types'),true));

    if($_GET['action'] == 'edit') {
        $template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_users_edit.tpl');

        $template_content->set('title',$PMDR->getLanguage('admin_users_profile'));

        if(!$PMDR->get('Authentication')->checkPermission('admin_users_groups_edit',false)) {
            $user_attributes['disabled'] = 'disabled';
        }

        $user = $PMDR->get('User',$_GET['id']);

        $template_content->set('users_summary_header',$user->getAdminSummaryHeader('profile'));

        $form->loadValues($user->data);

        $form->setFieldAttribute('pass','value','');

        $form->setFieldAttribute('user_groups','value', $current_groups = $db->GetCol("SELECT group_id FROM ".T_USERS_GROUPS_LOOKUP." WHERE user_id=?",array($_GET['id'])));
        $form->setFieldAttribute('email_lists','value', $current_lists = $db->GetCol("SELECT list_id FROM ".T_EMAIL_LISTS_LOOKUP." WHERE user_id=?",array($_GET['id'])));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_users_add'));
        $form->addValidator('pass',new Validate_NonEmpty());

        // If adding a user and we do not have permissions to edit user groups, make sure no gorups with admin login are available
        if(!$PMDR->get('Authentication')->checkPermission('admin_users_groups_edit',false)) {
            $form->setFieldAttribute('user_groups','options',$db->GetAssoc("SELECT g.id, g.name FROM ".T_USERS_GROUPS." g WHERE g.id NOT IN(SELECT group_id FROM ".T_USERS_GROUPS_PERMISSIONS_LOOKUP." gl WHERE gl.permission_id='admin_login')"));
        }
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_order')) {
        $data = $form->loadValues();
        if($_GET['action'] == 'add') {
            if($db->GetRow("SELECT id FROM ".T_USERS." WHERE user_email=?",array($data['user_email']))) {
                $form->addError($PMDR->getLanguage('admin_users_email_exists'),'user_email');
            }
            if($db->GetRow("SELECT id FROM ".T_USERS." WHERE login=?",array($data['login']))) {
                $form->addError($PMDR->getLanguage('admin_users_username_exists'),'login');
            }
        } elseif($_GET['action'] == 'edit') {
            if($db->GetRow("SELECT id FROM ".T_USERS." WHERE user_email=? AND id!=?",array($data['user_email'],$_GET['id']))) {
                $form->addError($PMDR->getLanguage('admin_users_email_exists'),'user_email');
            }
            if($db->GetRow("SELECT id FROM ".T_USERS." WHERE login=? AND id!=?",array($data['login'],$_GET['id']))) {
                $form->addError($PMDR->getLanguage('admin_users_username_exists'),'login');
            }
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $user_id = $PMDR->get('Users')->insert($data);
                if($data['send_registration_email']) {
                    $PMDR->get('Email_Templates')->send('user_registration',array('to'=>$data['user_email'],'variables'=>array('user_password'=>$data['pass']),'user_id'=>$user_id));
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['login'],$PMDR->getLanguage('admin_users'))),'insert');
                if($form->wasSubmitted('submit_order')) {
                    redirect('admin_orders_add.php',array('user_id'=>$user_id));
                } else {
                    redirect('admin_users_summary.php',array('id'=>$user_id));
                }
            } elseif($_GET['action'] == 'edit') {
                // If we don't have permission, use same user groups (to prevent cirumventing the "disabled" setting on the fields)
                if(!$PMDR->get('Authentication')->checkPermission('admin_users_groups_edit',false)) {
                    $data['user_groups'] = $current_groups;
                    $data['email_lists'] = $current_lists;
                }
                $PMDR->get('Users')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['login'],$PMDR->getLanguage('admin_users'))),'update');
                redirect(null,array('action'=>'edit','id'=>$_GET['id']));
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}
if(!isset($_GET['id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>