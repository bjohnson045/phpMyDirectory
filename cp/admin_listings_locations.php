<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_listings','admin_users','general_locations'));

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if(!$listing) {
        redirect();
    } else {
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('locations'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Listings')->deleteLocation($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_listings_location'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_listings_locations'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_listings_title'));
    $table_list->addColumn('address',$PMDR->getLanguage('admin_listings_locations_address'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $where_sql = '';
    if(isset($listing)) {
        $where[] = 'listing_id=?';
        $where_variables[] = $listing['id'];
    }
    if(count($where)) {
        $where_sql = 'WHERE '.implode(' AND ',$where);
    }
    $where_variables[] = $paging->limit1;
    $where_variables[] = $paging->limit2;
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_LISTINGS_LOCATIONS." $where_sql ORDER BY title DESC LIMIT ?,?",$where_variables);
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['address'] = Strings::nl2br_replace($PMDR->get('Cleaner')->clean_output($record['formatted']));
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_listings_edit');

    $location_count = $PMDR->get('Locations')->getCount();

    $form = $PMDR->get('Form');
    $form->addFieldSet('address_details',array('legend'=>$PMDR->getLanguage('admin_listings_locations_address_details')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_listings_title'),'fieldset'=>'address_details'));
    $form->addField('address1','text',array('label'=>$PMDR->getLanguage('admin_listings_address1'),'fieldset'=>'address_details'));
    $form->addField('address2','text',array('label'=>$PMDR->getLanguage('admin_listings_address2'),'fieldset'=>'address_details'));

    if($location_count > 1) {
        if($PMDR->getConfig('location_select_type') == 'tree_select' OR $PMDR->getConfig('location_select_type') == 'tree_select_group') {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'address_details','options'=>$db->GetAssoc("SELECT id, title, level, left_, right_ FROM ".T_LOCATIONS." WHERE ID != 1 ORDER BY left_")));
        } else {
            $form->addField('location_id',$PMDR->getConfig('location_select_type'),array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'address_details','options'=>array('type'=>'location_tree','search'=>true)));
        }
        $form->addValidator('location_id',new Validate_NonEmpty());
    } else {
        $form->addField('location_id','hidden',array('label'=>$PMDR->getLanguage('admin_listings_location'),'fieldset'=>'address_details','value'=>$db->GetOne("SELECT id FROM ".T_LOCATIONS." WHERE id!=1")));
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
    $form->addField('zip','text',array('label'=>$PMDR->getLanguage('admin_listings_zip_code'),'fieldset'=>'address_details'));
    $form->addField('phone','text',array('label'=>$PMDR->getLanguage('admin_listings_phone'),'fieldset'=>'address_details'));
    $form->addField('url','text',array('label'=>$PMDR->getLanguage('admin_listings_website'),'fieldset'=>'address_details'));
    $form->addField('email','text',array('label'=>$PMDR->getLanguage('admin_listings_email'),'fieldset'=>'address_details'));

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('address1',new Validate_NonEmpty());
    $form->addValidator('zip',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_listings_locations_edit'));
        $form->loadValues($PMDR->get('Listings')->getLocation($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_listings_locations_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        $data['url'] = standardize_url($data['url']);
        $location_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_LISTINGS_LOCATIONS." WHERE listing_id=?",array($_GET['listing_id']));
        if($location_count >= $listing['locations_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('admin_listings_locations_limit',$listing['locations_limit']));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Listings')->insertLocation($data,$listing['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_listings_location'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Listings')->updateLocation($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_listings_location'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>