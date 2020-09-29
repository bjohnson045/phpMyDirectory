<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_images','admin_listings','admin_users'));

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
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('images'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_delete');
    $PMDR->get('Images')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_images'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_images'));
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('id',$PMDR->getLanguage('admin_images_id'));
    $table_list->addColumn('image',$PMDR->getLanguage('admin_images_image'));
    if(!$listing) {
        $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_images_listing_id'));
    }
    $table_list->addColumn('title',$PMDR->getLanguage('admin_images_title'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_images_ordering').' [<a href="" onclick="updateOrdering(\''.T_IMAGES.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $where = array();
    if($listing) {
        $where[] = 'i.listing_id = '.$db->Clean($listing['id']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_IMAGES." i $where ORDER BY i.ordering DESC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        if(file_exists(IMAGES_THUMBNAILS_PATH.$record['id'].'.'.$record['extension'])) {
            $record['image'] = '<a href="'.get_file_url(IMAGES_PATH.$record['id'].'.'.$record['extension'],true).'" title="'.$record['title'].' - '.$record['description'].'" class="image_group" rel="image_group"><img src="'.get_file_url(IMAGES_THUMBNAILS_PATH.$record['id'].'.'.$record['extension'],true).'"></a>';
        }
        $record['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_listings_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('image_details',array('legend'=>$PMDR->getLanguage('admin_images')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_images_title'),'fieldset'=>'image_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_images_description'),'fieldset'=>'image_details'));
    $form->addField('image','file',array('label'=>$PMDR->getLanguage('admin_images_image'),'fieldset'=>'image_details','options'=>array('url_allow'=>true)));
    $form->addFieldNote('image',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('gallery_image_size')));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_images_ordering'),'fieldset'=>'image_details'));
    $PMDR->get('Fields')->addToForm($form,'images',array('fieldset'=>'product_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('image',new Validate_Image($PMDR->getConfig('gallery_image_width'),$PMDR->getConfig('gallery_image_height'),$PMDR->getConfig('gallery_image_size'),explode(',',$PMDR->getConfig('images_formats')),false,false,$_GET['action'] == 'add'));
    $form->addValidator('ordering',new Validate_Numeric_Range(0,50000));

    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_images_listing_id'),'fieldset'=>'image_details','value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_images_edit'));
        $edit_image = $PMDR->get('Images')->getRow($_GET['id']);
        $form->loadValues($edit_image);
        if(file_exists(IMAGES_THUMBNAILS_PATH.$edit_image['id'].'.'.$edit_image['extension'])) {
            $form->addField('preview','custom',array('label'=>$PMDR->getLanguage('admin_images_current'),'fieldset'=>'image_details','value'=>'','options'=>'','html'=>'<a href="'.get_file_url(IMAGES_PATH.$edit_image['id'].'.'.$edit_image['extension'],true).'" class="image_group" title="'.$edit_image['title'].'"><img src="'.get_file_url(IMAGES_THUMBNAILS_PATH.$edit_image['id'].'.'.$edit_image['extension'],true).'"></a>'));
        }
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_images_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if($_GET['action'] == 'add') {
            $image_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_IMAGES." WHERE listing_id=?",array($_GET['listing_id']));

            if($image_count >= $listing['images_limit'] AND $_GET['action'] != 'edit') {
                $form->addError($PMDR->getLanguage('admin_images_limit'));
            }
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Images')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_images'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Images')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_images'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>