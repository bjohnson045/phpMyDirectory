<?php
define('PMD_SECTION','members');

include('../defaults.php');

// Verify the user is logged in
$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

// Load language variables
$PMDR->loadLanguage(array('user_classifieds','user_orders'));

// Load required CSS and javascript
$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', closeOnContentClick: true, gallery:{enabled:true} });});</script>',20);

// Get the user details from the session user ID
$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

// Get the listing details from the listing ID
$listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);

// Set up the page title and breadcrumbs
$PMDR->setAdd('page_title',$PMDR->getLanguage('user_classifieds'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_classifieds'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

// If the user ID does not match the listing user ID redirect as this could be a security issue
if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

// If the action is to delete a classified
if($_GET['action'] == 'delete') {
    if($db->GetRow("SELECT id FROM ".T_CLASSIFIEDS." WHERE id=? AND listing_id=? LIMIT 1",array($_GET['id'],$listing['id']))) {
        $PMDR->get('Classifieds')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_classifieds'))),'delete');
    }
    redirect(array('listing_id'=>$listing['id']));
}

// Load the template that will be used to display this page
$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_classifieds.tpl');

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('classifieds'));

// If an action is not set, display a list of classifieds
if(!isset($_GET['action'])) {
    // Set the page title
    $template_content->set('title',$PMDR->getLanguage('user_classifieds'));

    // Set the classifieds add URL
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_classifieds.php?action=add&listing_id='.$listing['id']);

    // Create the table list that will be used to display the classifieds
    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_classifieds_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_classifieds_id'));
    $table_list->addColumn('product_image',$PMDR->getLanguage('user_classifieds_image'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_classifieds_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('user_classifieds_description'));
    $table_list->addColumn('price',$PMDR->getLanguage('user_classifieds_price'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));

    // Get a paging Object that will be used to display paging information
    $paging = $PMDR->get('Paging');

    // Get the classifieds from the database
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS o.*, GROUP_CONCAT(oi.id,'.',oi.extension SEPARATOR ',') AS images FROM ".T_CLASSIFIEDS." o LEFT JOIN ".T_CLASSIFIEDS_IMAGES." oi ON o.id=oi.classified_id WHERE listing_id=? GROUP BY o.id ORDER BY date DESC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));

    // Load the number of found records into the paging class to determine the page number details
    $paging->setTotalResults($db->FoundRows());

    // Loop through the records to maniuplate the results before being displayed
    foreach($records as $key=>$record) {
        // Escape the title and description
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);

        // Get the images for the classified
        $images = array_filter(explode(',',$record['images']));
        $records[$key]['images'] = array();
        foreach($images as $image_key=>$image) {
            $records[$key]['images'][] = array(
                'url'=>get_file_url(CLASSIFIEDS_PATH.$record['id'].'-'.$image),
                'url_thumbnail'=>get_file_url(CLASSIFIEDS_THUMBNAILS_PATH.$record['id'].'-'.$image),
                'hidden'=>($image_key == 0 ? 0 : 1)
            );
        }
        // Format the price according to the currency settings
        $records[$key]['price'] = format_number_currency($record['price']);
    }
    // Add the records and paging information to the table list
    $table_list->addRecords($records);
    $table_list->addPaging($paging);

    // Render the table list and send to the template
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_classifieds_form.tpl');

    // Set up the form for adding/editing classifieds
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('classified_details',array('legend'=>$PMDR->getLanguage('user_classifieds_classified')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_classifieds_title'),'fieldset'=>'classified_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('user_classifieds_description'),'fieldset'=>'classified_details'));
    $form->addField('price','text',array('label'=>$PMDR->getLanguage('user_classifieds_price'),'fieldset'=>'classified_details'));
    $form->addField('expire_date','datetime',array('label'=>$PMDR->getLanguage('user_classifieds_expire_date'),'fieldset'=>'classified_details'));
    $form->addField('www','text',array('label'=>$PMDR->getLanguage('user_classifieds_view_link'),'fieldset'=>'classified_details'));
    $form->addField('buttoncode','text',array('label'=>$PMDR->getLanguage('user_classifieds_purchase_link'),'fieldset'=>'classified_details'));
    if($listing['classifieds_images_allow']) {
        // Display 5 image input fields
        for($x = 1; $x <= 5; $x++) {
            $form->addField('classified_image'.$x,'file',array('label'=>$PMDR->getLanguage('user_classifieds_image').' '.$x,'fieldset'=>'classified_details'));
            // Validate each image input field to ensure it is a valid image file
            $form->addValidator('classified_image'.$x,new Validate_Image($PMDR->getConfig('classified_image_width'),$PMDR->getConfig('classified_image_height'),$PMDR->getConfig('classified_image_size'),explode(',',$PMDR->getConfig('classifieds_images_formats'))));
        }
    }
    $fields = $PMDR->get('Fields')->addToForm($form,'classifieds',array('fieldset'=>'classified_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));

    // Validate the fields in the form when submitted
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('title',new Validate_Banned_Words());
    $form->addValidator('description',new Validate_Banned_Words());

    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('user_classifieds_listing_id'),'fieldset'=>'classified_details','value'=>$listing['id']));

    // If a classified is being edited
    if($_GET['action'] == 'edit') {
        $PMDR->setAdd('page_title',$PMDR->getLanguage('user_classifieds_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_classifieds_edit'));
        // Load the classified content and add to the form
        $edit_classified = $PMDR->get('Classifieds')->getRow($_GET['id']);
        $form->loadValues($edit_classified);
        // Get the current images for teh classified
        if($listing['classifieds_images_allow']) {
            if($current_images = $db->GetAll("SELECT * FROM ".T_CLASSIFIEDS_IMAGES." WHERE classified_id=?",array($edit_classified['id']))) {
                foreach($current_images as $image) {
                    $current_images_ids[$image['id']] = $PMDR->getLanguage('user_classifieds_delete_image');
                    $current_images_array[] = '<a href="'.get_file_url(CLASSIFIEDS_PATH.$edit_classified['id'].'-'.$image['id'].'.'.$image['extension']).'" class="image_group" title=""><img src="'.get_file_url(CLASSIFIEDS_THUMBNAILS_PATH.$edit_classified['id'].'-'.$image['id'].'.'.$image['extension']).'"></a><br /><br />';
                }
                // Add delete checkboxes for all current images
                $form->addField('delete_images','checkbox',array('label'=>$PMDR->getLanguage('user_classifieds_current_images'),'fieldset'=>'classified_details','options'=>$current_images_ids,'html'=>$current_images_array));
            }
        }
    } else {
        $PMDR->setAdd('page_title',$PMDR->getLanguage('user_classifieds_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_classifieds_add'));
    }

    // If the add/edit form was submitted
    if($form->wasSubmitted('submit')) {
        // Load the form values
        $data = $form->loadValues();
        // Rewrite the title into a friendly URL
        $data['friendly_url'] = Strings::rewrite($data['title']);

        // Get the current count of classifieds for this listing and ensure it does not go over the limit
        $classifieds_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_CLASSIFIEDS." WHERE listing_id=?",array($listing['id']));
        if($classifieds_count >= $listing['classifieds_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_classifieds_limit'));
        }

        // Check to see if the form validates
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            // Add or edit the listing details with the information submitted
            if($_GET['action']=='add') {
                $PMDR->get('Classifieds')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_classifieds'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Classifieds')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_classifieds'))),'update');
                redirect(array('listing_id'=>$listing['id']));
            }
        }
    }
    // Render the form to HTML and send to the template
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>