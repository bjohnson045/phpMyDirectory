<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

// Get authentication
$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_banners','admin_listings','admin_users'));

$PMDR->get('Authentication')->checkPermission('admin_banners_view');

$PMDR->loadJavascript('<script type="text/javascript" src="'.BASE_URL.'/includes/jquery/magnific_popup/magnific.js"></script>',15);
$PMDR->loadCSS('<link rel="stylesheet" type="text/css" href="'.BASE_URL.'/includes/jquery/magnific_popup/magnific.css" media="screen" />',15);
$PMDR->loadJavascript('<script type="text/javascript">$(document).ready(function() {$("a.image_group").magnificPopup({type:\'image\', closeOnContentClick: true });});</script>',20);

if($_GET['action'] == 'get_type') {
    echo $db->GetOne("SELECT type FROM ".T_BANNER_TYPES." WHERE id=?",array($_GET['type_id']));
    exit();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!empty($_GET['listing_id'])) {
    if(!$listing = $PMDR->get('Listings')->getRow($_GET['listing_id'])) {
        redirect();
    }
    $template_content->set('listing_header',$PMDR->get('Listing',$_GET['listing_id'])->getAdminHeader('banners'));
    $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
}

// Delete a banner
if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_banners_delete');
    $PMDR->get('Banners')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_banners'))),'delete');
    if(isset($_GET['listing_id'])) {
        redirect(null,array('listing_id'=>$_GET['listing_id']));
    } else {
        redirect();
    }
}

