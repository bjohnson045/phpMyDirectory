<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->get('Authentication')->authenticate();

$PMDR->loadLanguage(array('admin_documents','admin_listings','admin_users'));

$PMDR->get('Authentication')->checkPermission('admin_listings_view');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(isset($_GET['listing_id'])) {
    $listing = $PMDR->get('Listings')->getRow($_GET['listing_id']);
    if(!$listing) {
        redirect();
    } else {
        $template_content->set('listing_header',$PMDR->get('Listing',$listing['id'])->getAdminHeader('documents'));
        $template_content->set('users_summary_header',$PMDR->get('User',$listing['user_id'])->getAdminSummaryHeader('orders'));
    }
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_listings_delete');
    $PMDR->get('Documents')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_documents'))),'delete');
    redirect(array('listing_id'=>$_GET['listing_id']));
}

if($_GET['action'] == 'download') {
    $PMDR->get('Documents')->download($_GET['id']);
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_documents'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_documents_id'));
    $table_list->addColumn('listing_id',$PMDR->getLanguage('admin_documents_listing_id'));
    $table_list->addColumn('type',$PMDR->getLanguage('admin_documents_type'));
    $table_list->addColumn('title',$PMDR->getLanguage('admin_documents_title'));
    $table_list->addColumn('description',$PMDR->getLanguage('admin_documents_description'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $where_sql = '';
    if(isset($listing)) {
        $where[] = 'listing_id=?';
        $where_variables[] = $listing['id'];
    }
    if(count($where)) {
        $where_sql = 'WHERE '.implode(' AND ',$where);
    }
    $where_variables[] = $paging->limit1;
    $where_variables[] = $paging->limit2;
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_DOCUMENTS." $where_sql ORDER BY date DESC LIMIT ?,?",$where_variables);
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['description'] = $PMDR->get('Cleaner')->clean_output($record['description']);
        $record['type'] = $PMDR->get('HTML')->icon($record['extension'],array('label'=>$record['extension']));
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>URL_NOQUERY.'?action=edit&listing_id='.$record['listing_id'].'&id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete&listing_id='.$record['listing_id'].'&id='.$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('download',array('href'=>URL_NOQUERY.'?action=download&listing_id='.$record['listing_id'].'&id='.$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_listings_edit');
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('document_details',array('legend'=>$PMDR->getLanguage('admin_documents')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_documents_title'),'fieldset'=>'document_details'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_documents_description'),'fieldset'=>'document_details'));
    $form->addField('document','file',array('label'=>$PMDR->getLanguage('admin_documents_document'),'fieldset'=>'document_details'));
    $form->addFieldNote('document',$PMDR->getLanguage('file_size_limit_kb',$PMDR->getConfig('documents_size')));
    $PMDR->get('Fields')->addToForm($form,'documents',array('fieldset'=>'document_details'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->addValidator('title',new Validate_NonEmpty());

    $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_documents_listing_id'),'fieldset'=>'document_details','value'=>$_GET['listing_id']));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_documents_edit'));
        $form->loadValues($PMDR->get('Documents')->getRow($_GET['id']));
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_documents_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        $form->addField('listing_id','hidden',array('label'=>$PMDR->getLanguage('admin_documents_listing_id'),'fieldset'=>'documents_details','value'=>$_GET['listing_id']));

        $document_count = $db->GetOne("SELECT COUNT(*) AS count FROM ".T_DOCUMENTS." WHERE listing_id=?",array($_GET['listing_id']));

        if($_FILES['document']['size']/1024 > $PMDR->getConfig('documents_size')) {
            $form->addError($PMDR->getLanguage('admin_documents_size_limit',array($PMDR->getConfig('documents_size'))));
        }
        if($document_count >= $listing['documents_limit'] AND $_GET['action'] != 'edit') {
            $form->addError($PMDR->getLanguage('admin_documents_limit',$listing['documents_limit']));
        }
        if(!$PMDR->get('Documents')->isValidType($_FILES['document']['name']) AND $_FILES['document']['name'] != '') {
            $form->addError($PMDR->getLanguage('admin_documents_format',$PMDR->getConfig('documents_allow')));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Documents')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_documents'))),'insert');
                redirect(array('listing_id'=>$_GET['listing_id']));
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Documents')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_documents'))),'update');
                redirect(array('listing_id'=>$_GET['listing_id']));
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>