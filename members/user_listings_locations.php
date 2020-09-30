<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->loadLanguage(array('user_listings','email_templates','general_locations'));

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_listings_locations.tpl');

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

if(isset($_GET['id'])) {
    $location = $PMDR->get('Listings')->getLocation($_GET['id']);
    if($location) {
        $listing = $PMDR->get('Listings')->getRow($location['listing_id']);
    } else {
        redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
    }
}

if(!$listing) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
}

if(!$listing OR $user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('locations'));

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_listings_locations'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_locations'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($_GET['action'] == 'delete') {
    $PMDR->get('Listings')->deleteLocation($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_listings_locations'))),'delete');
    redirect(array('listing_id'=>$listing['id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_listings_locations'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_listings_locations.php?action=add&listing_id='.$listing['id']);
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_listings_locations_list.tpl'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_listings_locations_title'));
    $table_list->addColumn('formatted',$PMDR->getLanguage('user_listings_locations_formatted'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS ll.* FROM ".T_LISTINGS_LOCATIONS." ll WHERE listing_id=? LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_listings_locations_form.tpl');

    $location_count = $PMDR->get('Locations')->getCount();

    $form = $PMDR->get('Form');
    $form->addFieldSet('address_details',array('legend'=>'Address Details'));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_listings_locations_title'),'fieldset'=>'address_details'));
    $form->addField('address1','text',array('label'=>$PMDR->getLanguage('user_listings_locations_address1'),'fieldset'=>'address_details'));
    $form->addField('address2','text',array('label'=>$PMDR->getLanguage('user_listings_locations_address2'),'fieldset'=>'address_details'));

    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'address_details','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'address_details','options'=>array('type'=>'location_tree','search'=>true)));
        }
        $form->addValidator('location_id',new Validate_NonEmpty());
    } else {
        $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('user_listings_location'),'fieldset'=>'address_details','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
    }
    if($PMDR->getConfig('location_text_1')) {
        $form->addField('location_text_1','text',array('label'=>$PMDR->getLanguage('general_locations_text_1'),'fieldset'=>'address_details'));
        $form->addValidator('location_text_1',new Validate_NonEmpty());
    }
    if($PMDR->getConfig('location_text_2')) {
        $form->addField('location_text_2','text',array('label'=>$PMDR->getLanguage('general_locations_text_2'),'fieldset'=>'address_details'));
        $form->addValidator('location_text_2',new Validate_NonEmpty());
    }
    if($PMDR->getConfig('location_text_3')) {
        $form->addField('location_text_3','text',array('label'=>$PMDR->getLanguage('general_locations_text_3'),'fieldset'=>'address_details'));
        $form->addValidator('location_text_3',new Validate_NonEmpty());
    }
    $form->addField('zip','text',array('label'=>$PMDR->getLanguage('user_listings_locations_zip'),'fieldset'=>'address_details'));
    $form->addField('phone','text',array('label'=>$PMDR->getLanguage('user_listings_locations_phone'),'fieldset'=>'address_details'));
    $form->addField('url','text',array('label'=>$PMDR->getLanguage('user_listings_locations_url'),'fieldset'=>'address_details'));
    $form->addField('email','text',array('label'=>$PMDR->getLanguage('user_listings_locations_email'),'fieldset'=>'address_details'));

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('address1',new Validate_NonEmpty());
    $form->addValidator('location_id',new Validate_NonEmpty());
    $form->addValidator('zip',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $PMDR->set('page_title',$PMDR->getLanguage('user_listings_locations_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_locations_edit'));
        $form->loadValues($location);
    } else {
        $PMDR->set('page_title',$PMDR->getLanguage('user_listings_locations_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_listings_locations_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $locations_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($listing['id']));

        if($locations_count >= $listing['locations_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_listings_locations_limit_exceeded'));
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $location_id = $PMDR->get('Listings')->insertLocation($data,$listing['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_listings_locations'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Listings')->updateLocation($data,$location['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_listings_locations'))),'update');
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