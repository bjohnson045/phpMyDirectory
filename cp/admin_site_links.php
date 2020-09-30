<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_site_links','admin_listings'));

$PMDR->get('Authentication')->checkPermission('admin_site_links_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_site_links_delete');
    $PMDR->get('Site_Links')->delete($_GET['id']);
    $PMDR->get('Cache')->delete('site_links');
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_site_links'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_site_links'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_site_links_id'));
    $table_list->addColumn('preview',$PMDR->getLanguage('admin_site_links_preview'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_site_links_title'));
    $table_list->addColumn('code',$PMDR->getLanguage('admin_site_links_code'));
    $table_list->addColumn('show_date',$PMDR->getLanguage('admin_site_links_show_date'));
    $table_list->addColumn('requires_active_product',$PMDR->getLanguage('admin_site_links_requires_active_product'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_SITE_LINKS." ORDER BY id ASC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['code'] = '<textarea class="form-control" style="width: 300px; height: 75px;">'.$PMDR->get('Cleaner')->clean_output('<script type="text/javascript" src="'.BASE_URL.'/site_links.php?&id='.$record['id'].'&action=display"></script>').'</textarea>';
        $records[$key]['show_date'] = $PMDR->get('HTML')->icon($record['show_date']);
        $records[$key]['requires_active_product'] = $PMDR->get('HTML')->icon($record['requires_active_product']);
        $records[$key]['preview'] = '<script type="text/javascript" src="'.BASE_URL.'/site_links.php?id='.$record['id'].'&action=display"></script>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_site_links_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('site_links_details',array('legend'=>$PMDR->getLanguage('admin_site_links_link')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_site_links_title'),'fieldset'=>'site_links_details','value'=>'','help'=>$PMDR->getLanguage('admin_site_links_help_title')));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_site_links_description'),'fieldset'=>'site_links_details','help'=>$PMDR->getLanguage('admin_site_links_help_description')));
    $form->addField('link_text','text',array('label'=>$PMDR->getLanguage('admin_site_links_link_text'),'fieldset'=>'site_links_details','help'=>$PMDR->getLanguage('admin_site_links_help_link_text')));
    $form->addField('url_alternate','url',array('fieldset'=>'site_links_details'));
    $form->addField('url_alternate_listing','url',array('fieldset'=>'site_links_details'));
    $form->addField('url_alternate_inactive','url',array('fieldset'=>'site_links_details'));
    $form->addField('image','file',array('label'=>$PMDR->getLanguage('admin_site_links_image'),'fieldset'=>'site_links_details','help'=>$PMDR->getLanguage('admin_site_links_help_image'),'options'=>array('url_allow'=>true)));
    $form->addField('show_date','checkbox',array('label'=>$PMDR->getLanguage('admin_site_links_show_date'),'fieldset'=>'site_links_details','help'=>$PMDR->getLanguage('admin_site_links_help_show_date')));
    $form->addField('requires_active_product','checkbox',array('label'=>$PMDR->getLanguage('admin_site_links_requires_active_product'),'fieldset'=>'site_links_details','help'=>$PMDR->getLanguage('admin_site_links_help_requires_active_product')));
    $form->addField('pricing_ids','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_site_links_pricing_ids'),'fieldset'=>'site_links_details','value'=>'','help'=>$PMDR->getLanguage('admin_site_links_help_pricing_ids'),'options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true)));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_site_links_edit'));
        $edit_site_link = $PMDR->get('Site_Links')->getRow($_GET['id']);
        $edit_site_link['pricing_ids'] = explode(',',$edit_site_link['pricing_ids']);
        $form->loadValues($edit_site_link);
        if(find_file(SITE_LINKS_PATH.$edit_site_link['id'].'.*')) {
            $form->addField('preview','custom',array('label'=>$PMDR->getLanguage('admin_site_links_current_image'),'fieldset'=>'site_links_details','value'=>'','options'=>'','html'=>'<img src="'.get_file_url(SITE_LINKS_PATH.$edit_site_link['id'].'.'.$edit_site_link['extension'],true).'">'));
        }
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_site_links_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if(!is_writable(SITE_LINKS_PATH)) {
            $form->addError('The '.SITE_LINKS_PATH.' path is not writable.');
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->delete('site_links');
            $data['pricing_ids'] = implode(',',(array) $data['pricing_ids']);
            if($_GET['action']=='add') {
                $PMDR->get('Site_Links')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_site_links'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Site_Links')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_site_links'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_site_links_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>