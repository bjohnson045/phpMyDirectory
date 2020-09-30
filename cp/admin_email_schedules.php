<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_email','admin_email_log','admin_email_lists','admin_email_campaigns','admin_email_queue','admin_email_schedules','email_templates'));

$PMDR->get('Authentication')->authenticate();

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

$actions = array(
    'user_registration_after'=>'After User Registration',
    'user_registration_after_no_order'=>'After User Registration Without Order',
    'user_inactivity'=>'After User Inactivity',
    'listing_creation_after'=>'After Listing Creation',
    'order_after'=>'After Order',
    'order_after_active'=>'After Order Activated',
    'order_before_due'=>'Before Order Due'
);

if($_GET['action'] == 'delete') {
    $PMDR->get('Email_Schedules')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_email_schedules'))),'delete');
    redirect();
}

if(!isset($_GET['action'])) {
    $template_content->set('title','Email Schedules');
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title');
    $table_list->addColumn('action');
    $table_list->addColumn('email_template_id');
    $table_list->addColumn('manage');
    $paging = $PMDR->get('Paging');
    $where = array();
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS * FROM ".T_EMAIL_SCHEDULES." ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'email_template_id DESC')." LIMIT ?,?",array($paging->limit1,$paging->limit2));
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['email_template_id'] = $PMDR->getLanguage('email_templates_'.$record['email_template_id'].'_name');
        if(strstr($value,'admin_')) {
            $records[$key]['email_template_id'] .= ' (Administrator)';
        }
        $records[$key]['action'] = $actions[$record['action']];
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    if($_GET['action'] == 'edit') {
        $schedule = $db->GetRow("SELECT * FROM ".T_EMAIL_SCHEDULES." WHERE id=?",array($_GET['id']));
        if(!empty($schedule['data']) AND $data = unserialize($schedule['data'])) {
            $schedule = array_merge($schedule,unserialize($schedule['data']));
        }
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('email_schedule',array('legend'=>'Email Schedule'));
    $form->addField('title','text');
    $form->addField('active','checkbox');
    $action_onchange = '
        var template_type = $(\'#action\').val().substring(0,$(\'#action\').val().indexOf(\'_\'));
        $.ajax({
            data: ({
                action: \'admin_email_schedules_get_templates\',
                type: template_type
            }),
            success: function(options) {
                $(\'#email_template_id > option\').remove();
                $.each(options, function(val, text) {
                    $(\'#email_template_id\').append($(\'<option></option>\').val(val).html(text));
                });';
                if($_GET['action'] == 'edit') {
                    $action_onchange .= '$(\'#email_template_id\').val(\''.$schedule['email_template_id'].'\');';
                }
                $action_onchange .= '
            },
            dataType: \'json\'
        });
        if(template_type == \'order\') {
            $(\'#pricing_ids-control-group\').show();
        } else {
            $(\'#pricing_ids-control-group\').hide();
        }
    ';
    $form->addField('action','select',array('first_option'=>'','options'=>$actions,'onchange'=>$action_onchange));
    $form->addField('pricing_ids','tree_select_expanding_checkbox',array('value'=>'','options'=>array('type'=>'products_tree','hidden'=>true)));
    $form->addField('email_template_id','select');
    $form->addField('days','text',array('label'=>'Days','value'=>1));
    $form->addField('submit','submit');

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('email_template_id',new Validate_NonEmpty());
    $form->addValidator('action',new Validate_NonEmpty());
    $form->addValidator('days',new Validate_NonEmpty());

    $PMDR->loadJavascript('
        <script type="text/javascript">
        $(document).ready(function(){
            $("#pricing_ids-control-group").hide();
            $("#action").trigger("change");
        });
        </script>
    ',50);

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_email_schedules_edit'));
        $form->loadValues($schedule);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_email_schedules_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!empty($data['pricing_ids'])) {
            $data['data'] = serialize(array(
                'pricing_ids'=>$data['pricing_ids']
            ));
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $PMDR->get('Email_Schedules')->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_email_schedule'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Email_Schedules')->update($data,$_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_email_schedule'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>