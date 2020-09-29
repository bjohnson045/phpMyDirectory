<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_jobs','admin_listings','admin_users','email_templates'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if(!$listing) {
        redirect();
    } else {
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('jobs'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Jobs')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_jobs'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if($_GET['action'] == 'approve') {
    $db->Execute("UPDATE ".T_JOBS." SET status='active' WHERE id=?",array($_GET['id']));
    $PMDR->get('Email_Templates')->send('jobs_approved',array('job_id'=>$_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_jobs_job'))),'update');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_jobs'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_jobs_id'));
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_jobs_listing_id'));
    }
    $table_list->addColumn('title',$PMDR->getLanguage('admin_jobs_title'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_jobs_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $where_sql = '';
    $where = array();
    if(isset($_GET['listing_id'])) {
        $where[] = 'listing_id='.$db->Clean($_GET['listing_id']);
    }
    if(count($where)) {
        $where_sql = 'WHERE '.implode(' AND ',$where);
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_JOBS." $where_sql ORDER BY title LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= '<a href="'.URL_NOQUERY.'?action=approve&id='.$record['id'].'&listing_id='.$record['listing_id'].'"><i class="text-success fa fa-check"></i></a> ';
        }
        $records[$key]['manage'] .= '<a target="_blank" href="'.$PMDR->get('Jobs')->getURL($record['id'],$record['friendly_url']).'"><i class="fa fa-eye"></i></a> ';
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addField('title','text');
    $form->addField('friendly_url','text');
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'pending'=>$PMDR->getLanguage('pending')
    );
    $form->addField('status','select',array('options'=>$statuses));
    $form->addField('categories','select_multiple',array('options'=>$db->GetAssoc("SELECT id, title FROM ".T_JOBS_CATEGORIES." ORDER BY title")));
    $form->addJavascript('title','onblur','$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){if($(\'#friendly_url\').val()==\'\'){$(\'#friendly_url\').val(text_rewrite);}}});');
    $types = array(
        'fulltime'=>$PMDR->getLanguage('admin_jobs_type_fulltime'),
        'parttime'=>$PMDR->getLanguage('admin_jobs_type_parttime'),
        'contract'=>$PMDR->getLanguage('admin_jobs_type_contract'),
        'commission'=>$PMDR->getLanguage('admin_jobs_type_commission'),
        'temporary'=>$PMDR->getLanguage('admin_jobs_type_temporary'),
        'seasonal'=>$PMDR->getLanguage('admin_jobs_type_seasonal'),
        'internship'=>$PMDR->getLanguage('admin_jobs_type_internship'),
        'other'=>$PMDR->getLanguage('admin_jobs_type_other'),
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
    $form->addField('meta_title','text');
    $form->addField('meta_keywords','text');
    $form->addField('meta_description','textarea');
    $PMDR->get('Fields')->addToForm($form,'jobs');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_Friendly_URL());
    $form->addValidator('website',new Validate_URL(false));
    $form->addValidator('email',new Validate_Email(false));

    $form->addField('listing_id','hidden',array('value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_jobs_edit'));
        $job = $PMDR->get('Jobs')->getRow($_GET['id']);
        $form->loadValues($job);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_jobs_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_JOBS." WHERE listing_id=?",array($_GET['listing_id']));

        if($count >= $listing['jobs_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('admin_jobs_limit_exceeded'));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $data['user_id'] = $listing['user_id'];
                $PMDR->get('Jobs')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_jobs'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                if($data['status'] == 'active' AND $edit_event['status'] == 'pending') {
                    $PMDR->get('Email_Templates')->send('jobs_approved',array('event_id'=>$_GET['id']));
                }
                $PMDR->get('Jobs')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_jobs'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>