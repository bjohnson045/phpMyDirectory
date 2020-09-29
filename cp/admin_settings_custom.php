<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('settings','admin_settings_custom'));

$PMDR->get('Authentication')->checkPermission('admin_settings');

if($_GET['action'] == 'delete') {
    $db->Execute("DELETE FROM ".T_SETTINGS." WHERE varname=?",array($_GET['varname']));
    $PMDR->get('Phrases')->delete($_GET['varname'],'custom');
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['varname'],$PMDR->getLanguage('admin_settings_custom'))),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_settings_custom'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('label');
    $table_list->addColumn('varname');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_SETTINGS." WHERE grouptitle='custom' ORDER BY varname DESC LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['label'] = $PMDR->getLanguage('setting_'.$record['varname'].'_title');
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&varname='.$record['varname']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&varname='.$record['varname']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_settings_custom_setting')));
    $form->addField('variablename','text',array('label'=>$PMDR->getLanguage('admin_settings_custom_varname'),'fieldset'=>'information'));
    $form->addField('label','text');
    $form->addField('description','textarea');
    $form->addField('type','select',array('label'=>'Type','options'=>array('text'=>$PMDR->getLanguage('admin_settings_custom_input'),'textarea'=>$PMDR->getLanguage('admin_settings_custom_textarea'),'checkbox'=>$PMDR->getLanguage('admin_settings_custom_checkbox'))));
    $form->addField('submit','submit');

    $form->addValidator('variablename',new Validate_NonEmpty());
    $form->addValidator('label',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_settings_custom_edit'));
        $form->deleteField('variablename');
        $form->deleteField('type');
        $form->loadValues(array('label'=>$PMDR->getLanguage('setting_'.$_GET['varname'].'_title'),'description'=>$PMDR->getLanguage('setting_'.$_GET['varname'].'_desc')));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_settings_custom_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action'] == 'add') {
                $PMDR->get('Phrases')->insert(array('variablename'=>'setting_custom_'.$data['variablename'].'_title','content'=>$data['label']),'settings');
                $PMDR->get('Phrases')->insert(array('variablename'=>'setting_custom_'.$data['variablename'].'_desc','content'=>$data['description']),'settings');
                $db->Execute("INSERT INTO ".T_SETTINGS." (varname,grouptitle,optioncode_type) VALUES (?,?,?)",array('custom_'.$data['variablename'],'custom',$data['type']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['variablename'],$PMDR->getLanguage('admin_settings_custom'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Phrases')->updatePhrase(-1,'settings','setting_'.$_GET['varname'].'_title',$data['label']);
                $PMDR->get('Phrases')->updatePhrase(-1,'settings','setting_'.$_GET['varname'].'_desc',$data['description']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['varname'],$PMDR->getLanguage('admin_settings_custom'))),'update');
            }
            redirect();
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_settings_custom_menu.tpl');
$template_page_menu->set('custom_count',$db->GetOne("SELECT COUNT(*) FROM ".T_SETTINGS." WHERE grouptitle='custom'"));
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>