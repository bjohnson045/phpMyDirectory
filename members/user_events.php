<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->loadLanguage(array('user_events','user_listings','email_templates'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_events.tpl');

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if(isset($_GET['id'])) {
    $event = $PMDR->get('Events')->getRow($_GET['id']);
    $listing = $PMDR->get('Listings')->getRow($event['listing_id']);
    if($user['id'] != $event['user_id']) {
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
    $PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('events'));
}

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_events'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_events'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($_GET['action'] == 'delete') {
    $PMDR->get('Events')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_events'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_events'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_events.php?action=add&listing_id='.$listing['id']);
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_events_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_events_title'));
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('user_events_listing_id'));
    }
    $table_list->addColumn('date',$PMDR->getLanguage('user_events_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("
        SELECT SQL_CALC_FOUND_ROWS ev.*
        FROM (
            SELECT e.id, e.title, e.friendly_url, e.listing_id, ed.date_start, ed.date_end
            FROM ".T_EVENTS." e
            INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id WHERE listing_id=?
            ORDER BY ed.date_start >= NOW() DESC, ABS(NOW() - ed.date_start) ASC
        ) ev
        GROUP BY ev.id
        LIMIT ?,?",
        array($listing['id'],$paging->limit1,$paging->limit2)
    );
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_start']).' - '.$PMDR->get('Dates_Local')->formatDateTime($record['date_end']);
        $records[$key]['url'] = $PMDR->get('Events')->getURL($record['id'],$record['friendly_url']);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_events_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addField('title','text');
    $form->addField('friendly_url','text');
    $form->addField('categories','select_multiple',array('options'=>$db->GetAssoc("SELECT id, title FROM ".T_EVENTS_CATEGORIES." ORDER BY title")));
    $form->addJavascript('title','onblur','$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){if($(\'#friendly_url\').val()==\'\'){$(\'#friendly_url\').val(text_rewrite);}}});');
    $form->addField('image','file',array('options'=>array('url_allow'=>true)));
    $form->addValidator('image',new Validate_Image($PMDR->getConfig('event_image_width'),$PMDR->getConfig('event_image_height'),$PMDR->getConfig('event_image_size'),explode(',',$PMDR->getConfig('event_images_formats'))));
    $form->addFieldNote('image',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('event_image_size')));
    if(isset($_GET['id']) AND $_GET['action'] == 'edit' AND $image = get_file_url(EVENT_IMAGES_THUMB_PATH.$_GET['id'].'.*',true)) {
        $form->addField('preview','custom',array('html'=>'<img class="img-thumbnail" src="'.$image.'">'));
        $form->addField('delete_image','checkbox',array('value'=>'0'));
    }
    $form->addField('date_start','datetime');
    $form->addField('date_end','datetime');
    $form->addField('recurring','checkbox',array('label'=>$PMDR->getLanguage('user_events_recurring')));
    $repeats_options = array(
        'daily'=>$PMDR->getLanguage('user_events_daily'),
        'weekly'=>$PMDR->getLanguage('user_events_weekly'),
        'monthly'=>$PMDR->getLanguage('user_events_monthly'),
        'yearly'=>$PMDR->getLanguage('user_events_yearly'),
    );
    $form->addField('recurring_end','datetime',array('label'=>$PMDR->getLanguage('user_events_recurring_end')));
    $form->addDependency('recurring_end',array('type'=>'display','field'=>'recurring','value'=>'1'));
    $form->addField('recurring_type','select',array('label'=>$PMDR->getLanguage('user_events_recurring_type'),'options'=>$repeats_options));
    $form->addDependency('recurring_type',array('type'=>'display','field'=>'recurring','value'=>'1'));
    $form->addField('recurring_interval','select',array('label'=>$PMDR->getLanguage('user_events_recurring_interval'),'options'=>array_combine(range(1,30,1),range(1,30,1))));
    $form->addDependency('recurring_interval',array('type'=>'display','field'=>'recurring','value'=>'1'));

    $days = $PMDR->get('Dates_Local')->getWeekDays(true);
    $form->addField('recurring_days','checkbox',array('label'=>$PMDR->getLanguage('user_events_recurring_days'),'options'=>$days,'implode'=>true,'implode_character'=>','));
    $form->addDependency('recurring_days',array('type'=>'display','field'=>'recurring_type','value'=>'weekly'));
    $monthly_type = array(
        'day'=>$PMDR->getLanguage('user_events_monthly_type_day'),
        'week'=>$PMDR->getLanguage('user_events_monthly_type_week')
    );
    $form->addField('recurring_monthly','radio',array('label'=>$PMDR->getLanguage('user_events_recurring_monthly'),'options'=>$monthly_type));
    $form->addDependency('recurring_monthly',array('type'=>'display','field'=>'recurring_type','value'=>'monthly'));

    $form->addField('color','color',array('label'=>$PMDR->getLanguage('user_events_color'),'predefined'=>true));
    $form->addField('allow_rsvp','checkbox',array('label'=>$PMDR->getLanguage('user_events_allow_rsvp')));
    $form->addField('website','text');
    $form->addField('email','text');
    $form->addField('phone','text');
    $form->addField('contact_name','text');
    $form->addField('admission','text');
    $form->addField('description_short','textarea');
    $form->addField('description','textarea');
    $form->addField('venue','text');
    $form->addField('location','textarea');
    $form->addField('keywords','text');
    $form->addField('meta_title','text');
    $form->addField('meta_keywords','text');
    $form->addField('meta_description','textarea');
    $fields = $PMDR->get('Fields')->addToForm($form,'events');
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_Friendly_URL());
    $form->addValidator('date_start',new Validate_DateTime(true));
    $form->addValidator('date_end',new Validate_DateTime(false));
    $form->addValidator('website',new Validate_URL(false));
    $form->addValidator('email',new Validate_Email(false));

    $form->addField('listing_id','hidden',array('value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $PMDR->set('page_title',$PMDR->getLanguage('user_events_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_events_edit'));
        $edit_event = $PMDR->get('Events')->getRow($_GET['id']);
        $form->loadValues($edit_event);
    } else {
        $PMDR->set('page_title',$PMDR->getLanguage('user_events_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_events_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $events_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_EVENTS." WHERE listing_id=?",array($listing['id']));

        if($events_count >= $listing['events_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_events_limit_exceeded'));
        }

        if(!$PMDR->get('Dates')->isZero($data['recurring_end'])) {
            $now = new DateTime();
            $now->add(new DateInterval('P'.$PMDR->getConfig('event_start_days').'D'));
            $start = new DateTime($data['date_start']);
            if($start > $now) {
                $form->addError($PMDR->getLanguage('user_events_date_start_error'),'date_start');
            }
            $start->add(new DateInterval('P3Y'));
            $end = new DateTime($data['recurring_end']);
            if($end > $start) {
                $form->addError($PMDR->getLanguage('user_events_recurring_end_error'),'recurring_end');
            }
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {

            if($_GET['action']=='add') {
                if($PMDR->getConfig('event_status') == 'pending') {
                    $data['status'] = 'pending';
                } else {
                    $data['status'] = 'active';
                }
                $data['user_id'] = $listing['user_id'];
                $event_id = $PMDR->get('Events')->insert($data);
                $PMDR->get('Email_Templates')->send('admin_events_new',array('event_id'=>$event_id));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_events'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Events')->update($data,$_GET['id']);
                $PMDR->get('Email_Templates')->send('admin_events_edit',array('event_id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_events'))),'update');
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