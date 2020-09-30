<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_email_log'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_manager');

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if($_GET['action'] == 'delete') {
    if($db->GetOne("SELECT COUNT(*) FROM ".T_EMAIL_QUEUE." WHERE campaign_id=?",array($_GET['id']))) {
        $PMDR->addMessage('error','An email campaign may not be deleted while emails are in the email queue from this campaign.');
    } else {
        $PMDR->get('Email_Campaigns')->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_campaigns'))),'delete');
    }
    redirect();
}

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_email_campaigns'));
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title');
    $table_list->addColumn('type');
    $table_list->addColumn('date');
    $table_list->addColumn('date_sent');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_EMAIL_CAMPAIGNS." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'title DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        if($PMDR->get('Dates')->isZero($records[$key]['date_sent'])) {
            $records[$key]['date_sent'] = '-';
        } else {
            $records[$key]['date_sent'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_sent']);
        }
        $records[$key]['type'] = $PMDR->getLanguage('admin_email_campaigns_'.$record['type']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('arrow_green',array('label'=>$PMDR->getLanguage('admin_email_campaigns_send'),'href'=>'admin_email_campaigns_send.php?id='.$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('email_campaign',array('legend'=>$PMDR->getLanguage('admin_email_campaigns_campaign')));
    $types = array(
        'users'=>$PMDR->getLanguage('admin_email_campaigns_users'),
        'listings'=>$PMDR->getLanguage('admin_email_campaigns_listings')
    );
    $form->addField('type','select',array('options'=>$types));
    $form->addField('title','text');
    $form->addField('from_name','text');
    $form->addField('from_email','text');
    $form->addField('reply_email','text');
    $form->addField('bounce_email','text');
    $form->addField('subject','text');
    $form->addField('body_text','textarea');
    $form->addField('body_html','htmleditor');
    $form->addField('attachment','file');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('subject',new Validate_NonEmpty());
    $form->addValidator('body_text',new Validate_NonEmpty());
    $form->addValidator('from_name',new Validate_NonEmpty());
    $form->addValidator('from_email',new Validate_Email(true));
    $form->addValidator('reply_email',new Validate_Email(false));
    $form->addValidator('bounce_email',new Validate_Email(false));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_email_campaigns_edit'));
        $record = $db->GetRow("SELECT * FROM ".T_EMAIL_CAMPAIGNS." WHERE id=?",array($_GET['id']));
        $form->addField('attachment_current','custom',array('html'=>'<a href="'.get_file_url(TEMP_UPLOAD_PATH.$record['attachment']).'">'.$record['attachment'].'</a>'));
        $form->addField('attachment_delete','checkbox',array('value'=>'0'));
        $form->loadValues($record);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_email_campaigns_add'));
    }

    $form->addField('submit','submit');

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!empty($data['attachment']) AND DEMO_MODE) {
            unset($data['attachment']);
            $PMDR->addMessage('notice','Email campaign attachments are disabled in the demo.');
        }
        if($_GET['action'] == 'edit') {
            if($db->GetOne("SELECT COUNT(*) FROM ".T_EMAIL_QUEUE." WHERE campaign_id=?",array($_GET['id']))) {
                $form->addError($PMDR->getLanguage('admin_email_campaigns_edit_error'));
            }
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $data['date'] = $PMDR->get('Dates')->dateTimeNow();
                $PMDR->get('Email_Campaigns')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_email_campaigns'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Email_Campaigns')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_email_campaigns'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu[] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl'));

if(isset($_GET['action'])) {
    $template_page_menu_variables = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_campaigns_variables_menu.tpl');
    $template_page_menu_variables->set('general_variables',$PMDR->get('Email_Variables')->getGeneralKeys());
    $template_page_menu_variables->set('user_variables',$PMDR->get('Email_Variables')->getUserKeys());
    $template_page_menu_variables->set('listing_variables',$PMDR->get('Email_Variables')->getListingKeys());
    $template_page_menu[] = array('title'=>'Variables','content'=>$template_page_menu_variables);
}

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>