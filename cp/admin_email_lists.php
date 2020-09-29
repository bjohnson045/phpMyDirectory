<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_email_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_manager');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if($_GET['action'] == 'delete') {
    $PMDR->get('Email_Lists')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_lists'))),'delete');
    redirect();
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_email_lists'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title');
    $table_list->addColumn('hidden');
    $table_list->addColumn('optout');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_EMAIL_LISTS." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'title DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['optout'] = $PMDR->get('HTML')->icon($record['optout']);
        $records[$key]['hidden'] = $PMDR->get('HTML')->icon($record['hidden']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->addFieldSet('email_campaign',array('legend'=>$PMDR->getLanguage('admin_email_lists_list')));
    $form->addField('title','text');
    $form->addField('description','textarea');
    //$form->addField('subscribe_notify','checkbox');
    //$form->addField('unsubscribe_notify','checkbox');
    if($email_marketing = $PMDR->get('Email_Marketing')) {
        if($lists = $email_marketing->getLists()) {
            $form->addField('email_marketing_list_id','select',array('label'=>$email_marketing->getMarketingName(),'first_option'=>'','options'=>$lists));
        }
    }
    $form->addField('hidden','checkbox');
    $form->addField('optout','checkbox');
    $form->addField('addall','checkbox');
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_email_lists_edit'));
        $edit_list = $db->GetRow("SELECT * FROM ".T_EMAIL_LISTS." WHERE id=?",array($_GET['id']));
        $form->loadValues($edit_list);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_email_lists_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Email_Lists')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_email_list'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Email_Lists')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_email_list'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>