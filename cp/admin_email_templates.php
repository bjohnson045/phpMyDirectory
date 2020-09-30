<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email_templates','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_email_templates');

$email_templates = $PMDR->get('Email_Templates');

// Delete an email template - NOTE: need email template class?
if($_GET['action'] == 'delete') {
    if($db->GetOne("SELECT COUNT(*) FROM ".T_EMAIL_TEMPLATES." WHERE id=? AND custom=1",array($_GET['id']))) {
        $email_templates->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_templates'))),'delete');
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_email_templates.tpl');
$template_page_menu[] = array('content'=>$PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_templates_menu.tpl'));

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->all_one_page = true;
    $table_list->addColumn('type',$PMDR->getLanguage('admin_email_templates_type'));
    $table_list->addColumn('name',$PMDR->getLanguage('admin_email_templates_name'));
    $table_list->addColumn('disable',$PMDR->getLanguage('admin_email_templates_disable'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $paging->all_one_page = true;

    $where = array();
    if(isset($_GET['filter'])) {
        if($_GET['filter'] == 'administrator') {
            $where[] = "id LIKE 'admin_%'";
        } elseif($_GET['filter'] == 'notadministrator') {
            $where[] = "id NOT LIKE 'admin_%'";
        } elseif(in_array($_GET['filter'],array('general','invoice','listing','order','review','user','event','blog','job'))) {
            $where[] = "type='".$_GET['filter']."'";
        }
    }
    if(count($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }

    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_EMAIL_TEMPLATES." $where ORDER BY type, id");
    $paging->setTotalResults($db->FoundRows());
    foreach($records as &$record) {
        $record['name'] = $PMDR->getLanguage('email_templates_'.$record['id'].'_name');
        if(strstr($record['id'],'admin_')) {
            $record['name'] .= ' <span class="label label-default pull-right">Administrator</span>';
        }
        $record['type'] = $PMDR->getLanguage('admin_email_templates_type_'.$record['type']);
        $record['disable'] = $PMDR->get('HTML')->icon($record['disable']);
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        // We do not want to allow deleting any core email templates
        if($record['custom']) {
            $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('title',$PMDR->getLanguage('admin_email_templates'));
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'edit') {
        $template = $email_templates->getRow($_GET['id']);
    }

    $form = $PMDR->get('Form');
    $form->enctype = 'multipart/form-data';
    $form->addFieldSet('details',array('legend'=>$PMDR->getLanguage('admin_email_templates_info')));
    $form->addFieldSet('email_settings',array('legend'=>$PMDR->getLanguage('admin_email_templates_settings')));
    $type_options = array(
        'general'=>$PMDR->getLanguage('admin_email_templates_type_general'),
        'user'=>$PMDR->getLanguage('admin_email_templates_type_user'),
        'order'=>$PMDR->getLanguage('admin_email_templates_type_order'),
        'listing'=>$PMDR->getLanguage('admin_email_templates_type_listing'),
        'review'=>$PMDR->getLanguage('admin_email_templates_type_review'),
        'event'=>$PMDR->getLanguage('admin_email_templates_type_event'),
        'blog'=>$PMDR->getLanguage('admin_email_templates_type_blog')
    );
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_email_templates_type'),'fieldset'=>'details','options'=>$type_options));
    if($_GET['action'] == 'add') {
        $form->addField('id','text',array('label'=>'Variable Name','fieldset'=>'details'));
        $form->addValidator('id',new Validate_NonEmpty());
        $form->addValidator('id',new Validate_Regex('/[a-z_]+/i',$PMDR->getLanguage('admin_email_templates_variable_format_error')));
    } elseif($_GET['action'] == 'edit') {
        $form->addField('id','custom',array('label'=>'Variable Name','fieldset'=>'details'));
    }

    $form->addField('disable','checkbox',array('label'=>$PMDR->getLanguage('admin_email_templates_disable'),'fieldset'=>'details','help'=>$PMDR->getLanguage('admin_email_templates_help_disable')));
    if($_GET['action'] == 'add' OR ($_GET['action'] == 'edit' AND !strstr($template['id'],'admin_'))) {
        $form->addField('moderate','checkbox',array('fieldset'=>'details'));
    }
    $form->addField('name','text',array('label'=>$PMDR->getLanguage('admin_email_templates_name'),'fieldset'=>'details','help'=>$PMDR->getLanguage('admin_email_templates_help_name')));
    $form->addField('from_name','text',array('label'=>$PMDR->getLanguage('admin_email_templates_from_name'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_from_name')));
    $form->addField('from_address','text',array('label'=>$PMDR->getLanguage('admin_email_templates_from_address'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_from_address')));
    $form->addField('reply_name','text',array('label'=>$PMDR->getLanguage('admin_email_templates_reply_name'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_reply_name')));
    $form->addField('reply_address','text',array('label'=>$PMDR->getLanguage('admin_email_templates_reply_address'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_reply_address')));
    $form->addField('recipients','textarea',array('label'=>$PMDR->getLanguage('admin_email_templates_recipients'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_recipients')));
    $form->addField('recipients_bcc','textarea',array('label'=>$PMDR->getLanguage('admin_email_templates_recipients_bcc'),'fieldset'=>'email_settings','help'=>$PMDR->getLanguage('admin_email_templates_help_recipients_bcc')));
    $form->addField('subject','text',array('label'=>$PMDR->getLanguage('admin_email_templates_subject'),'fieldset'=>'details','help'=>$PMDR->getLanguage('admin_email_templates_help_subject')));
    $form->addField('body_plain','textarea',array('label'=>$PMDR->getLanguage('admin_email_templates_body_plain'),'fieldset'=>'details','style'=>'width: 600px','fullscreen'=>true,'help'=>$PMDR->getLanguage('admin_email_templates_help_body_plain')));
    $form->addField('body_html','htmleditor',array('label'=>$PMDR->getLanguage('admin_email_templates_body_html'),'fieldset'=>'details','help'=>$PMDR->getLanguage('admin_email_templates_help_body_html')));
    $form->addField('attachments','file',array('label'=>$PMDR->getLanguage('admin_email_templates_attachments'),'fieldset'=>'details','multiple'=>true));
    if(isset($template) AND !empty($template['attachments']) AND $template_attachments = unserialize($template['attachments']) AND count($template_attachments) > 0) {
        $form->addField('attachments_current','checkbox',array('label'=>$PMDR->getLanguage('admin_email_templates_attachments_current'),'fieldset'=>'details','options'=>array_combine($template_attachments,$template_attachments)));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('recipients',new Validate_Email(false));
    $form->addValidator('subject',new Validate_NonEmpty());
    $form->addValidator('body_plain',new Validate_NonEmpty());
    $form->addValidator('name',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_email_templates_edit'));

        if(!in_array($template['id'],array('listings_send_email_friend','admin_contact_submission','listings_send_email'))) {
            $form->addValidator('from_address',new Validate_Email(false));
        }

        if(!$template['custom']) {
            $form->setFieldAttribute('type','type','hidden');
        }
        $template_page_menu[] = array('title'=>$PMDR->getLanguage('admin_email_templates_variables'),'content'=>$PMDR->get('Email_Templates')->getVariablesTemplate($template['id'],$template['type']),'type'=>'content');
        $form->loadValues($template);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_email_templates_add'));
        $form->addValidator('from_address',new Validate_Email(false));
        $PMDR->loadJavascript('
        <script type="text/javascript">
        $(document).ready(function(){
            $("#type").change(function(){
                $("#email_template_variables").remove();
                $.ajax({ data: ({ action: "admin_email_templates_show_variables", type: $(this).val() }), success: function(data){ $("#side_menu").append(data); }});
            });
            $("#type").trigger("change");
        });
        </script>
        ',50);
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->deletePrefix('language_');
            if($_GET['action']=='add') {
                $data['custom'] = 1;
                $email_templates->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['name'],$PMDR->getLanguage('admin_email_templates'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $email_templates->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['name'],$PMDR->getLanguage('admin_email_templates'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>