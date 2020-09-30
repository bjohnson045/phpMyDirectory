<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$PMDR->loadLanguage(array('user_banners','user_orders'));

$user = $PMDR->get('Users')->getRow($PMDR->get('Session')->get('user_id'));

$listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_banners'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_banners'));
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
    if($db->GetRow("SELECT id FROM ".T_BANNERS." WHERE id=? AND listing_id=? LIMIT 1",array($_GET['id'],$listing['id']))) {
        $PMDR->get('Banners')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_banners'))),'delete');
    }
    redirect(array('listing_id'=>$_GET['listing_id']));
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_banners.tpl');

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('banners'));

if(!isset($_GET['action'])) {
    $types = $PMDR->get('Banners_Types')->getTypesAssoc();

    $template_content->set('title',$PMDR->getLanguage('user_banners'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_banners.php?action=add&listing_id='.$listing['id']);

    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_banners_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_banners_id'));
    $table_list->addColumn('type_id',$PMDR->getLanguage('user_banners_type'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_banners_title'));
    $table_list->addColumn('impressions',$PMDR->getLanguage('user_banners_impressions'));
    $table_list->addColumn('date_last_displayed',$PMDR->getLanguage('user_banners_last_displayed'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_BANNERS." WHERE listing_id=? ORDER BY id ASC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['type_id'] = $types[$record['type_id']];
        $records[$key]['url'] = get_file_url(BANNERS_PATH.$record['id'].'.'.$record['extension']);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_banners_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('banner_details',array('legend'=>$PMDR->getLanguage('user_banners_banner')));
    $banner_types = $PMDR->get('Banners_Types')->getTypesAssoc('image');
    foreach($banner_types AS $id=>$value) {
        if(!$listing['banner_limit_'.$id]) {
            unset($banner_types[$id]);
        }
    }
    $form->addField('type_id','select',array('label'=>$PMDR->getLanguage('user_banners_type'),'fieldset'=>'banner_details','value'=>'','options'=>$banner_types));
    $form->addField('title','text',array('fieldset'=>'banner'));
    $form->addField('image','file',array('fieldset'=>'banner_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('user_banners_listing_id'),'fieldset'=>'banner_details','value'=>$listing['id']));
    $form->addValidator('title',new Validate_Banned_Words());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('user_banners_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_banners_edit'));
        $edit_banner = $PMDR->get('Banners')->getRow($_GET['id']);
        $form->loadValues($edit_banner);
        $form->addField('current_image','custom',array('label'=>$PMDR->getLanguage('user_banners_current_image'),'fieldset'=>'banner_details','html'=>'<a href="'.get_file_url(BANNERS_PATH.$edit_banner['id']).'.'.$edit_banner['extension'].'" class="lightwindow" title="'.$edit_banner['title'].'"><img src="'.get_file_url(BANNERS_PATH.$edit_banner['id'].'.'.$edit_banner['extension']).'"></a>'));
    } else {
        $template_content->set('title',$PMDR->getLanguage('user_banners_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_banners_edit'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $banner_type = $PMDR->get('Banners_Types')->getRow($data['type_id']);
        $form->addValidator('image',
            new Validate_Image(
                $banner_type['width'],
                $banner_type['height'],
                $banner_type['filesize'],
                explode(',',$PMDR->getConfig('banners_formats')),
                true,
                true
            )
        );

        $banner_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_BANNERS." WHERE listing_id=? AND type_id=?",array($listing['id'],$data['type_id']));

        if($banner_count >= $listing['banner_limit_'.$data['type_id']] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_banners_limit'));
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $data['status'] = $listing['status'];
                $data['all_pages'] = 1;
                $PMDR->get('Banners')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['image']['name'],$PMDR->getLanguage('user_banners'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Banners')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['image']['name'],$PMDR->getLanguage('user_banners'))),'update');
                redirect(array('listing_id'=>$listing['id']));
            }
        }
    }
    $template_content_form->set('form',$form);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>