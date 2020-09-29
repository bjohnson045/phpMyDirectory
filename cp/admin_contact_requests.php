<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_contact_requests','admin_users','admin_users_merge','admin_messages','email_templates'));

$PMDR->get('Authentication')->authenticate();

if(!intval($PMDR->getConfig('contact_requests_limit'))) {
    $PMDR->addMessage('notice','The <a href="admin_settings.php?group=listings&varname=contact_request_limit">contact request limit</a> is set to 0, therefore contact requests are disabled.  Increase this limit to enable contact requests.');
}

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_CONTACT_REQUESTS." WHERE id=?",array($_GET['id']));
    $db->Execute("UPDATE ".T_MESSAGES." SET contact_request_id=NULL WHERE contact_request_id=?",array($_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_contact_requests'))),'delete');
    redirect();
}

if($_GET['action'] == 'approve') {
    $contact_request = $db->GetRow("SELECT id, user_id FROM ".T_CONTACT_REQUESTS." WHERE id=?",array($_GET['id']));
    $db->Execute("UPDATE ".T_CONTACT_REQUESTS." SET status='approved' WHERE id=? AND status='pending'",array($contact_request['id']));
    $PMDR->get('Email_Templates')->send('contact_request_approved',array('user_id'=>$contact_request['user_id'],'variables'=>array('contact_request_id'=>$contact_request['id'])));
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_contact_requests_approved',$contact_request['id']),'update');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id');
    $table_list->addColumn('user');
    $table_list->addColumn('status');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS c.*, CONCAT(COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login)) AS user FROM ".T_CONTACT_REQUESTS." c LEFT JOIN ".T_USERS." u ON c.user_id=u.id ORDER BY c.id LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['status'] = $PMDR->getLanguage('admin_contact_requests_'.$record['status']);
        if(is_null($record['user_id'])) {
            $records[$key]['user'] = $record['name'].' ('.$record['email'].')';
        } else {
            $records[$key]['user'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">'.$record['user'].'</a> (ID: '.$record['user_id'].')';
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('checkmark',array('label'=>$PMDR->getLanguage('admin_contact_requests_approve'),'href'=>URL_NOQUERY.'?action=approve&id='.$record['id']));
        }
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_contact_requests'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('contact_request',array('legend'=>$PMDR->getLanguage('admin_contact_requests_request')));

    $category_count = $PMDR->get('Categories')->getCount();
    $location_count = $PMDR->get('Locations')->getCount();

    if($_GET['action'] == 'edit') {
        $request = $db->GetRow("SELECT c.*, CONCAT(COALESCE(NULLIF(TRIM(CONCAT(user_first_name,' ',user_last_name)),''),login)) AS user, user_email, user_phone FROM ".T_CONTACT_REQUESTS." c LEFT JOIN ".T_USERS." u ON c.user_id=u.id WHERE c.id=?",array($_GET['id']));
    }

    $form->addField('name','custom',array('html'=>'<a href="admin_users_summary.php?id='.$request['user_id'].'">'.$request['user'].'</a>'));
    $form->addField('email','custom',array('html'=>$request['user_email']));
    $form->addField('phone','custom',array('html'=>$request['user_phone']));

    $available_options = array(
        'anytime'=>$PMDR->getLanguage('admin_contact_requests_anytime'),
        'morning'=>$PMDR->getLanguage('admin_contact_requests_morning'),
        'lunch'=>$PMDR->getLanguage('admin_contact_requests_lunch'),
        'afternoon'=>$PMDR->getLanguage('admin_contact_requests_afternoon'),
        'evening'=>$PMDR->getLanguage('admin_contact_requests_evening'),
        'weekend'=>$PMDR->getLanguage('admin_contact_requests_weekend')
    );
    $form->addField('available','select',array('options'=>$available_options));
    $preferred_contact = array(
        'either'=>$PMDR->getLanguage('admin_contact_requests_either'),
        'phone'=>$PMDR->getLanguage('admin_contact_requests_phone'),
        'email'=>$PMDR->getLanguage('admin_contact_requests_email')
    );
    $form->addField('preferred_contact','select',array('options'=>$preferred_contact));

    if($category_count > 1) {
        if($PMDR->getConfig('category_select_type') == 'tree_select') {
            $form->addField('categories','tree_select',array('value'=>$_GET['category'],'first_option'=>'','options'=>$PMDR->get('Categories')->getSelect()));
        } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading') {
            $form->addField('categories','tree_select_cascading',array('value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0)));
        } else {
            $form->addField('categories','tree_select_expanding_radio',array('value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0)));
        }
    } else {
        $form->addField('categories','hidden',array('value'=>$PMDR->get('Categories')->getOneID()));
    }
    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('value'=>$_GET['location'],'first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 AND hidden=0 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('value'=>$_GET['location'],'options'=>array('type'=>'location_tree','hidden'=>0)));
        }
    } else {
        $form->addField('location_id','hidden',array('value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
    }

    $form->addField('message','textarea',array('counter'=>500));
    $fields = $PMDR->get('Fields')->addToForm($form,'contact_requests',array('fieldset'=>'contact_request'));
    $form->addField('submit','submit');
    if($_GET['action'] == 'edit' AND $request['status'] == 'pending') {
        $form->addField('submit_approve','submit',array('label'=>$PMDR->getLanguage('admin_contact_requests_approve'),'class'=>'btn-success'));
    }
    $form->addValidator('categories',new Validate_NonEmpty());
    $form->addValidator('message',new Validate_NonEmpty());

    if($request['status'] == 'processed' OR $request['status'] == 'approved') {
        $form->setFieldAttribute('submit','disabled','disabled');
    }

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_contact_requests_edit'));
        $contact_request = $db->GetRow("SELECT * FROM ".T_CONTACT_REQUESTS." WHERE id=?",array($_GET['id']));
        $form->loadValues($contact_request);
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_approve')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'edit' AND $request['status'] == 'pending') {
                if($form->wasSubmitted('submit_approve')) {
                    $db->Execute("UPDATE ".T_CONTACT_REQUESTS." SET status='approved' WHERE id=?",array($_GET['id']));
                    $PMDR->get('Email_Templates')->send('contact_request_approved',array('user_id'=>$contact_request['user_id'],'variables'=>array('contact_request_id'=>$_GET['id'])));
                }

                $db->Execute("UPDATE ".T_CONTACT_REQUESTS." SET available=?, preferred_contact=?, categories=?, location_id=?, message=? WHERE id=?",
                array($data['available'],$data['preferred_contact'],$data['categories'],$data['location_id'],$data['message'],$_GET['id']));

                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_contact_requests_request'))),'update');
                redirect();
            }
        }
    } elseif($_GET['action'] == 'edit' AND ($request['status'] == 'processed' OR $request['status'] == 'approved')) {
        $PMDR->addMessage('notice',$PMDR->getLanguage('admin_contact_requests_noedit'));
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_users_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>