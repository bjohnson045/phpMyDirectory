<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_discount_codes'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_discount_codes_view');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

/** @var Discount_Codes */
$discount_codes = $PMDR->get('Discount_Codes');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_discount_codes_delete');
    $discount_codes->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_discount_codes'))),'delete');
    redirect();
}

if($_GET['action'] == 'expire') {
    $PMDR->get('Authentication')->checkPermission('admin_discount_codes_edit');
    $discount_codes->expire($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_discount_codes'))),'update');
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('title',$PMDR->getLanguage('admin_discount_codes_title'));
    $table_list->addColumn('code',$PMDR->getLanguage('admin_discount_codes_code'));
    $table_list->addColumn('value',$PMDR->getLanguage('admin_discount_codes_value'));
    $table_list->addColumn('date_expire',$PMDR->getLanguage('admin_discount_codes_expire_date'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $table_list->setTotalResults($discount_codes->getCount());
    $records = $discount_codes->getRows();
    foreach($records as &$record) {
        $record['title'] = $PMDR->get('Cleaner')->clean_output($record['title']);
        $record['code'] = $PMDR->get('Cleaner')->clean_output($record['code']);
        $record['value'] = $PMDR->get('Cleaner')->clean_output($record['value']);
        $record['date_start'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_start']);
        if(strtotime($record['date_expire']) < time()) {
            $record['date_expire'] = '<span class="text-danger">'.$PMDR->get('Dates_Local')->formatDateTime($record['date_expire']).'</span>';
        } else {
            $record['date_expire'] = $PMDR->get('Dates_Local')->formatDateTime($record['date_expire']).' <a href="admin_discount_codes.php?action=expire&id='.$record['id'].'" title="Expire Now"><i class="text-danger glyphicon glyphicon-minus-sign"></i></a>';
        }
        $record['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
    }
    $table_list->addRecords($records);
    $template_content->set('title',$PMDR->getLanguage('admin_discount_codes'));
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_discount_codes_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('information',array('legend'=>$PMDR->getLanguage('admin_discount_codes')));
    $form->addField('title','text',array('label'=>$PMDR->getLanguage('admin_discount_codes_title'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_help_title')));
    $form->addField('code','text',array('label'=>$PMDR->getLanguage('admin_discount_codes_code'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_help_code')));
    $form->addFieldNote('code','<a id="code_generate" href="" class="btn btn-default btn-xs">'.$PMDR->getLanguage('admin_discount_codes_generate').'</a>');
    $PMDR->loadJavascript('
        <script type="text/javascript">
        $(document).ready(function() {
            $("#code_generate").click(function(e) {
                e.preventDefault();
                $.ajax({ data: ({ action: "random_string", length: 5 }), success:
                    function(data) {
                        $("#code").val(data);
                        $("#code").focus();
                    }
                });
            });
        });
        </script>
    ',100);
    $form->addField('type','select',array('label'=>$PMDR->getLanguage('admin_discount_codes_type'),'fieldset'=>'information','options'=>array('onetime'=>$PMDR->getLanguage('admin_discount_codes_onetime'),'recurring'=>$PMDR->getLanguage('admin_discount_codes_recurring')),'help'=>$PMDR->getLanguage('admin_discount_codes_help_type')));
    $form->addField('discount_type','select',array('label'=>$PMDR->getLanguage('admin_discount_codes_discount_type'),'fieldset'=>'information','options'=>array('fixed'=>$PMDR->getLanguage('admin_discount_codes_fixed'),'percentage'=>$PMDR->getLanguage('admin_discount_codes_percentage')),'help'=>$PMDR->getLanguage('admin_discount_codes_help_discount_type')));
    $form->addField('value','text',array('label'=>$PMDR->getLanguage('admin_discount_codes_value'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_help_value')));
    $form->addField('date_start','datetime',array('label'=>$PMDR->getLanguage('admin_discount_codes_start_date'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_help_date_start')));
    $form->addField('date_expire','datetime',array('label'=>$PMDR->getLanguage('admin_discount_codes_expire_date'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_help_date_expire')));
    $form->addField('used_limit','text_unlimited',array('label'=>$PMDR->getLanguage('admin_discount_codes_use_limit'),'fieldset'=>'information','value'=>'0','help'=>$PMDR->getLanguage('admin_discount_codes_help_used_limit')));
    $form->addField('user_used_limit','text_unlimited',array('label'=>$PMDR->getLanguage('admin_discount_codes_user_used_limit'),'fieldset'=>'information','value'=>'0','help'=>$PMDR->getLanguage('admin_discount_codes_help_user_used_limit')));
    $user_order_status = array(
        'all'=>$PMDR->getLanguage('admin_discount_codes_user_order_status_all'),
        'new'=>$PMDR->getLanguage('admin_discount_codes_user_order_status_new'),
        'old'=>$PMDR->getLanguage('admin_discount_codes_user_order_status_old')
    );
    $form->addField('user_order_status','radio',array('label'=>$PMDR->getLanguage('admin_discount_codes_user_order_status'),'fieldset'=>'information','value'=>'all','options'=>$user_order_status,'help'=>$PMDR->getLanguage('admin_discount_codes_help_user_order_status')));
    $form->addField('pricing_ids','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_discount_codes_applicable_pricing'),'fieldset'=>'information','value'=>'','options'=>array('type'=>'products_tree','hidden'=>true),'help'=>$PMDR->getLanguage('admin_discount_codes_help_pricing_ids')));
    $form->addField('pricing_ids_required','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_discount_codes_pricing_ids_required'),'fieldset'=>'information','value'=>'','options'=>array('type'=>'products_tree','hidden'=>true),'help'=>$PMDR->getLanguage('admin_discount_codes_help_pricing_ids_required')));
    if($gateways = $db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1")) {
        $form->addField('gateway_ids','checkbox',array('label'=>$PMDR->getLanguage('admin_discount_codes_applicable_gateways'),'fieldset'=>'information','value'=>array_keys($gateways),'options'=>$gateways,'help'=>$PMDR->getLanguage('admin_discount_codes_help_gateway_ids')));
        $form->addValidator('gateway_ids',new Validate_NonEmpty(false));
    }
    $form->addField('display','checkbox');
    $form->addField('comments','textarea',array('label'=>$PMDR->getLanguage('admin_discount_codes_comments'),'fieldset'=>'information','help'=>$PMDR->getLanguage('admin_discount_codes_comments_help')));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('title',new Validate_NonEmpty());
    $form->addValidator('code',new Validate_NonEmpty());
    $form->addValidator('value',new Validate_NonEmpty());
    $form->addValidator('date_start',new Validate_NonEmpty());
    $form->addValidator('date_expire',new Validate_NonEmpty());
    $form->addValidator('pricing_ids',new Validate_NonEmpty(false));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_discount_codes_edit'));
        $discount_code = $discount_codes->getRow($_GET['id']);
        $discount_code['gateway_ids'] = explode(',',$discount_code['gateway_ids']);
        $discount_code['pricing_ids'] = explode(',',$discount_code['pricing_ids']);
        $discount_code['pricing_ids_required'] = explode(',',$discount_code['pricing_ids_required']);
        $form->loadValues($discount_code);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_discount_codes_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();

        if($_GET['action'] == 'add') {
            if($PMDR->get('Discount_Codes')->codeExists($data['code'])) {
                $form->addError($PMDR->getLanguage('admin_discount_codes_exists'),'code');
            }
        }
        if($_GET['action'] == 'edit') {
            if($PMDR->get('Discount_Codes')->codeExists($data['code'],$_GET['id'])) {
                $form->addError($PMDR->getLanguage('admin_discount_codes_exists'),'code');
            }
        }

        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $data['pricing_ids'] = implode(',',(array) $data['pricing_ids']);
            $data['pricing_ids_required'] = implode(',',(array) $data['pricing_ids_required']);
            $data['gateway_ids'] = implode(',',(array) $data['gateway_ids']);
            if($_GET['action']=='add') {
                $discount_codes->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['title'],$PMDR->getLanguage('admin_discount_codes'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $discount_codes->update($data, array('id'=>$_GET['id']));
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['title'],$PMDR->getLanguage('admin_discount_codes'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_discount_codes_menu.tpl');
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>