if(!isset($_GET['action'])) {
    // Get the banner types for display in the results, used below in the foreach loop
    $types = $PMDR->get('Banners_Types')->getTypesAssoc();

    $template_content->set('title',$PMDR->getLanguage('admin_banners'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id');
    if(empty($listing)) {
        $table_list->addColumn('listing_id');
    }
    $table_list->addColumn('title');
    $table_list->addColumn('status');
    $table_list->addColumn('type_id',$PMDR->getLanguage('admin_banners_type'));
    $table_list->addColumn('statistics',$PMDR->getLanguage('admin_banners_impressions') .' / '.$PMDR->getLanguage('admin_banners_clicks'));
    $table_list->addColumn('date_displayed');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $where = array();
    if(isset($_GET['listing_id'])) {
        $where[] = 'b.listing_id = '.$PMDR->get('Cleaner')->clean_db($_GET['listing_id']);
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS b.*, bt.width, bt.height, bt.name FROM ".T_BANNERS." b INNER JOIN ".T_BANNER_TYPES." bt ON bt.id = b.type_id $where ORDER BY b.id ASC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['status'] = $PMDR->get('HTML')->icon(($record['status'] == 'active' ? '1' : '0'));
        $records[$key]['type_id'] = $types[$record['type_id']];
        $records[$key]['statistics'] = $record['impressions'].' / '.$record['clicks'];
        if($PMDR->get('Dates')->isZero($record['date_last_displayed'])) {
            $records[$key]['date_displayed'] = '-';
        } else {
            $records[$key]['date_displayed'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_last_displayed']);
        }
        if(empty($listing)) {
            if(is_null($record['listing_id'])) {
                $records[$key]['listing_id'] = '-';
            } else {
                $records[$key]['listing_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_listings.php?id='.$record['listing_id'].'&action=edit">'.$record['listing_id'].'</a>';
            }
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&id='.$record['id'].(isset($_GET['listing_id']) ? '&listing_id='.$record['listing_id'] : '')));

        if($record['code'] != '') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>'#banner_'.$record['id'],'class'=>'image_group','rel'=>'image_group','label'=>$PMDR->getLanguage('admin_banners_view')));
            if($record['url']) {
                $records[$key]['manage'] .= '<div style="display: none;" id="banner_'.$record['id'].'"><a href="'.$record['url'].'">'.$record['code'].'</a></div>';
            } else {
                $records[$key]['manage'] .= '<div style="display: none;" id="banner_'.$record['id'].'">'.$record['code'].'</div>';
            }
        } else {
            if($record['extension'] == 'swf') {
                $banner_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_banners_swf.tpl');
                $banner_content->set('file',get_file_url(BANNERS_PATH.$record['id'].'.'.$record['extension'],true));
                $banner_content->set('width',$PMDR->get('Cleaner')->clean_output($record['width']));
                $banner_content->set('height',$PMDR->get('Cleaner')->clean_output($record['height']));
                $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>'#banner_content_'.$record['id'],'class'=>'image_group','rel'=>'image_group','title'=>$record['name'].' ('.$PMDR->getLanguage('admin_banners_id').': '.$record['id'].')','label'=>$PMDR->getLanguage('admin_banners_view')));
                $records[$key]['manage'] .= '<div style="display: none;"><div id="banner_content_'.$record['id'].'">'.$banner_content->render().'</div></div>';
            } else {
                $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>get_file_url(BANNERS_PATH.$record['id'].'.'.$record['extension']),'class'=>'image_group','rel'=>'image_group','title'=>$record['name'].' ('.$PMDR->getLanguage('admin_banners_id').': '.$record['id'].')','label'=>$PMDR->getLanguage('admin_banners_view')));
            }
        }

        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&id='.$record['id'].(isset($_GET['listing_id']) ? '&listing_id='.$record['listing_id'] : '')));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'add') {
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_BANNER_TYPES)) {
            $PMDR->addMessage('error','You must first add a banner type before adding a banner.');
            redirect_url(BASE_URL_ADMIN.'/admin_banners_types.php?action=add');
        }
    }

    $PMDR->get('Authentication')->checkPermission('admin_banners_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('banner');
    $statuses = array(
        'active'=>$PMDR->getLanguage('active'),
        'suspended'=>$PMDR->getLanguage('suspended')
    );
    $form->addField('status','select',array('fieldset'=>'banner','options'=>$statuses));
    $form->addField('type_id','select',array('fieldset'=>'banner','label'=>$PMDR->getLanguage('admin_banners_type'),'value'=>'','options'=>$PMDR->get('Banners_Types')->getTypesAssoc(),'onchange'=>'toggle_banner_type();'));
    if(!empty($listing)) {
        $form->addField('listing_id','hidden',array('fieldset'=>'banner','value'=>$listing['id']));
    }
    $form->addField('title','text',array('fieldset'=>'banner'));
    $form->addField('image','file',array('label'=>$PMDR->getLanguage('admin_banners_image'),'fieldset'=>'banner'));
    $form->addField('code_text','text',array('fieldset'=>'banner','counter'=>'0'));
    if(empty($listing)) {
        $form->addField('url','text',array('fieldset'=>'banner'));
        $form->addValidator('url',new Validate_NonEmpty());
    }
    $form->addField('target','select',array('fieldset'=>'banner','options'=>array('_blank'=>'New Window (_blank)','_self'=>'Same Window (_self)','_top'=>'Top Window (_top)','_parent'=>'Parent Window (_parent)')));
    $form->addField('alt_text','text',array('fieldset'=>'banner'));
    $form->addField('code','textarea',array('fieldset'=>'banner'));
    $form->addField('categories','tree_select_expanding_checkbox',array('fieldset'=>'banner','options'=>array('type'=>'category_tree','bypass_setup'=>true,'search'=>true)));
    $form->addField('locations','tree_select_expanding_checkbox',array('fieldset'=>'banner','options'=>array('type'=>'location_tree','bypass_setup'=>true,'search'=>true)));
    $form->addField('all_pages','checkbox',array('fieldset'=>'banner'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('code',new Validate_NonEmpty());
    $form->addValidator('code_text',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_banners_edit'));
        $edit_banner = $PMDR->get('Banners')->getRow($_GET['id']);
        if(!is_null($edit_banner['listing_id']) AND !isset($_GET['listing_id'])) {
            redirect(BASE_URL_ADMIN.'/admin_banners.php?action=edit&id='.$_GET['id'].'&listing_id='.$edit_banner['listing_id']);
        }
        $form->loadValues($edit_banner);
        if($edit_banner['extension'] == 'swf') {
            $banner_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_banners_swf.tpl');
            $banner_content->set('file',get_file_url(BANNERS_PATH.$edit_banner['id'].'.'.$edit_banner['extension'],true));
            $banner_content->set('width',$PMDR->get('Cleaner')->clean_output($edit_banner['width']));
            $banner_content->set('height',$PMDR->get('Cleaner')->clean_output($edit_banner['height']));
            $preview = $banner_content->render();
        } else {
            $preview = '<a href="'.get_file_url(BANNERS_PATH.$edit_banner['id'].'.'.$edit_banner['extension'],true).'" class="image_group" title="'.$edit_banner['title'].'"><img src="'.get_file_url(BANNERS_PATH.$edit_banner['id'].'.'.$edit_banner['extension'],true).'"></a>';
        }
        if($edit_banner['type'] == 'image') {
            $form->addField('current_image','custom',array('fieldset'=>'banner','value'=>'','options'=>'','html'=>$preview));
        }
        $form->setFieldAttribute('type_id','type','readonly');
        $form->setFieldAttribute('code_text','value',$edit_banner['code']);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_banners_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $type = $db->GetRow("SELECT type, width, height, filesize, character_limit FROM ".T_BANNER_TYPES." WHERE id=?",array($data['type_id']));

        if($type['type'] == 'image') {
            $form->addValidator('image',new Validate_Image($type['width'],$type['height'],$type['filesize'],$PMDR->getConfig('banners_formats'),true,true));
            $form->removeValidator('code_text');
            $form->removeValidator('code');
        }

        if($type['type'] == 'html') {
            $form->removeValidator('code_text');
            $form->removeValidator('url');
            $form->removeValidator('image');
            $form->removeValidator('target');
        }

        if($type['type'] == 'text') {
            $data['code'] = Strings::limit_characters($data['code_text'],$type['character_limit']);
            $form->removeValidator('code');
            $form->removeValidator('image');
            $form->removeValidator('target');
        }

        if(isset($_GET['listing_id'])) {
            $banner_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_BANNERS." WHERE listing_id=? AND type_id=?",array($_GET['listing_id'],$data['type_id']));
            if($banner_count >= $listing['banner_limit_'.$form->getFieldValue('type_id')] AND $_GET['action'] != 'edit') {
                $form->addError($PMDR->getLanguage('admin_banners_limit',array($listing['banner_limit_'.$form->getFieldValue('type_id')])));
            }
        }

        // Listing banners getting removed from listings
        if($data['listing_id'] == 0) {
            $data['listing_id'] = NULL;
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $data['status'] = ($listing) ? $listing['status'] : 'active';
                $id = $PMDR->get('Banners')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($id,$PMDR->getLanguage('admin_banners'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Banners')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_banners'))),'update');
            }
            if($listing) {
                redirect(null,array('listing_id'=>$_GET['listing_id']));
            } else {
                redirect();
            }
        }
    }
    $javascript = '
    <script type="text/javascript">
    function toggle_banner_type() {
        $.get(\''.BASE_URL_ADMIN.'/admin_banners.php\',{ action: \'get_type\', type_id: $(\'#type_id\').val() },
        function(data){
            if(data == \'\') {
                $(\'#banner-control-group,#code_text-control-group,#code-control-group,#alt_text-control-group,#url-control-group,#target-control-group,#submit\').hide();
            } else {
                $(\'#alt_text-control-group,#image-control-group,#preview-control-group\').toggle(data == \'image\');
                $(\'#target-control-group,#url-control-group\').toggle(data != \'html\');
                $(\'#code_text-control-group\').toggle(data == \'text\');
                $(\'#code-control-group\').toggle(data == \'html\');
                $(\'#submit\').show();
            }
        });
        $.ajax({
            data:({
                action: "admin_banner_types_character_limit",
                id: $(\'#type_id\').val()
            }),
            success: function(data) {
                $("#code_text").charCounter(data);
            }
        });
        return false;
    }
    $(document).ready(toggle_banner_type);
    </script>';
    $template_content->set('content',$form->toHTML().$javascript);
}

if(!isset($_GET['listing_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_banners_types_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>