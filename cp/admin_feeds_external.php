<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_feeds_external'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_external_feeds');

$external_feeds = $PMDR->get('External_Feeds');

if($_GET['action'] == 'delete') {
    if($external_feeds->delete($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_feeds_external'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('messages_delete_failed'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_feeds_external_id'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_feeds_external_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_feeds_external_description'));
    $table_list->addColumn('url',$PMDR->getLanguage('admin_feeds_external_url'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_feeds_external_active'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($external_feeds->getCount());
    $records = $db->GetAll("SELECT * FROM ".T_FEEDS_EXTERNAL." LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['title'] .= ' '.$PMDR->get('HTML')->icon('rss',array('href'=>$PMDR->get('Cleaner')->clean_output($record['url']),'target'=>'_blank'));
        $record['url'] = '<a target="_blank" href="'.$PMDR->get('Cleaner')->clean_output($record['url']).'">'.$PMDR->get('Cleaner')->clean_output($record['url']).'</a>';
        $record['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_feeds_external'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('feed',array('legend'=>$PMDR->getLanguage('admin_feeds_external')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_feeds_external_title'),'fieldset'=>'feed','help'=>$PMDR->getLanguage('admin_feeds_external_help_title')));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_feeds_external_description'),'fieldset'=>'feed','help'=>$PMDR->getLanguage('admin_feeds_external_help_description')));
    $form->addField('url','text',array('label'=>$PMDR->getLanguage('admin_feeds_external_url'),'fieldset'=>'feed','help'=>$PMDR->getLanguage('admin_feeds_external_help_url')));
    $form->addField('limit','text',array('label'=>$PMDR->getLanguage('admin_feeds_external_limit'),'fieldset'=>'feed'));
    $form->addField('active','checkbox',array('label'=>$PMDR->getLanguage('admin_feeds_external_active'),'fieldset'=>'feed','help'=>$PMDR->getLanguage('admin_feeds_external_help_active')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('url',new Validate_NonEmpty());

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_feeds_external_edit'));
        $form->loadValues($external_feeds->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_feeds_external_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $external_feeds->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_feeds_external'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $external_feeds->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_feeds_external'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_feeds_external_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>