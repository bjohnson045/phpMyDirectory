<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_zip_codes'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_zip_codes_view');

/** @var Zip_Codes */
$zip_codes = $PMDR->get('Zip_Codes');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_zip_codes_delete');
    $zip_codes->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_zip_codes'))),'delete');
    redirect();
}

if($_GET['action'] == 'clear') {
    $PMDR->get('Authentication')->checkPermission('admin_zip_codes_delete');
    $zip_codes->clear();
    $PMDR->addMessage('success',$PMDR->getLanguage('admin_zip_codes_cleared'),'delete');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('zipcode',$PMDR->getLanguage('admin_zip_codes_zip'));
    $table_list->addColumn('lat',$PMDR->getLanguage('admin_zip_codes_lat'));
    $table_list->addColumn('lon',$PMDR->getLanguage('admin_zip_codes_lon'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($zip_codes->getCount());
    if($_GET['action'] == 'search') {
        $records = $zip_codes->search($_GET['zipcode'],$_GET['radius'],$table_list->page_data['limit1'],$table_list->page_data['limit2']);
        $table_list->setTotalResults($db->GetOne("SELECT FOUND_ROWS()"));
    } else {
        $table_list->setTotalResults($zip_codes->getCount());
        $records = $zip_codes->getRows(array(),array('zipcode'=>'ASC'),$table_list->page_data['limit1'],$table_list->page_data['limit2']);
    }
    foreach($records as $key=>$record) {
        $records[$key]['zipcode'] = '<a href="admin_zip_codes.php?action=edit&id='.$record['id'].'">'.$PMDR->get('Cleaner')->clean_output($record['zipcode']).'</a>';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('globe',array('target'=>'_blank','href'=>'https://maps.google.com/?q='.$record['zipcode'],'id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_zip_codes'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_zip_codes_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_zip_codes_zip')));
    $form->addField('zipcode','text',array('label'=>$PMDR->getLanguage('admin_zip_codes_zip'),'fieldset'=>'information'));
    $form->addField('lat','text',array('label'=>$PMDR->getLanguage('admin_zip_codes_lat'),'fieldset'=>'information'));
    $form->addField('lon','text',array('label'=>$PMDR->getLanguage('admin_zip_codes_lon'),'fieldset'=>'information'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('zipcode',new Validate_NonEmpty());
    $form->addValidator('lon',new Validate_NonEmpty());
    $form->addValidator('lat',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_zip_codes_edit'));
        $form->loadValues($zip_codes->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_zip_codes_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $zip_codes->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['zipcode'],$PMDR->getLanguage('admin_zip_codes'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $zip_codes->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['zipcode'],$PMDR->getLanguage('admin_zip_codes'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_zip_codes_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>