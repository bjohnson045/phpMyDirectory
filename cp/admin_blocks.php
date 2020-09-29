<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_blocks'));

$PMDR->get('Authentication')->authenticate();

if($_GET['action'] == 'delete') {
    if($PMDR->get('Blocks')->delete($_GET['id'])) {
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_blocks'))),'delete');
    } else {
        $PMDR->addMessage('error',$PMDR->getLanguage('messages_delete_failed'));
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_blocks_title'));
    $table_list->addColumn('variable',$PMDR->getLanguage('admin_blocks_variable'));
    $table_list->addColumn('active',$PMDR->getLanguage('admin_blocks_active'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($PMDR->get('Blocks')->getCount());
    $records = $db->GetAll("SELECT * FROM ".T_BLOCKS." LIMIT ".$table_list->page_data['limit1'].", ".$table_list->page_data['limit2']);
    foreach($records as &$record) {
        $record['active'] = $PMDR->get('HTML')->icon($record['active']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_blocks'));
    $template_content->set('content',$table_list->render());
} elseif($_GET['action'] == 'add' AND !isset($_GET['type'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_blocks_add'));
    $form = $PMDR->get('Form');
    $form->addFieldSet('block',array('legend'=>$PMDR->getLanguage('admin_blocks')));
    $types = $PMDR->get('Blocks')->getTypes();
    $types_options = array();
    foreach($types AS $type) {
        $types_options[$type] = $PMDR->getLanguage('admin_blocks_type_'.$type);
    }
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_blocks_type'),'fieldset'=>'block','options'=>$types_options));
    $form->addField('submit_type','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    if($form->wasSubmitted('submit_type')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            redirect(null,array('action'=>'add','type'=>$data['type']));
        }
    }
    $template_content->set('content',$form->toHTML());
} else {
    $types = $PMDR->get('Blocks')->getTypes();
    if($_GET['action'] == 'add' AND !in_array($_GET['type'],$types)) {
        redirect();
    }

    if($_GET['action'] == 'add') {
        $type = $_GET['type'];
    } else {
        $block = $PMDR->get('Blocks')->getRow($_GET['id']);
        $type = $block['type'];
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('block',array('legend'=>$PMDR->getLanguage('admin_blocks')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_blocks_title'),'fieldset'=>'block'));
    $form->addField('variable','text',array('label'=>$PMDR->getLanguage('admin_blocks_variable'),'fieldset'=>'block'));
    $form->addField('active','checkbox',array('label'=>$PMDR->getLanguage('admin_blocks_active'),'fieldset'=>'block'));
    if($type == 'rss') {
        $form->addField('data_url','url',array('label'=>$PMDR->getLanguage('admin_blocks_type_'.$type),'fieldset'=>'block'));
        $form->addField('data_limit','number_toggle',array('label'=>'RSS Feed Limit','fieldset'=>'block'));
    } else {
        $form->addField('data_content',$type,array('label'=>$PMDR->getLanguage('admin_blocks_type_'.$type),'fieldset'=>'block'));
    }
    $form->addField('template','text',array('label'=>$PMDR->getLanguage('admin_blocks_template'),'fieldset'=>'block','placeholder'=>TEMPLATE_PATH.'blocks/example.tpl'));
    $form->addField('cache_minutes','number_toggle',array('label'=>$PMDR->getLanguage('admin_blocks_cache_minutes'),'fieldset'=>'block'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('variable',new Validate_NonEmpty());

    // If we are editing a page, look it up to get its values
    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_blocks_edit'));
        $form->loadValues($block);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_blocks_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $data['type'] = $type;
                $PMDR->get('Blocks')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_blocks'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Blocks')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_blocks'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_blocks_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>