<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_products','admin_products_pricing','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_products_pricing_view');

$tablegateway = $PMDR->get('TableGateway',T_PRODUCTS_PRICING);

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_products_pricing_delete');
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." WHERE pricing_id=?",array($_GET['id']))) {
        $tablegateway->delete($_GET['id']);
        $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_products_pricing'))),'delete');
    }
    redirect();
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_content_page.tpl');

if(!isset($_GET['action'])) {
    $template_content->set('title',$PMDR->getLanguage('admin_products_pricing'));
    $table_list = $PMDR->get('TableList');
    $table_list->form = true;
    $table_list->addColumn('id',$PMDR->getLanguage('admin_products_pricing_id'));
    $table_list->addColumn('name',$PMDR->getLanguage('admin_products_pricing_product'));
    $table_list->addColumn('setup_price',$PMDR->getLanguage('admin_products_pricing_setup'));
    $table_list->addColumn('price',$PMDR->getLanguage('admin_products_pricing_price'));
    $table_list->addColumn('period',$PMDR->getLanguage('admin_products_pricing_period'));
    $table_list->addColumn('ordering',$PMDR->getLanguage('admin_products_pricing_order').' [<a href="" onclick="updateOrdering(\''.T_PRODUCTS_PRICING.'\',\'table_list_form\'); return false;">'.$PMDR->getLanguage('admin_update').'</a>]');
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $paging = $PMDR->get('Paging');
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS ".T_PRODUCTS.".name, ".T_PRODUCTS_PRICING.".* FROM ".T_PRODUCTS_PRICING." INNER JOIN ".T_PRODUCTS." ON ".T_PRODUCTS_PRICING.".product_id=".T_PRODUCTS.".id ORDER BY ".T_PRODUCTS.".id, ".T_PRODUCTS_PRICING.".ordering LIMIT ".$paging->limit1.",".$paging->limit2);
    $paging->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        $records[$key]['period'] = $record['period_count'].' '.$PMDR->getLanguage($record['period']);
        $records[$key]['name'] = '<a href="admin_products.php?action=edit&id='.$record['product_id'].'">'.$PMDR->get('Cleaner')->clean_output($record['name']).'</a>';
        $records[$key]['ordering'] = '<input id="ordering_'.$record['id'].'" style="width: 30px" type="text" name="order_'.$record['id'].'" value="'.$record['ordering'].'">';
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
        if(!$db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." WHERE pricing_id=?",array($record['id']))) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $table_list->addPaging($paging);
    $template_content->set('content',$table_list->render());
} else {
    if(!$db->GetOne("SELECT COUNT(*) FROM ".T_PRODUCTS)) {
        $PMDR->addMessage('error',$PMDR->getLanguage('admin_products_pricing_add_product_error'));
        redirect(BASE_URL_ADMIN.'/admin_products.php?action=add');
    }
    $PMDR->get('Authentication')->checkPermission('admin_products_pricing_edit');
    $form = $PMDR->get('Form');
    $form->addFieldSet('product_pricing_details',array('legend'=>$PMDR->getLanguage('admin_products_pricing')));
    $products = $db->GetAssoc("SELECT id, name FROM ".T_PRODUCTS." ORDER BY ordering");
    $form->addField('product_id','select',array('label'=>$PMDR->getLanguage('admin_products_pricing_product'),'fieldset'=>'product_pricing_details','first_option'=>'Select Product','options'=>$products,'help'=>$PMDR->getLanguage('admin_products_pricing_help_product_id')));
    if(isset($_GET['product_id'])) {
        $form->setFieldAttribute('product_id','value',$_GET['product_id']);
    }
    $form->addField('active','checkbox',array('value'=>1,'label'=>$PMDR->getLanguage('admin_products_pricing_active'),'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_active_help')));
    $form->addField('hidden','checkbox',array('label'=>$PMDR->getLanguage('admin_products_pricing_hidden'),'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_hidden_help')));
    $form->addField('setup_price','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_setup'),'fieldset'=>'product_pricing_details','value'=>'0.00','help'=>$PMDR->getLanguage('admin_products_pricing_help_setup_price')));
    $form->addField('price','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_price'),'fieldset'=>'product_pricing_details','value'=>'0.00','help'=>$PMDR->getLanguage('admin_products_pricing_help_price')));
    $form->addField('period','select',array('label'=>$PMDR->getLanguage('admin_products_pricing_period'),'fieldset'=>'product_pricing_details','value'=>'days','options'=>array('days'=>$PMDR->getLanguage('days'),'months'=>$PMDR->getLanguage('months'),'years'=>$PMDR->getLanguage('years')),'help'=>$PMDR->getLanguage('admin_products_pricing_help_period')));
    $form->addField('period_count','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_period_count'),'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_help_period_count')));
    $form->addField('label','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_label'),'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_help_label')));
    $form->addField('prorate','checkbox',array('label'=>$PMDR->getLanguage('admin_products_pricing_prorate'),'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_help_prorate')));
    $form->addField('prorate_day','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_prorate_day'),'fieldset'=>'product_pricing_details','value'=>1,'help'=>$PMDR->getLanguage('admin_products_pricing_help_prorate_day')));
    $form->addField('prorate_day_next_month','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_prorate_day_next_month'),'fieldset'=>'product_pricing_details','value'=>15,'help'=>$PMDR->getLanguage('admin_products_pricing_help_prorate_day_next_month')));
    $form->addField('ordering','text',array('label'=>$PMDR->getLanguage('admin_products_pricing_order'),'fieldset'=>'product_pricing_details','value'=>'0','help'=>$PMDR->getLanguage('admin_products_pricing_help_ordering')));
    $form->addField('user_limit','text_unlimited',array('label'=>$PMDR->getLanguage('admin_products_pricing_user_limit'),'fieldset'=>'product_pricing_details','value'=>'0','help'=>$PMDR->getLanguage('admin_products_pricing_help_user_limit')));
    $activate_options = array(
        'immediate'=>$PMDR->getLanguage('admin_products_pricing_activate_immediate'),
        'payment'=>$PMDR->getLanguage('admin_products_pricing_activate_payment'),
        'approved'=>$PMDR->getLanguage('admin_products_pricing_activate_approved')
    );
    $form->addField('renewable','checkbox',array('label'=>$PMDR->getLanguage('admin_products_pricing_renewable'),'value'=>1,'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_help_renewable')));
    $form->addField('activate','select',array('label'=>$PMDR->getLanguage('admin_products_pricing_activate'),'value'=>'payment','options'=>$activate_options,'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_help_activate')));
    $email_options = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type='order' AND custom=1");
    foreach($email_options AS $id) {
        $email_options[$id] = $PMDR->getLanguage('email_templates_'.$id.'_name');
    }
    $form->addField('activate_email','select',array('label'=>$PMDR->getLanguage('admin_products_pricing_activate_email'),'first_option'=>'','options'=>$email_options,'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_activate_email_help')));
    $form->addFieldNote('activate_email','<a class="btn btn-default btn-xs" target="_blank" href="admin_email_templates.php?action=add">'.$PMDR->getLanguage('admin_products_pricing_add_email_template').'</a>');
    $suspend_action_options = array(
        'suspend'=>$PMDR->getLanguage('admin_products_pricing_suspend'),
        'cancel'=>$PMDR->getLanguage('admin_products_pricing_cancel'),
        'product_change'=>$PMDR->getLanguage('admin_products_pricing_change_product'),
        'nothing'=>$PMDR->getLanguage('admin_products_pricing_do_nothing'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $form->addField('overdue_action','select',array('label'=>$PMDR->getLanguage('admin_products_pricing_overdue_action'),'value'=>'suspend','options'=>$suspend_action_options,'fieldset'=>'product_pricing_details','help'=>$PMDR->getLanguage('admin_products_pricing_overdue_action_help')));
    $form->addField('overdue_pricing_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_products_pricing_change_to'),'fieldset'=>'product_pricing_details','options'=>array('type'=>'products_tree','hidden'=>true)));
    $form->addDependency('overdue_pricing_id',array('type'=>'display','field'=>'overdue_action','value'=>'product_change'));
    if($gateways = $db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1")) {
        $form->addField('gateway_ids','checkbox',array('label'=>$PMDR->getLanguage('admin_products_pricing_gateway_ids'),'fieldset'=>'product_pricing_details','value'=>array_keys($gateways),'options'=>$gateways,'help'=>$PMDR->getLanguage('admin_products_pricing_help_gateway_ids')));
        $form->addValidator('gateway_ids',new Validate_NonEmpty(false));
    }
    if($_GET['action'] == 'edit') {
        $form->addFieldSet('product_pricing_update',array('legend'=>$PMDR->getLanguage('admin_products_pricing_recalculate')));
        $form->addField('recalculate','checkbox',array('label'=>$PMDR->getLanguage('admin_products_pricing_recalculate'),'fieldset'=>'product_pricing_update','help'=>$PMDR->getLanguage('admin_products_pricing_help_recalculate')));
        $form->addField('next_date','date',array('label'=>$PMDR->getLanguage('admin_products_pricing_next_date'),'fieldset'=>'product_pricing_update','help'=>$PMDR->getLanguage('admin_products_pricing_help_next_date')));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('product_id',new Validate_NonEmpty());
    $form->addValidator('period',new Validate_NonEmpty());
    $form->addValidator('period_count',new Validate_NonEmpty());
    $form->addValidator('price',new Validate_NonEmpty());
    $form->addValidator('setup_price',new Validate_NonEmpty());

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_products_pricing_edit'));
        $product_pricing = $tablegateway->getRow(array('id'=>$_GET['id']));
        $product_pricing['gateway_ids'] = explode(',',$product_pricing['gateway_ids']);
        $form->loadValues($product_pricing);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_products_pricing_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        $data['gateway_ids'] = implode(',',(array) $data['gateway_ids']);
        if($data['overdue_action'] != 'product_change') {
            $data['overdue_pricing_id'] = null;
        }
        if($form->getFieldValue('prorate') AND $form->getFieldValue('period') == 'days') {
            $form->addError('Prorating is only available for month and year billing periods.','prorate');
        }
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            $PMDR->get('Cache')->delete('compare');
            if($_GET['action']=='add') {
                $tablegateway->insert($data);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['price'].' ('.$data['period_count'].' '.$data['period'].')',$PMDR->getLanguage('admin_products_pricing'))),'insert');
                redirect();
            } elseif($_GET['action'] == 'edit') {
                $tablegateway->update($data, array('id'=>$_GET['id']));
                if($data['recalculate']) {
                    $PMDR->get('Products')->syncPricing($data, $_GET['id']);
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($data['price'].' ('.$data['period_count'].' '.$data['period'].')',$PMDR->getLanguage('admin_products_pricing'))),'update');
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

$template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_products_menu.tpl');

include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>