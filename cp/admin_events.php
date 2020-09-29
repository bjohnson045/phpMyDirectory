<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_events','admin_listings','admin_users','email_templates'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_events.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if(!$listing) {
        redirect();
    } else {
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('events'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Events')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_events'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if($_GET['action'] == 'approve') {
    $PMDR->get('Events')->activate($_GET['id']);
    $PMDR->get('Email_Templates')->send('events_approved',array('event_id'=>$_GET['id']));
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_events_event'))),'update');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $form_search = $PMDR->getNew('Form');
    $form_search->method = 'GET';
    $form_search->addFieldSet('event_search',array('legend'=>$PMDR->getLanguage('admin_events_search')));
    $status_options = array(
        'active'=>$PMDR->getLanguage('active'),
        'pending'=>$PMDR->getLanguage('pending')
    );
    $form_search->addField('status','select',array('first_option'=>'','value'=>$_GET['status'],'options'=>$status_options,'help'=>''));
    $form_search->addField('keywords','text',array('value'=>$_GET['keywords'],'help'=>''));
    $form_search->addField('category','select',array('first_option'=>'','value'=>$_GET['category'],'options'=>$PMDR->get('Events')->getCategoriesSelect()));
    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form_search->addField('action','hidden',array('value'=>'search'));
    $template_content->set('form_search',$form_search);

    $template_content->set('title',$PMDR->getLanguage('admin_events'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_events_id'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_events_title'));
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_events_listing_id'));
    }
    $table_list->addColumn('date',$PMDR->getLanguage('admin_events_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $where = array();
    $where_dates = array();
    if(isset($_GET['listing_id'])) {
        $where[] = 'listing_id='.$db->Clean($_GET['listing_id']);
    }
    /*
    if(isset($_GET['date_start'])) {
        $where_dates[] = 'ed.date_start >= '.$db->Clean($_GET['date_start']);
    }
    if(isset($_GET['date_end'])) {
        $where_dates[] = 'ed.date_end <= '.$db->Clean($_GET['date_end']);
    }
    */
    if(!empty($_GET['status'])) {
        $where[] = "status = ".$PMDR->get('Cleaner')->clean_db($_GET['status']);
    }
    if(!empty($_GET['keywords'])) {
        $where[] = "MATCH(title,keywords,description_short) AGAINST (".$PMDR->get('Cleaner')->clean_db($_GET['keywords']).")";
    }
    if(!empty($_GET['category'])) {
        $category_join = 'INNER JOIN '.T_EVENTS_CATEGORIES_LOOKUP.' ecl ON e.id=ecl.event_id';
        $where[] = "ecl.category_id = ".$PMDR->get('Cleaner')->clean_db($_GET['category']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    if(count($where_dates)) {
        $where_dates = 'WHERE '.implode(' AND ',$where_dates);
    } else {
        $where_dates = ' WHERE ed.date_start >= NOW()';
    }
    /*
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS 
        events.*, l.title AS listing_title, l.id AS listing_id 
        FROM (
            SELECT ev.* FROM (
                SELECT e.id, e.title, e.listing_id, e.status, e.friendly_url, ed.date_start, ed.date_end 
                FROM ".T_EVENTS." e 
                LEFT JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id $category_join $where 
                ORDER BY ed.date_start >= NOW() DESC, ABS(NOW() - ed.date_start) ASC
            ) ev 
            GROUP BY ev.id LIMIT ?,?
        ) AS events 
        LEFT JOIN ".T_LISTINGS." l ON events.listing_id=l.id",array($paging->limit1,$paging->limit2)
    );
    
    //$category_join $where
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS 
        events.*, l.title AS listing_title, l.id AS listing_id 
        FROM (
            SELECT e.id, e.title, e.listing_id, e.status, e.friendly_url, ed.date_start, ed.date_end 
            FROM ".T_EVENTS." e
            LEFT JOIN (SELECT event_id, MIN(date_start) date_start FROM ".T_EVENTS_DATES." $where_dates GROUP BY event_id) edd ON edd.event_id=e.id
            $category_join $where
            LIMIT ?,?
        ) as events
        LEFT JOIN ".T_LISTINGS." l ON events.listing_id=l.id",array($paging->limit1,$paging->limit2)
    );
    */
                           /*
                            LEFT JOIN (SELECT classified_id, MIN(id) id FROM ".T_CLASSIFIEDS_IMAGES." GROUP BY classified_id) cii ON cii.classified_id=c.id
                        INNER JOIN ".T_CLASSIFIEDS_IMAGES." ci on ci.id=cii.id*/
        //$category_join $where
    /*
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS 
        events.*, l.title AS listing_title, l.id AS listing_id 
        FROM (
            SELECT e.id, e.title, e.listing_id, e.status, e.friendly_url, edd.date_start, edd.date_end 
            FROM ".T_EVENTS." e
            LEFT JOIN (SELECT event_id, date_start, date_end FROM ".T_EVENTS_DATES." $where_dates GROUP BY event_id) edd ON edd.event_id=e.id
            $category_join
            LIMIT ?,?
        ) as events
        LEFT JOIN ".T_LISTINGS." l ON events.listing_id=l.id",array($paging->limit1,$paging->limit2)
    );
    */
    
    //SELECT SQL_CALC_FOUND_ROWS events.*, l.title AS listing_title, l.id AS listing_id FROM ( SELECT e.id, e.title, e.listing_id, e.status, e.friendly_url, edd.date_start, edd.date_end FROM pmd_events e LEFT JOIN ( SELECT edo.* FROM ( SELECT event_id, date_start, date_end FROM pmd_events_dates ORDER BY date_start >= NOW() DESC, ABS(NOW() - date_start) ASC ) edo GROUP BY edo.event_id ) edd ON edd.event_id=e.id LIMIT 0,10 ) as events LEFT JOIN pmd_listings l ON events.listing_id=l.id
    
    
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS 
        events.*, l.title AS listing_title, l.id AS listing_id 
        FROM (
            SELECT e.id, e.title, e.listing_id, e.status, e.friendly_url, edd.date_start, edd.date_end 
            FROM ".T_EVENTS." e
            LEFT JOIN (
                SELECT edo.* FROM (
                    SELECT event_id FROM ".T_EVENTS_DATES." $where_dates ORDER BY date_start >= NOW() DESC, ABS(NOW() - date_start) ASC
                ) edo 
                GROUP BY edo.event_id
                LEFT JOIN (
                    SELECT date_start, date_end FROM ".T_EVENTS_DATES." ed ON edo.event_id=ed.id
                ) edd ON edd.
            ) eddc ON eddc.event_id=e.id
            $category_join
            LIMIT ?,?
        ) as events
        LEFT JOIN ".T_LISTINGS." l ON events.listing_id=l.id",array($paging->limit1,$paging->limit2)
    );
    */                    

                        
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_start']).' - '.$PMDR->get('Dates_Local')->formatDateTime($record['date_end']);
        $records[$key]['listing_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_events.php?listing_id='.$record['listing_id'].'">'.$record['listing_title'].'</a>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        if($record['status'] == 'pending') {
            $records[$key]['manage'] .= '<a href="'.URL_NOQUERY.'?action=approve&id='.$record['id'].'&listing_id='.$record['listing_id'].'"><i class="text-success fa fa-check"></i></a> ';
        }
        $records[$key]['manage'] .= '<a target="_blank" href="'.$PMDR->get('Events')->getURL($record['id'],$record['friendly_url']).'"><i class="fa fa-eye"></i></a> ';
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    if(!$listing) {
        $form->addField('listing_id','select_window',array('options'=>'select_listing'));
    } else {
        $form->addField('listing_id','hidden',array('value'=>$listing['id']));
    }
    $form->addField('title','text');
    $form->addField('friendly_url','text');
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'pending'=>$PMDR->getLanguage('pending')
    );
    $form->addField('status','select',array('options'=>$statuses));
    $form->addField('categories','select_multiple',array('options'=>$PMDR->get('Events')->getCategoriesSelect()));
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
    $form->addField('recurring','checkbox');
    $repeats_options = array(
        'daily'=>$PMDR->getLanguage('admin_events_daily'),
        'weekly'=>$PMDR->getLanguage('admin_events_weekly'),
        'monthly'=>$PMDR->getLanguage('admin_events_monthly'),
        'yearly'=>$PMDR->getLanguage('admin_events_yearly'),
    );
    $form->addField('recurring_end','datetime');
    $form->addDependency('recurring_end',array('type'=>'display','field'=>'recurring','value'=>'1'));
    $form->addField('recurring_type','select',array('options'=>$repeats_options));
    $form->addDependency('recurring_type',array('type'=>'display','field'=>'recurring','value'=>'1'));
    $form->addField('recurring_interval','select',array('options'=>array_combine(range(1,30,1),range(1,30,1))));
    $form->addDependency('recurring_interval',array('type'=>'display','field'=>'recurring','value'=>'1'));

    $days = $PMDR->get('Dates_Local')->getWeekDays(true);
    $form->addField('recurring_days','checkbox',array('options'=>$days,'implode'=>true,'implode_character'=>','));
    $form->addDependency('recurring_days',array('type'=>'display','field'=>'recurring_type','value'=>'weekly'));
    $monthly_type = array(
        'day'=>$PMDR->getLanguage('admin_events_monthly_type_day'),
        'week'=>$PMDR->getLanguage('admin_events_monthly_type_week'),
    );
    $form->addField('recurring_monthly','radio',array('label'=>'Repeat by','options'=>$monthly_type));
    $form->addDependency('recurring_monthly',array('type'=>'display','field'=>'recurring_type','value'=>'monthly'));

    $form->addField('color','color',array('predefined'=>true));
    $form->addField('allow_rsvp','checkbox');
    $form->addField('website','text');
    $form->addField('email','text');
    $form->addField('phone','text');
    $form->addField('contact_name','text');
    $form->addField('admission','text');
    $form->addField('description_short','textarea');
    $form->addField('description','textarea');
    $form->addField('venue','text');
    $form->addField('location','textarea');
    $form->addField('latitude','text');
    $form->addField('longitude','text');
    $form->addPicker('longitude','coordinates',null,array('label'=>$PMDR->getLanguage('admin_events_select_coordinates')));
    $form->addField('recalculate_coordinates','checkbox',array('label'=>$PMDR->getLanguage('admin_events_recalculate_coordinates'),'help'=>$PMDR->getLanguage('admin_listings_recalculate_coordinates_help')));
    $form->addField('keywords','text');
    $form->addField('meta_title','text');
    $form->addField('meta_keywords','text');
    $form->addField('meta_description','textarea');
    $PMDR->get('Fields')->addToForm($form,'events');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_Friendly_URL());
    $form->addValidator('date_start',new Validate_DateTime(true));
    $form->addValidator('date_end',new Validate_DateTime(false));
    $form->addValidator('website',new Validate_URL(false));
    $form->addValidator('email',new Validate_Email(false));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_events_edit'));
        $edit_event = $PMDR->get('Events')->getRow($_GET['id']);
        $form->loadValues($edit_event);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_events_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if($_GET['action'] == 'add' AND isset($listing)) {
            $events_count = $PMDR->get('Events')->getListingCount($listing['id']);
            if($events_count >= $listing['events_limit'] AND $_GET['action'] != 'edit') {
                $form->addError($PMDR->getLanguage('admin_events_limit_exceeded'));
            }
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($data['recalculate_coordinates']) {
                $map = $PMDR->get('Map');
                if($coordinates = $map->getGeocode($data['location'])) {
                    if(abs($coordinates['lat']) > 0 AND abs($coordinates['lon']) > 0) {
                        $data['latitude'] = $coordinates['lat'];
                        $data['longitude'] = $coordinates['lon'];
                    }
                } else {
                    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_events_recalculate_coordinates_error'));
                }
            }

            if($_GET['action']=='add') {
                if($listing) {
                    $data['user_id'] = $listing['user_id'];
                } else {
                    $data['user_id'] = $PMDR->get('Session')->get('admin_id');
                }
                $PMDR->get('Events')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_events'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                if($data['status'] == 'active' AND $edit_event['status'] == 'pending') {
                    $PMDR->get('Email_Templates')->send('events_approved',array('event_id'=>$_GET['id']));
                }
                $PMDR->get('Events')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_events'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_events_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>