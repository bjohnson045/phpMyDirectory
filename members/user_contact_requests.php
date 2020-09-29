<?php
define('PMD_SECTION', 'members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_contact_requests','email_templates'));

$PMDR->get('Authentication')->authenticate();

if(!intval($PMDR->getConfig('contact_requests_limit'))) {
    $PMDR->addMessage('error','The contact request limit is set to 0, therefore contact requests are disabled.');
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_contact_requests'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_contact_requests.php','text'=>$PMDR->getLanguage('user_contact_requests')));

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_contact_requests.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_contact_requests_list.tpl'));
    $table_list->addColumn('message');
    $table_list->addColumn('status');
    $table_list->addColumn('date_requested');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS id, user_id, message, status, date_requested FROM ".T_CONTACT_REQUESTS." WHERE user_id=?",array($user['id']));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['date_requested'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_requested']);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('user_contact_requests'));
    $template_content->set('content',$table_list->render());
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_contact_requests.php?action=add');
} elseif($_GET['action'] == 'add') {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_contact_requests_form.tpl');

    $category_count = $PMDR->get('Categories')->getCount();
    $location_count = $PMDR->get('Locations')->getCount();

    $form = $PMDR->getNew('Form');
    $form->addFieldSet('contact_request',array('legend'=>$PMDR->getLanguage('user_contact_requests_add')));

    if(empty($user['user_first_name'])) {
        $form->addField('first_name','text',array('label'=>$PMDR->getLanguage('user_contact_requests_first_name')));
    } else {
        $form->addField('first_name','custom',array('label'=>$PMDR->getLanguage('user_contact_requests_first_name'),'html'=>$user['user_first_name']));
    }
    if(empty($user['user_last_name'])) {
        $form->addField('last_name','text',array('label'=>$PMDR->getLanguage('user_contact_requests_last_name')));
    } else {
        $form->addField('last_name','custom',array('label'=>$PMDR->getLanguage('user_contact_requests_last_name'),'html'=>$user['user_last_name']));
    }
    if(!$PMDR->getConfig('contact_requests_messages')) {
        $form->addField('email','custom',array('label'=>$PMDR->getLanguage('user_contact_requests_email')));
        if(empty($user['user_phone'])) {
            $form->addField('phone','text',array('label'=>$PMDR->getLanguage('user_contact_requests_phone')));
            $form->addValidator('phone',new Validate_NonEmpty());
        } else {
            $form->addField('phone','custom',array('label'=>$PMDR->getLanguage('user_contact_requests_phone'),'html'=>$user['user_phone']));
        }
        $form->setFieldAttribute('email','value',$user['user_email']);

        $available_options = array(
            'anytime'=>$PMDR->getLanguage('user_contact_requests_anytime'),
            'morning'=>$PMDR->getLanguage('user_contact_requests_morning'),
            'lunch'=>$PMDR->getLanguage('user_contact_requests_lunch'),
            'afternoon'=>$PMDR->getLanguage('user_contact_requests_afternoon'),
            'evening'=>$PMDR->getLanguage('user_contact_requests_evening'),
            'weekend'=>$PMDR->getLanguage('user_contact_requests_weekend')
        );
        $form->addField('available','select',array('label'=>$PMDR->getLanguage('user_contact_requests_available'),'options'=>$available_options));
        $preferred_contact = array(
            'either'=>$PMDR->getLanguage('user_contact_requests_either'),
            'phone'=>$PMDR->getLanguage('user_contact_requests_phone'),
            'email'=>$PMDR->getLanguage('user_contact_requests_email')
        );
        $form->addField('preferred_contact','select',array('label'=>$PMDR->getLanguage('user_contact_requests_preferred_contact'),'options'=>$preferred_contact));
    }
    if($category_count > 1) {
        if($PMDR->getConfig('category_select_type') == 'tree_select') {
            $form->addField('categories','tree_select',array('label'=>$PMDR->getLanguage('user_contact_requests_categories'),'value'=>$_GET['category'],'first_option'=>'','options'=>$PMDR->get('Categories')->getSelect(array('closed'=>0))));
        } elseif($PMDR->getConfig('category_select_type') == 'tree_select_cascading') {
            $form->addField('categories','tree_select_cascading',array('label'=>$PMDR->getLanguage('user_contact_requests_categories'),'value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0)));
        } else {
            $form->addField('categories','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('user_contact_requests_categories'),'value'=>$_GET['category'],'options'=>array('type'=>'category_tree','hidden'=>0)));
        }
    } else {
        $form->addField('categories','hidden',array('value'=>$PMDR->get('Categories')->getOneID()));
    }
    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_contact_requests_location_id'),'value'=>$_GET['location'],'first_option'=>'','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 AND hidden=0 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_contact_requests_location_id'),'value'=>$_GET['location'],'options'=>array('type'=>'location_tree','hidden'=>0)));
        }
    } else {
        $form->addField('location_id','hidden',array('value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
    }

    $form->addField('message','textarea',array('label'=>$PMDR->getLanguage('user_contact_requests_message'),'counter'=>500));

    if($PMDR->getConfig('captcha_logged_in')) {
        $form->addField('security_code','security_image',array('label'=>$PMDR->getLanguage('user_contact_requests_security_code')));
        $form->addValidator('security_code',new Validate_Captcha());
    }

    $fields = $PMDR->get('Fields')->addToForm($form,'contact_requests',array('fieldset'=>'contact_request'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit')));

    $form->addValidator('message',new Validate_NonEmpty());
    $form->addValidator('categories',new Validate_NonEmpty());

    $PMDR->get('Plugins')->run_hook('contact_request_form');

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $PMDR->get('Plugins')->run_hook('contact_request_submit');

        if(intval($PMDR->getConfig('contact_requests_limit')) <= $db->GetOne("SELECT COUNT(*) FROM ".T_CONTACT_REQUESTS." WHERE user_id=? AND date_requested > DATE_SUB(NOW(),INTERVAL 1 HOUR)",array($user['id']))) {
            $form->addError($PMDR->getLanguage('user_contact_requests_limit_reached',array(intval($PMDR->getConfig('contact_requests_limit')))));
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Plugins')->run_hook('contact_request_submit_success');

            $update_parts = array();
            if(!empty($data['first_name'])) {
                $update_parts[] = 'user_first_name='.$db->Clean($data['first_name']);
            }
            if(!empty($data['last_name'])) {
                $update_parts[] = 'user_last_name='.$db->Clean($data['last_name']);
            }
            if(!empty($data['phone'])) {
                $update_parts[] = 'user_phone='.$db->Clean($data['phone']);
            }
            if(count($update_parts)) {
                $db->Execute("UPDATE ".T_USERS." SET ".implode(',',$update_parts)." WHERE id=?",array($user['id']));
            }

            $db->Execute("INSERT INTO ".T_CONTACT_REQUESTS." (user_id,available,preferred_contact,message,categories,location_id,status,date_requested) VALUES
            (?,?,?,?,?,?,?,NOW())",array($user['id'],$data['available'],$data['preferred_contact'],$data['message'],$data['categories'],$data['location_id'],'pending'));

            $contact_request_id = $db->Insert_ID();

            $PMDR->get('Email_Templates')->send('admin_contact_request_submitted',array('user_id'=>$user['id'],'variables'=>array('contact_request_id'=>$contact_request_id)));

            $PMDR->addMessage('success',$PMDR->getLanguage('user_contact_requests_submitted'),'insert');
            redirect();
        }
    }
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>