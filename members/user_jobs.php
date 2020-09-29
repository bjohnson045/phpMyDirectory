<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->loadLanguage(array('user_jobs','user_listings','email_templates'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_jobs.tpl');

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if(isset($_GET['id'])) {
    $job = $PMDR->get('Jobs')->getRow($_GET['id']);
    $listing = $PMDR->get('Listings')->getRow($job['listing_id']);
    if($user['id'] != $job['user_id']) {
        redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
    }
}

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if($user['id'] != $listing['user_id']) {
        redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
    }
}

if(isset($listing)) {
    $PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('jobs'));
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_jobs'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_jobs'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($_GET['action'] == 'delete') {
    $PMDR->get('Jobs')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_jobs'))),'delete');
    redirect(array('listing_id'=>$listing['id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_jobs'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_jobs.php?action=add&listing_id='.$listing['id']);
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_jobs_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_jobs_title'));
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('user_jobs_listing_id'));
    }
    $table_list->addColumn('date',$PMDR->getLanguage('user_jobs_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS j.* FROM ".T_JOBS." j WHERE listing_id=? ORDER BY title ASC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        $records[$key]['url'] = $PMDR->get('Jobs')->getURL($record['id'],$record['friendly_url']);
    }
    //print_array($records);
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_jobs_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addField('title','text');
    $form->addField('categories','select_multiple',array('options'=>$db->GetAssoc("SELECT id, title FROM ".T_JOBS_CATEGORIES." ORDER BY title")));
    $types = array(
        'fulltime'=>$PMDR->getLanguage('user_jobs_type_fulltime'),
        'parttime'=>$PMDR->getLanguage('user_jobs_type_parttime'),
        'contract'=>$PMDR->getLanguage('user_jobs_type_contract'),
        'commission'=>$PMDR->getLanguage('user_jobs_type_commission'),
        'temporary'=>$PMDR->getLanguage('user_jobs_type_temporary'),
        'seasonal'=>$PMDR->getLanguage('user_jobs_type_seasonal'),
        'internship'=>$PMDR->getLanguage('user_jobs_type_internship'),
        'other'=>$PMDR->getLanguage('user_jobs_type_other'),
    );
    $form->addField('type','select',array('first_option'=>'','options'=>$types));
    $form->addField('description_short','textarea');
    $form->addField('description','textarea');
    $form->addField('requirements','textarea');
    $form->addField('compensation','text');
    $form->addField('benefits','text');
    $form->addField('website','text');
    $form->addField('email','text');
    $form->addField('phone','text');
    $form->addField('contact_name','text');
    $form->addField('keywords','text');
    $fields = $PMDR->get('Fields')->addToForm($form,'jobs');
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('website',new Validate_URL(false));
    $form->addValidator('email',new Validate_Email(false));

    $form->addField('listing_id','hidden',array('value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $PMDR->set('page_title',$PMDR->getLanguage('user_jobs_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_jobs_edit'));
        $edit_job = $PMDR->get('Jobs')->getRow($_GET['id']);
        $form->loadValues($edit_job);
    } else {
        $PMDR->set('page_title',$PMDR->getLanguage('user_jobs_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_jobs_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $jobs_count = $PMDR->get('Jobs')->getListingJobsCount($listing['id']);

        if($jobs_count >= $listing['jobs_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_jobs_limit_exceeded'));
        }

        $data['friendly_url'] = Strings::rewrite($data['title']);

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                if($PMDR->getConfig('jobs_status') == 'pending') {
                    $data['status'] = 'pending';
                } else {
                    $data['status'] = 'active';
                }
                $data['user_id'] = $listing['user_id'];
                $job_id = $PMDR->get('Jobs')->insert($data);
                $PMDR->get('Email_Templates')->send('admin_jobs_new',array('job_id'=>$job_id));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_jobs'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Jobs')->update($data,$_GET['id']);
                $PMDR->get('Email_Templates')->send('admin_jobs_edit',array('job_id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_jobs'))),'update');
                redirect(array('listing_id'=>$listing['id']));
            }
        }
    }
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>