<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_pages','admin_menu_links'));

$PMDR->get('Authentication')->checkPermission('admin_pages_view');

/** @var CustomPage */
$custom_page = $PMDR->get('CustomPage');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_pages_delete');
    if($custom_page->delete($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_pages'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('messages_delete_failed'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',null,false,true);
    $table_list->addColumn('content');
    $table_list->addColumn('manage',null,false,true);
    $table_list->setTotalResults($custom_page->getCount());
    $records = $custom_page->getRows($table_list->page_data['limit1'],$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['content'] = Strings::limit_words(strip_tags($record['content']),500);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>BASE_URL.((MOD_REWRITE) ? '/pages/'.$record['friendly_url'].'.html' : '/page.php?id='.$record['id']),'label'=>$PMDR->getLanguage('admin_pages_view'),'target'=>'_blank'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_pages'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_pages_edit');
    // Create form
    $form = $PMDR->get('Form');
    $form->addFieldSet('information');
    $form->addFieldSet('meta_information');
    $form->addFieldSet('template',array('legend'=>'Template'));

    // Add necesarry form fields for the page editor
    $form->addField('title','text',array('fieldset'=>'information','onblur'=>'$.ajax({data:({action:\'rewrite\',text:$(this).val()}),success:function(text_rewrite){$(\'#friendly_url\').val(text_rewrite)}});'));
    $form->addField('friendly_url','text',array('fieldset'=>'information'));
    $form->addField('active','checkbox',array('fieldset'=>'information'));
    $form->addField('meta_title','text',array('fieldset'=>'meta_information'));
    $form->addField('meta_description','textarea',array('fieldset'=>'meta_information'));
    $form->addField('meta_keywords','text',array('fieldset'=>'meta_information'));
    $form->addField('content','htmleditor',array('fieldset'=>'information'));

    $form->addField('header_template_file','text',array('fieldset'=>'template'));
    $form->addField('footer_template_file','text',array('fieldset'=>'template'));
    $form->addField('wrapper_template_file','text',array('fieldset'=>'template'));

    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    // Add validators to the form by title, and validator type
    $form->addValidator('title',new Validate_NonEmpty());
    if(MOD_REWRITE) {
        $form->addValidator('friendly_url',new Validate_NonEmpty());
    }

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_pages_edit'));
        $form->loadValues($custom_page->getRow($_GET['id']));
        $form->addField('submit_edit','submit',array('label'=>$PMDR->getLanguage('admin_submit_reload'),'fieldset'=>'submit'));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_pages_add'));
    }

    if($form->wasSubmitted('submit') OR $form->wasSubmitted('submit_edit')) {
        $data = $form->loadValues();
        if($_GET['action'] == 'add') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_PAGES." WHERE friendly_url=?",array($data['friendly_url']))) {
                $form->addError($PMDR->getLanguage('admin_pages_friendly_url_exists'),'friendly_url');
            }
        } elseif($_GET['action'] == 'edit') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_PAGES." WHERE friendly_url=? AND id!=?",array($data['friendly_url'],$_GET['id']))) {
                $form->addError($PMDR->getLanguage('admin_pages_friendly_url_exists'),'friendly_url');
            }
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $custom_page->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_pages'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $custom_page->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_pages'))),'update');
                if($form->wasSubmitted('submit_edit')) {
                    redirect(URL);
                } else {
                    redirect();
                }
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_pages_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>