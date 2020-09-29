<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_orders','admin_users','admin_products','general_locations','email_templates'));
// Not used: $PMDR->getLanguage('admin_orders_term')
// Not used: $PMDR->getLanguage('admin_orders_ban_ip')

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_orders_view');

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_orders_delete');
    $PMDR->get('Orders')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_orders'))),'delete');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

if(isset($_POST['table_list_submit'])) {
    if(isset($_POST['table_list_checkboxes']) AND count($_POST['table_list_checkboxes'])) {
        if(in_array($_POST['action'],array('active','pending','completed','suspended','canceled','fraud'))) {
            $PMDR->get('Authentication')->checkPermission('admin_orders_edit');
            foreach($_POST['table_list_checkboxes'] AS $id) {
                $PMDR->get('Orders')->changeStatus($id,$_POST['action']);
                if($_POST['notify']) {
                    $PMDR->get('Email_Templates')->send('order_status_change',array('order_id'=>$id));
                }
            }
        } elseif($_POST['action'] == 'delete') {
            $PMDR->get('Authentication')->checkPermission('admin_orders_delete');
            foreach($_POST['table_list_checkboxes'] AS $id) {
                $PMDR->get('Orders')->delete($id);
            }
        }
    }
    $PMDR->addMessage('success','Orders updated');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders.tpl');


if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('orders'));
}

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $form_search = $PMDR->getNew('Form');
    $form_search->method = 'GET';
    $form_search->addFieldSet('orders_search',array('legend'=>'Orders Search'));
    $status_options = array(
        'active'=>$PMDR->getLanguage('active'),
        'completed'=>$PMDR->getLanguage('completed'),
        'pending'=>$PMDR->getLanguage('pending'),
        'suspended'=>$PMDR->getLanguage('suspended'),
        'canceled'=>$PMDR->getLanguage('canceled'),
        'fraud'=>$PMDR->getLanguage('fraud')
    );
    $form_search->addField('status','select',array('label'=>'Status','fieldset'=>'orders_search','first_option'=>'','value'=>$_GET['status'],'options'=>$status_options));
    $form_search->addField('id','text',array('label'=>$PMDR->getLanguage('admin_orders_id'),'fieldset'=>'orders_search','value'=>$_GET['id']));
    $form_search->addField('order_id','text',array('label'=>$PMDR->getLanguage('admin_orders_number'),'fieldset'=>'orders_search','value'=>$_GET['order_id']));
    $form_search->addField('date','date',array('label'=>$PMDR->getLanguage('admin_orders_date'),'fieldset'=>'orders_search','value'=>$_GET['date']));
    if($PMDR->getConfig('user_select') == 'select_window' or true) {
        $form_search->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_orders_user_id'),'fieldset'=>'orders_search','value'=>$_GET['user_id'],'icon'=>'users_search','options'=>'select_user'));
    } else {
        $form_search->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_orders_user_id'),'fieldset'=>'orders_search','value'=>$_GET['user_id'],'options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
    }
    $form_search->addField('pricing_ids','products_select',array('label'=>$PMDR->getLanguage('admin_orders_product'),'fieldset'=>'orders_search','first_option'=>''));

    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_search',$form_search);

    $template_content->set('title',$PMDR->getLanguage('admin_orders'));
    $table_list = $PMDR->get('TableList');
    $order_checkbox_options =
    array(
        ''=>'',
        'active'=>$PMDR->getLanguage('active'),
        'completed'=>$PMDR->getLanguage('completed'),
        'pending'=>$PMDR->getLanguage('pending'),
        'suspended'=>$PMDR->getLanguage('suspended'),
        'canceled'=>$PMDR->getLanguage('canceled'),
        'fraud'=>$PMDR->getLanguage('fraud'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$order_checkbox_options,'onchange'=>'if(jQuery.inArray($(this).val(),[\''.implode('\',\'',array_keys($status_options)).'\']) == -1) { $(\'#notify_container\').hide(); } else { $(\'#notify_container\').show(); }'),'checkbox'=>array('name'=>'notify','value'=>1,'label'=>'Notify User')));
    $table_list->addColumn('id',$PMDR->getLanguage('admin_orders_id'),true);
    $table_list->addColumn('order_id',$PMDR->getLanguage('admin_orders_number'));
    $table_list->addColumn('date',$PMDR->getLanguage('admin_orders_date'),true);
    if(empty($_GET['user_id'])) {
        $table_list->addColumn('user_id',$PMDR->getLanguage('admin_orders_user'),true);
    }
    $table_list->addColumn('product',$PMDR->getLanguage('admin_orders_product'));
    if(!$PMDR->getConfig('disable_billing')) {
        $table_list->addColumn('next_due_date',$PMDR->getLanguage('admin_orders_next_due_date'),true);
    }
    $table_list->addColumn('status',$PMDR->getLanguage('admin_orders_status'),true);
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));

    if($form_search->wasSubmitted('submit')) {
        $data = $form_search->loadValues();
        if(!$form_search->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        }
    }

    $where[] = "o.type = 'listing_membership'";
    if(!empty($_GET['id'])) {
        $where[] = 'o.id = '.$PMDR->get('Cleaner')->clean_db($_GET['id']);
    }
    if(!empty($_GET['order_id'])) {
        $where[] = 'o.order_id = '.$PMDR->get('Cleaner')->clean_db($_GET['order_id']);
    }
    if(!empty($_GET['listing_id'])) {
        $where[] = 'o.type=\'listing_membership\' AND o.type_id = '.$PMDR->get('Cleaner')->clean_db($_GET['listing_id']);
    }
    if(!empty($_GET['status'])) {
        $where[] = "o.status = ".$PMDR->get('Cleaner')->clean_db($_GET['status']);
    }
    if(!empty($_GET['date'])) {
        $where[] = "DATE(DATE_ADD(o.date,INTERVAL ".$PMDR->get('Dates')->offset." SECOND)) = ".$PMDR->get('Cleaner')->clean_db($data['date']);
    }
    if(!empty($_GET['user_id'])) {
        $where[] = 'o.user_id = '.$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
    }
    if(!empty($_GET['pricing_ids'])) {
        $where[] = 'o.pricing_id IN('.$PMDR->get('Cleaner')->clean_db($_GET['pricing_ids']).')';
    }
    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    }

    // We order by twice here because for some unknown reason the sub-select does not stay ordered.
    $records = $db->GetAll("
    SELECT v.*, u.user_first_name, u.user_last_name, u.login, p.name, pp.label, l.title, l.friendly_url
    FROM
        (SELECT o.* FROM ".T_ORDERS." o $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'o.date DESC')." LIMIT ".$table_list->paging->limit1.",".$table_list->paging->limit2.") AS v
        LEFT JOIN ".T_USERS." u ON u.id = v.user_id
        INNER JOIN ".T_PRODUCTS_PRICING." pp ON pp.id = v.pricing_id
        INNER JOIN ".T_PRODUCTS." p ON p.id = pp.product_id
        LEFT JOIN ".T_LISTINGS." l ON l.id = v.type_id
        ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'v.date DESC'));

    $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_ORDERS." o $where"));
    foreach($records as $key=>$record) {
        if($record['label'] != '') {
            $records[$key]['term'] = $record['label'];
        } elseif($record['period_count'] == 0) {
            $records[$key]['term'] = $PMDR->getLanguage('admin_orders_non_expiring');
        } else {
            $records[$key]['term'] = $record['period_count'].' '.$PMDR->getLanguage('admin_orders_'.$record['period']);
        }
        if(!$records[$key]['next_due_date'] = $PMDR->get('Dates_Local')->formatDate($record['next_due_date'])) {
            $records[$key]['next_due_date'] = '-';
        } elseif(strtotime($record['next_due_date']) < time()) {
            $records[$key]['next_due_date'] = '<span class="text-danger">'.$records[$key]['next_due_date'].'</span>';
            if($db->GetOne("SELECT COUNT(*) FROM ".T_INVOICES." WHERE order_id=? AND status='unpaid'",array($record['id']))) {
                $records[$key]['next_due_date'] .= ' <a class="text-danger" href="admin_invoices.php?order_id='.$record['id'].'"><i title="Unpaid Invoices" class="glyphicon glyphicon-warning-sign"><i></a>';
            }
        }
        $records[$key]['product'] = $PMDR->get('Cleaner')->clean_output($record['name']);
        if(!is_null($record['title'])) {
            $records[$key]['product'] .= '<br /><a data-action="view_listing_summary" data-id="'.$record['type_id'].'" href="'.BASE_URL_ADMIN.'/admin_listings.php?action=edit&id='.$record['type_id'].'&user_id='.$record['user_id'].'">'.$PMDR->get('Cleaner')->clean_output($record['title']).'</a>';
        }
        $records[$key]['status'] = '<span class="label label-'.$record['status'].'">'.$PMDR->getLanguage($record['status']).'</span>';

        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDate($record['date']);
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id'],'user_id'=>$record['user_id']));

        if($record['type'] == 'listing_membership') {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('eye',array('href'=>$PMDR->get('Listings')->getURL($record['type_id'],$record['friendly_url']),'label'=>'View Public Listing','target'=>'_blank'));
        }

        if(!empty($_GET['user_id'])) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id'],'user_id'=>$_GET['user_id']));
        } else {
            $records[$key]['user_id'] = '<a data-action="user_preview" data-id="'.$record['user_id'].'" href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
            $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
            $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_orders_edit');
    if(!$order = $PMDR->get('Orders')->getRow($_GET['id'])) {
        redirect();
    }

    $template_content->set('listing_header',$PMDR->get('Listing',$order['type_id'])->getAdminHeader('order'));

    $order['upgrades'] = explode(',',$order['upgrades']);
    $user = $PMDR->get('Users')->getRow($order['user_id']);
    $template_content->set('title',$PMDR->getLanguage('admin_orders_edit'));
    $template_order = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_orders_view.tpl');
    $order['user_label'] = ($user['user_first_name'] == '') ? $order['user_id'] : trim($user['user_first_name'].' '.$user['user_last_name']);

    $template_order->set('invoices',$db->GetAll("SELECT * FROM ".T_INVOICES." WHERE order_id=? ORDER BY date",array($order['id'])));

    if($order['discount_code']) {
        $order['discount'] = $order['discount_code'].' - ';
        if($order['discount_code_discount_type'] == 'fixed') {
            $order['discount'] .= format_number_currency($order['discount_code_value']);
        } else {
            $order['discount'] .= $order['discount_code_value'].'%';
        }
        $order['discount'] .= ' ('.$order['discount_code_type'].')';
    }
    if(!is_null($order['pricing_id'])) {
        $order = array_merge($order,$db->GetRow("SELECT p.name AS product_name, p.id AS product_id FROM ".T_PRODUCTS." p, ".T_PRODUCTS_PRICING." pp  WHERE p.id=pp.product_id AND pp.id=?",array($order['pricing_id'])));
    }
    if($order['type'] == 'listing_membership') {
        $order = array_merge($order,(array) $db->GetRow("SELECT l.title AS product_title, l.status AS product_status FROM ".T_LISTINGS." l WHERE l.id=?",array($order['type_id'])));
        $order['product_type'] = $PMDR->getLanguage('admin_products_types_listing_membership');
    }

    $template_order->set('order',$order);

    $form = $PMDR->getNew('Form');

    $status_options = array(
        'active'=>$PMDR->getLanguage('active'),
        'completed'=>$PMDR->getLanguage('completed'),
        'pending'=>$PMDR->getLanguage('pending'),
        'suspended'=>$PMDR->getLanguage('suspended'),
        'canceled'=>$PMDR->getLanguage('canceled'),
        'fraud'=>$PMDR->getLanguage('fraud')
    );

    if(!$PMDR->getConfig('disable_billing')) {
        $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_orders_status'),'fieldset'=>'order_modify','options'=>$status_options));
        $form->addField('notify_user','checkbox',array('label'=>$PMDR->getLanguage('admin_orders_notify_user'),'fieldset'=>'order_modify','value'=>''));
    } else {
        $form->addField('status','custom',array('label'=>$PMDR->getLanguage('admin_orders_status'),'fieldset'=>'order_modify','html'=>$status_options[$order['status']]));
        $form->addField('product_status','custom',array('label'=>'Product Status','fieldset'=>'order_modify','html'=>$status_options[$order['product_status']]));
    }
    if($PMDR->getConfig('user_select') == 'select_window') {
        $form->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_orders_user'),'fieldset'=>'order_modify','icon'=>'users_search','options'=>'select_user'));
    } else {
        $form->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_orders_user'),'fieldset'=>'order_modify','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
    }

    if(!$PMDR->getConfig('disable_billing')) {
        $form->addField('pricing_id','tree_select_expanding_radio',array('label'=>$PMDR->getLanguage('admin_orders_product'),'fieldset'=>'order_modify','value'=>$order['pricing_id'],'options'=>array('type'=>'products_tree','product_type'=>$order['type'],'hidden'=>true)));
        $form->addField('product_status','select',array('label'=>'Product Status','fieldset'=>'order_modify','options'=>array('active'=>'Active','pending'=>'Pending','suspended'=>'Suspended')));
        $form->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_orders_payment_method'),'fieldset'=>'order_modify','first_option'=>'','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS)));
        $form->addField('amount_recurring','text',array('label'=>$PMDR->getLanguage('admin_orders_amount_recurring'),'fieldset'=>'order_modify'));
        $form->addField('period','select',array('label'=>'Period','fieldset'=>'order_modify','options'=>array('days'=>$PMDR->getLanguage('days'),'months'=>$PMDR->getLanguage('months'),'years'=>$PMDR->getLanguage('years'))));
        $form->addField('period_count','text',array('label'=>'Period Length','fieldset'=>'order_modify'));
        $form->addField('next_due_date','date',array('label'=>'Next Due Date','fieldset'=>'order_modify'));
        $form->addField('next_invoice_date','date',array('label'=>'Next Invoice Date','fieldset'=>'order_modify'));;
        if(!$PMDR->get('Dates')->isZero($order['next_invoice_date'])) {
            $form->addFieldNote('next_invoice_date',$PMDR->getLanguage('admin_orders_next_invoice_creation').': '.$PMDR->get('Dates_Local')->formatDate($PMDR->get('Dates')->dateSubtract($order['next_invoice_date'],$PMDR->getConfig('invoice_generation_days'))));
        }
        if($discount_codes = $PMDR->get('Discount_Codes')->getFormattedCodes()) {
            $form->addField('discount_code','select',array('label'=>'Discount','first_option'=>'','fieldset'=>'order','value'=>$order['discount_code'],'options'=>$discount_codes));
            $form->addFieldNote('discount_code','Changing the discount code will not automatically update the pricing.');
        }
        $form->addField('suspend_overdue_days','text',array('label'=>$PMDR->getLanguage('admin_orders_suspend_overdue_days'),'fieldset'=>'order_modify'));
        $form->addField('taxed','checkbox',array('label'=>$PMDR->getLanguage('admin_orders_taxed'),'fieldset'=>'order_modify'));
        $form->addField('subscription_id','text',array('label'=>$PMDR->getLanguage('admin_orders_subscription_id'),'fieldset'=>'order_modify'));
        $form->addField('upgrades','tree_select_expanding_checkbox',array('label'=>$PMDR->getLanguage('admin_orders_upgrades'),'fieldset'=>'order_modify','value'=>'','options'=>array('type'=>'products_tree','product_type'=>'listing_membership','hidden'=>true)));
        $form->addField('renewable','checkbox',array('label'=>$PMDR->getLanguage('admin_orders_renewable'),'fieldset'=>'order_modify'));
    }
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $form->loadValues($order);

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if(!empty($data['upgrades']) AND is_array($data['upgrades'])) {
                $data['upgrades'] = implode(',',$data['upgrades']);
            }
            $PMDR->get('Orders')->update($data, $_GET['id']);

            if(isset($data['pricing_id']) AND $order['pricing_id'] != $data['pricing_id']) {
                $PMDR->get('Orders')->changePricingID($order['id'],$data['pricing_id']);
            }
            if($data['notify_user']) {
                $PMDR->get('Email_Templates')->send('order_status_change',array('to'=>$order['user_id'],'order_id'=>$order['id']));
            }
            if(isset($data['user_id']) AND $data['user_id'] != $order['user_id']) {
                $PMDR->get('Orders')->changeUser($order['id'],$data['user_id']);
            }
            if(isset($data['status']) AND $data['status'] != $order['status']) {
                $PMDR->get('Orders')->changeStatus($order['id'],$data['status']);
            }
            if(isset($data['product_status']) AND $data['product_status'] != $order['product_status']) {
                if(!$PMDR->get('Orders')->changeProductStatus($order['id'],$data['product_status'])) {
                    $PMDR->addMessage('error',$PMDR->getLanguage('admin_orders_product_status_error'));
                }
            }
            $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($order['id'],$PMDR->getLanguage('admin_orders'))),'update');
            if(!empty($_GET['user_id'])) {
                redirect(null,array('user_id'=>$_GET['user_id']));
            } else {
                redirect();
            }
        }
    }
    $template_content->set('content',$form->toHTML());
}

if(!isset($_GET['user_id']) OR empty($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_orders_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>