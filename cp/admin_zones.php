<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_zones'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    if($PMDR->get('Zones')->delete($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_zones'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('messages_delete_failed'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_zones_id'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_zones_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_zones_description'));
    $table_list->addColumn('variable',$PMDR->getLanguage('admin_zones_variable'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_zones_active'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($PMDR->get('Zones')->getCount());
    $records = $db->GetAll("SELECT * FROM ".T_ZONES." LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_zones'));
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('zone',array('legend'=>$PMDR->getLanguage('admin_zones')));

    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_zones_title'),'fieldset'=>'zone'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_zones_description'),'fieldset'=>'zone'));
    $form->addField('variable','text',array('label'=>$PMDR->getLanguage('admin_zones_variable'),'fieldset'=>'zone'));
    $form->addField('active','checkbox',array('label'=>$PMDR->getLanguage('admin_zones_active'),'fieldset'=>'zone'));

    $zones = $PMDR->get('Zones')->getZoneDisplayOptions();
    $form->addFieldSet('zones',array('legend'=>'Display Options'));
    foreach($zones AS $zone) {
        $form->addField($zone,'checkbox',array('label'=>$PMDR->getLanguage('admin_zones_display_'.$zone),'fieldset'=>'zones','value'=>$zone_key));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $content_options = $PMDR->get('Zones')->getContentOptions();
    $form->addFieldSet('content',array('legend'=>'Content','help'=>$PMDR->getLanguage('admin_zones_content_help',BASE_URL_ADMIN.'/admin_blocks.php?action=add')));
    if(count($content_options)) {
        foreach($content_options AS $option_type=>$options) {
            $form->addField($option_type,'checkbox',array('label'=>$PMDR->getLanguage('admin_zones_content_type_'.$option_type),'fieldset'=>'content','options'=>$options));
        }
    } else {
        $form->addField('no_content','custom',array('label'=>'','fieldset'=>'content','html'=>$PMDR->getLanguage('admin_zones_content_help',BASE_URL_ADMIN.'/admin_blocks.php?action=add')));
    }
    $form->addValidator('title',new Validate_NonEmpty());

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_zones_edit'));
        $form->loadValues($PMDR->get('Zones')->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_zones_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Zones')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_zones'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Zones')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_zones'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_zones_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>