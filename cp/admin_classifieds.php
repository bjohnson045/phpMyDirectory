<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_classifieds','admin_listings','admin_users'));

$PMDR->get('Authentication')->checkPermission('admin_listings_view');

$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', closeOnContentClick: true });});</script>',20);

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if(!$listing) {
        redirect();
    } else {
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('classifieds'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_delete');
    $PMDR->get('Classifieds')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_classifieds'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_classifieds'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_classifieds_id'));
    $table_list->addColumn('product_image',$PMDR->getLanguage('admin_classifieds_image'));
    if(empty($listing)) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_classifieds_listing_id'));
    }
    $table_list->addColumn('title',$PMDR->getLanguage('admin_classifieds_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_classifieds_description'));
    $table_list->addColumn('price',$PMDR->getLanguage('admin_classifieds_price'));
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
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS o.*, GROUP_CONCAT(oi.id,'.',oi.extension SEPARATOR ',') AS images FROM ".T_CLASSIFIEDS." o LEFT JOIN ".T_CLASSIFIEDS_IMAGES." oi ON o.id=oi.classified_id $where_sql GROUP BY o.id ORDER BY date DESC LIMIT ?,?",$where_variables);
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $images = explode(',',$record['images']);
        foreach($images as $image_key=>$image) {
            $records[$key]['product_image'] .= '<a href="'.get_file_url(CLASSIFIEDS_PATH.$record['id'].'-'.$image,true).'" class="image_group"'.($image_key == 0 ? '' : 'style="display: none"').'" title="'.$record['title'].'" rel="image_group'.$record['id'].'"><img src="'.get_file_url(CLASSIFIEDS_THUMBNAILS_PATH.$record['id'].'-'.$image,true).'"></a>';
        }
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $records[$key]['price'] = format_number_currency($PMDR->get('Cleaner')->clean_output($record['price']));
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} elseif(!isset($_GET['primary_category_id']) AND $_GET['action'] != 'edit') {
    $template_content->set('title',$PMDR->getLanguage('admin_classifieds_add'));
    $category_count = $PMDR->get('Classifieds_Categories')->getCount();
    if($category_count > 1) {
        $form = $PMDR->getNew('Form');
        $form->addFieldSet('classified',array('legend'=>$PMDR->getLanguage('admin_classifieds_categories_primary_category_id')));
        $PMDR->get('Classifieds_Categories')->addPrimaryCategoryField($form,$PMDR->getLanguage('admin_classifieds_category'),'classified');
    } elseif($category_count == 1) {
        redirect(array('listing_id'=>$_GET['listing_id'],'action'=>$_GET['action'],'primary_category_id'=>$PMDR->get('Classifieds_Categories')->getOneID()));
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_listings_add_category_error'));
        redirect(BASE_URL_ADMIN.'/admin_classifieds.php');
    }

    $form->addValidator('primary_category_id',new Validate_NonEmpty());
    $form->addField('submit_category','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    if($form->wasSubmitted('submit_category')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            redirect(null,array('action'=>'add','listing_id'=>$_GET['listing_id'],'primary_category_id'=>$data['primary_category_id']));
        }
    }

    $template_content->set('content',$form->toHTML());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_listings_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('product_details',array('legend'=>$PMDR->getLanguage('admin_classifieds_classified')));
    $form->addField('date','datetime',array('label'=>'Date','fieldset'=>'product_details','value'=>$PMDR->get('Dates')->dateTimeNow()));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_classifieds_title'),'fieldset'=>'product_details'));
    $form->addField('friendly_url','text',array('label'=>'Friendly URL','fieldset'=>'product_details'));
    $form->addJavascript('title','onblur','$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){if($(\'#friendly_url\').val()==\'\'){$(\'#friendly_url\').val(text_rewrite);}}});');
    $PMDR->get('Classifieds_Categories')->addPrimaryCategoryField($form,$PMDR->getLanguage('admin_classifieds_categories_primary_category_id'),'product_details');
    $PMDR->get('Classifieds_Categories')->addCategoriesField($form,$PMDR->getLanguage('admin_classifeids_categories_additional_categories'),'product_details',5);
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_classifieds_description'),'fieldset'=>'product_details'));
    $form->addField('price','currency',array('label'=>$PMDR->getLanguage('admin_classifieds_price'),'fieldset'=>'product_details','value'=>'0.00'));
    $form->addField('expire_date','datetime',array('label'=>$PMDR->getLanguage('admin_classifieds_expire_date'),'fieldset'=>'product_details'));
    $form->addField('www','text',array('label'=>$PMDR->getLanguage('admin_classifieds_view_link'),'fieldset'=>'product_details'));
    $form->addField('buttoncode','text',array('label'=>$PMDR->getLanguage('admin_classifieds_purchase_link'),'fieldset'=>'product_details'));
    $form->addField('keywords','text',array('label'=>'Keywords','fieldset'=>'product_details'));
    $form->addField('meta_title','text',array('label'=>'Meta Title','fieldset'=>'product_details'));
    $form->addField('meta_keywords','text',array('label'=>'Meta Keywords','fieldset'=>'product_details'));
    $form->addField('meta_description','textarea',array('label'=>'Meta Description','fieldset'=>'product_details'));
    for($x = 1; $x <= 5; $x++) {
        $form->addField('classified_image'.$x,'file',array('label'=>$PMDR->getLanguage('admin_classifieds_image').' '.$x,'fieldset'=>'product_details'));
        $form->addValidator('classified_image'.$x,new Validate_Image($PMDR->getConfig('classified_image_width'),$PMDR->getConfig('classified_image_height'),$PMDR->getConfig('classified_image_size'),explode(',',$PMDR->getConfig('classifieds_images_formats'))));
        $form->addFieldNote('classified_image'.$x,$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('classified_image_size')));
    }
    $PMDR->get('Fields')->addToForm($form,'classifieds',array('fieldset'=>'product_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('friendly_url',new Validate_Friendly_URL());
    $form->addValidator('expire_date',new Validate_DateTime(false));
    $form->addValidator('date',new Validate_DateTime(true));

    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_classifieds_listing_id'),'fieldset'=>'product_details','value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_classifieds_edit'));
        $edit_classified = $PMDR->get('Classifieds')->getRow($_GET['id']);
        $edit_classified['categories'] = $db->GetCol("SELECT category_id FROM ".T_CLASSIFIEDS_CATEGORIES_LOOKUP." WHERE classified_id=? AND category_id!=?",array($edit_classified['id'],$edit_classified['primary_category_id']));
        $form->loadValues($edit_classified);
        $current_images = $db->GetAll("SELECT * FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=?",array($_GET['id']));
        if(count($current_images)) {
            foreach($current_images as $image) {
                $current_images_ids[$image['id']] = $PMDR->getLanguage('admin_classifieds_delete_image');
                $current_images_array[] = '<a href="'.get_file_url(CLASSIFIEDS_PATH.$edit_classified['id'].'-'.$image['id'].'.'.$image['extension'],true).'" class="image_group" title="" rel="image_group"><img src="'.get_file_url(CLASSIFIEDS_THUMBNAILS_PATH.$edit_classified['id'].'-'.$image['id'].'.'.$image['extension'],true).'"></a><br />';
            }
            $form->addField('delete_images','checkbox',array('label'=>$PMDR->getLanguage('admin_classifieds_current_images'),'fieldset'=>'product_details','options'=>$current_images_ids,'html'=>$current_images_array));
        }
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_classifieds_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $classifieds_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($_GET['listing_id']));

        if($classifieds_count >= $listing['classifieds_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('admin_classifieds_limit_exceeded'));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Classifieds')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_classifieds'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Classifieds')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_classifieds'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_classifieds_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>