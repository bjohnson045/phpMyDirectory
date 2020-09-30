<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_menu_links','admin_pages'));

$PMDR->get('Authentication')->checkPermission('admin_menu_links_view');

$custom_links = $PMDR->get('CustomLinks');
// Do not allow a menu link to be a parent of itself.
if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_menu_links_delete');
    if($custom_links->delete(array('id'=>$_GET['id']))) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_custom_links'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('messages_delete_failed'));
    }
    $PMDR->get('Cache')->delete(array('block_menu_logged_in','block_menu_logged_out'));
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->all_one_page = true;
    $table_list->form = true;
    $table_list->sortable(T_MENU_LINKS, $PMDR->getLanguage('admin_menu_links_order'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_menu_links_title'));
    $table_list->addColumn('link',$PMDR->getLanguage('admin_menu_links_link'));
    $table_list->addColumn('parent_title',$PMDR->getLanguage('admin_menu_links_parent'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_menu_links_active'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($custom_links->getCount());
    $records = $db->GetAll("SELECT menu_links.*, IF(LENGTH(parent.title) > 0,parent.title,'-') as parent_title, ".T_PAGES.".title as page_title FROM ".T_MENU_LINKS." as menu_links LEFT JOIN ".T_MENU_LINKS." as parent ON menu_links.parent_id=parent.id LEFT JOIN ".T_PAGES." ON menu_links.page_id=".T_PAGES.".id ORDER BY ordering LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2']);
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        if($record['page_title'] != '') {
            $records[$key]['link'] = '<a href="'.BASE_URL.'/page.php?id='.$record['page_id'].'">'.$record['page_title'].'</a>';
        } else {
            $records[$key]['link'] = '<a href="'.strstr($record['link'],'http') ? $record['link'] : BASE_URL.'/'.$record['link'].'">'.$record['link'].'</a>';
        }
        $records[$key]['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $records[$key]['active'] = $PMDR->get('HTML')->icon($record['active']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_menu_links'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_menu_links_edit');
    // Create form
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_menu_links_information')));
    $form->addFieldSet('display',array('legend'=>$PMDR->getLanguage('admin_menu_links_display')));

    // Add necesarry form fields for the menu link editor
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_menu_links_title'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_menu_links_help_title')));
    $languages = $db->GetAll("SELECT languageid, title FROM ".T_LANGUAGES." WHERE languageid!=1");
    foreach($languages AS $language) {
        $form->addField('title_'.$language['languageid'],'text',array('fieldset'=>'information','label'=>$PMDR->getLanguage('admin_menu_links_title').' ('.$language['title'].')'));
    }
    if($_GET['action'] == 'edit') {
        $links = $db->GetAssoc("SELECT id, title FROM ".T_MENU_LINKS." WHERE id!=? ORDER BY title",array($_GET['id']));
    } else {
        $links = $db->GetAssoc("SELECT id, title FROM ".T_MENU_LINKS." ORDER BY title");
    }
    array_unshift_assoc($links, 'NULL', $PMDR->getLanguage('admin_menu_links_select_parent'));
    $form->addField('parent_id','select',array('label'=>$PMDR->getLanguage('admin_menu_links_parent'),'fieldset'=>'information','value'=>'NULL','options'=>$links,'help'=>$PMDR->getLanguage('admin_menu_links_help_parent_id')));

    $pages = $db->GetAssoc("SELECT id, title FROM ".T_PAGES." ORDER BY title");
    array_unshift_assoc($pages, 'NULL', $PMDR->getLanguage('admin_menu_links_select_page'));
    $form->addField('page_id','select',array('label'=>$PMDR->getLanguage('admin_menu_links_page_link'),'fieldset'=>'information','value'=>'NULL','options'=>$pages,'help'=>$PMDR->getLanguage('admin_menu_links_help_page_id')));

    $form->addField('link','text',array('label'=>$PMDR->getLanguage('admin_menu_links_link'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_menu_links_help_link')));

    $form->addField('target','select',array('label'=>$PMDR->getLanguage('admin_menu_links_target'),'fieldset'=>'information','first_option'=>'','options'=>array('_blank'=>'_blank','_self'=>'_self','_top'=>'_top','_parent'=>'_parent'),'help'=>$PMDR->getLanguage('admin_menu_links_help_target')));
    $form->addField('nofollow','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_nofollow'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_menu_links_help_nofollow')));
    $form->addField('logged_in','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_logged_in'),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_logged_in')));
    $form->addField('logged_out','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_logged_out'),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_logged_out')));
    $form->addField('sitemap','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_sitemap'),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_sitemap')));
    $form->addField('sitemap_xml','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_sitemap_xml'),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_sitemap_xml')));
    $sitemap_priorities = array(
        '0.0'=>'0.0',
        '0.1'=>'0.1',
        '0.2'=>'0.2',
        '0.3'=>'0.3',
        '0.4'=>'0.4',
        '0.5'=>'0.5',
        '0.6'=>'0.6',
        '0.7'=>'0.7',
        '0.8'=>'0.8',
        '0.9'=>'0.9',
        '1.0'=>'1.0',
    );
    $form->addField('sitemap_xml_priority','select',array('label'=>$PMDR->getLanguage('admin_menu_links_sitemap_xml_priority'),'options'=>$sitemap_priorities,'first_option'=>array(''=>''),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_sitemap_xml_priority')));
    $form->addField('active','checkbox',array('label'=>$PMDR->getLanguage('admin_menu_links_active'),'fieldset'=>'display','help'=>$PMDR->getLanguage('admin_menu_links_help_active')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    // Add validators to the form by title, and validator type
    $form->addValidator('title',new Validate_NonEmpty());

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_menu_links_edit'));
        $form->loadValues($custom_links->getRow(array('id'=>$_GET['id'])));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_menu_links_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $custom_links->insert($data);
                $PMDR->get('Cache')->delete('block_menu');
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_menu_links'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $custom_links->update($data, array('id'=>$_GET['id']));
                $PMDR->get('Cache')->delete('block_menu');
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_menu_links'))),'update');
            }
            $PMDR->get('Cache')->delete(array('block_menu_logged_in','block_menu_logged_out'));
            redirect();
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_pages_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>