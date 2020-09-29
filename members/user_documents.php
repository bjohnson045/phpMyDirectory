<?php
define('PMD_SECTION','members');

include('../defaults.php');

$PMDR->loadLanguage(array('user_documents','user_orders'));

$PMDR->get('Authentication')->authenticate();

if(!$PMDR->get('Authentication')->checkPermission('user_advertiser')) {
    redirect(BASE_URL.MEMBERS_FOLDER);
}

$documents = $PMDR->get('Documents');
$listings = $PMDR->get('Listings');
$users = $PMDR->get('Users');

$user = $users->getRow($PMDR->get('Session')->get('user_id'));

$listing = $listings->getRow($_GET['listing_id']);

$PMDR->setAdd('page_title',$PMDR->getLanguage('user_documents'));
$PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_documents'));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'index.php','text'=>$PMDR->getLanguage('user_general_my_account')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders.php','text'=>$PMDR->getLanguage('user_general_orders')));
$PMDR->setAddArray('breadcrumb',array('link'=>BASE_URL.MEMBERS_FOLDER.'user_orders_view.php?id='.$db->GetOne("SELECT id FROM ".T_ORDERS." WHERE type='listing_membership' AND type_id=?",array($listing['id'])),'text'=>$PMDR->getLanguage('user_orders_view')));

if($user['id'] != $listing['user_id']) {
    redirect(BASE_URL.MEMBERS_FOLDER.'index.php');
}

if($_GET['action'] == 'delete') {
    if($db->GetRow("SELECT id FROM ".T_DOCUMENTS." WHERE id=? AND listing_id=? LIMIT 1",array($_GET['id'],$listing['id']))) {
        $documents->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('user_documents'))),'delete');
    }
    redirect(array('listing_id'=>$listing['id']));
}

if($_GET['action'] == 'download') {
    $documents->download($_GET['id']);
}

$template_content = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/user_documents.tpl');

$PMDR->set('page_header',$PMDR->get('Listing',$listing['id'])->getUserHeader('documents'));

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('user_documents'));
    $template_content->set('add_link',BASE_URL.MEMBERS_FOLDER.'user_documents.php?action=add&listing_id='.$listing['id']);

    $table_list = $PMDR->get('TableList',array('template'=>PMDROOT.TEMPLATE_PATH.'members/blocks/user_documents_list.tpl'));
    $table_list->addColumn('id',$PMDR->getLanguage('user_documents_id'));
    $table_list->addColumn('type',$PMDR->getLanguage('user_documents_type'));
    $table_list->addColumn('title',$PMDR->getLanguage('user_documents_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('user_documents_description'));
    $table_list->addColumn('manage',$PMDR->getLanguage('user_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_DOCUMENTS." WHERE listing_id=? ORDER BY date DESC LIMIT ?,?",array($listing['id'],$paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $records[$key]['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $template_content_form = $PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/user_documents_form.tpl');

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('document_details',array('legend'=>$PMDR->getLanguage('user_documents_document')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('user_documents_title'),'fieldset'=>'document_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('user_documents_description'),'fieldset'=>'document_details'));
    $form->addField('document','file',array('label'=>$PMDR->getLanguage('user_documents_document'),'fieldset'=>'document_details'));
    $fields = $PMDR->get('Fields')->addToForm($form,'documents',array('fieldset'=>'document_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('user_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());

    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('user_documents_listing_id'),'fieldset'=>'document_details','value'=>$listing['id']));

    if($_GET['action'] == 'edit') {
        $PMDR->set('page_title',$PMDR->getLanguage('user_documents_edit'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_documents_edit'));
        $form->loadValues($documents->getRow($_GET['id']));
    } else {
        $form->addValidator('document',new Validate_NonEmpty_File());
        $PMDR->set('page_title',$PMDR->getLanguage('user_documents_add'));
        $PMDR->set('meta_title',$listing['title'].' '.$PMDR->getLanguage('user_documents_add'));
    }

    $form->addValidator('title',new Validate_Banned_Words());
    $form->addValidator('description',new Validate_Banned_Words());

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        $document_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_DOCUMENTS." WHERE listing_id=?",array($listing['id']));

        if(isset($data['document']) AND $data['document']['size']/1024 > $PMDR->getConfig('documents_size')) {
            $form->addError($PMDR->getLanguage('user_documents_size_limit',array($PMDR->getConfig('documents_size'))));
        }
        if($document_count >= $listing['documents_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('user_documents_limit',$listing['documents_limit']));
        }
        if(!$documents->isValidType($data['document']['name']) AND $data['document']['name'] != '') {
            $form->addError($PMDR->getLanguage('user_documents_format',$PMDR->getConfig('documents_allow')));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $documents->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('user_documents'))),'insert');
                redirect(array('listing_id'=>$listing['id']));
            } elseif($_GET['action'] == 'edit') {
                $documents->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('user_documents'))),'update');
                redirect(array('listing_id'=>$listing['id']));
            }
        }
    }
    $template_content_form->set('form',$form);
    $template_content_form->set('fields',$fields);
    $template_content->set('content',$template_content_form);
}

include(PMDROOT.'/includes/template_setup.php');
?>