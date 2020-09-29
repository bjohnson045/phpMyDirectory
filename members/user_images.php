<?php
define('PMD_SECTION','members');

include ('../defaults.php');

$PMDR->loadLanguage(array('user_images','user_orders'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_images'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_images'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

$PMDR->loadJavascript('<script type="text/javascript" src="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.CDN_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', closeOnContentClick: true });});</script>',20);

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

if($_GET['action'] == 'delete') {
    if($db->GetRow("SELECT id FROM ".T_IMAGES." WHERE id=? AND listing_id=? LIMIT 1",array($_GET['id'],$listing['id']))) {
        $PMDR->get('Images')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_images'))),'delete');
    }
    redirect(array('listing_id'=>$listing['id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'/members/user_images.tpl');

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('images'));

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_images'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_images.php?action=add&listing_id='.$listing['id']);

    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_images_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_images_id'));
    $table_list->addColumn('image',$PMDR->getLanguage('user_images_image'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_images_title'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('user_images_order'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_IMAGES." WHERE listing_id=? ORDER BY date DESC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $records[$key]['url'] = get_file_url(IMAGES_PATH.$record['id'].'.'.$record['extension']);
        $records[$key]['url_thumbnail'] = get_file_url(IMAGES_THUMBNAILS_PATH.$record['id'].'.'.$record['extension']).'?'.Strings::random(10);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_images_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('image_details',array('legend'=>$PMDR->getLanguage('user_images_image')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_images_title'),'fieldset'=>'image_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('user_images_description'),'fieldset'=>'image_details'));
    $form->addField('image','file',array('label'=>$PMDR->getLanguage('user_images_image'),'fieldset'=>'image_details'));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('user_images_order'),'fieldset'=>'image_details'));
    $fields = $PMDR->get('Fields')->addToForm($form,'images',array('fieldset'=>'image_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('image',
        new Validate_Image(
            $PMDR->getConfig('gallery_image_width'),
            $PMDR->getConfig('gallery_image_height'),
            $PMDR->getConfig('gallery_image_size'),
            explode(',',$PMDR->getConfig('images_formats')),
            false,
            false,
            ($_GET['action'] != 'edit')
        )
    );
    $form->addValidator('ordering',new Validate_Numeric_Range(0,50000));

    $form->addField('listing_id','hidden',array('label'=>'Listing ID','fieldset'=>'image_details','value'=>$listing['id']));

    if($_GET['action'] == 'edit') {
        $PMDR->set('page_title',$PMDR->getLanguage('user_images_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_images_edit'));
        $edit_image = $PMDR->get('Images')->getRow($_GET['id']);
        $form->loadValues($edit_image);
        $form->addField('preview','custom',array('label'=>$PMDR->getLanguage('user_images_current_image'),'fieldset'=>'image_details','html'=>'<a href="'.get_file_url_cdn(IMAGES_PATH.$edit_image['id'].'.'.$edit_image['extension']).'" class="image_group" title="'.$edit_image['title'].'"><img src="'.get_file_url(IMAGES_THUMBNAILS_PATH.$edit_image['id'].'.'.$edit_image['extension']).'"></a>'));
    } else {
        $PMDR->set('page_title',$PMDR->getLanguage('user_images_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_images_add'));
    }

    $form->addValidator('title',new Validate_Banned_Words());
    $form->addValidator('description',new Validate_Banned_Words());

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $image_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_IMAGES." WHERE listing_id=?",array($listing['id']));

        if($image_count >= $listing['images_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_images_limit'));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Images')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_images'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Images')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_images'))),'update');
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