<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_invoices','admin_users','admin_gateways','admin_transactions','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_invoices_view');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Authentication')->checkPermission('admin_invoices_delete');
    $PMDR->get('Invoices')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_invoices'))),'delete');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

if(isset($_POST['table_list_submit'])) {
    if(in_array($_POST['action'],array('paid','unpaid','canceled'))) {
        $PMDR->get('Authentication')->checkPermission('admin_invoices_edit');
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Invoices')->changeStatus($id,$_POST['action']);
        }
    } elseif($_POST['action'] == 'reminder') {
        foreach($_POST['table_list_checkboxes'] AS $id) {
            if($PMDR->get('Email_Templates')->queue('invoice_reminder',array('invoice_id'=>$id,'attachment'=>array('data'=>$PMDR->get('Invoices')->getPDF($id),'name'=>'Invoice_'.$id.'.pdf','type'=>'application/pdf')))) {
                $db->Execute("UPDATE ".T_INVOICES." SET reminder_sent=1 WHERE id=?",array($id));
            }
        }
    } elseif($_POST['action'] == 'delete') {
        $PMDR->get('Authentication')->checkPermission('admin_invoices_delete');
        foreach($_POST['table_list_checkboxes'] AS $id) {
            $PMDR->get('Invoices')->delete($id);
        }
    }
    $PMDR->addMessage('success','Invoices updated');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

if($_GET['action'] == 'delete_transaction') {
    $PMDR->get('Authentication')->checkPermission('admin_invoices_delete');
    $PMDR->get('Transactions')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_transactions'))),'delete');
    if(!empty($_GET['user_id'])) {
        redirect(array('action'=>'edit','id'=>$_GET['invoice_id'],'user_id'=>$_GET['user_id']));
    } else {
        redirect(array('action'=>'edit','id'=>$_GET['invoice_id']));
    }
}

if(in_array($_GET['action'],array('paid','unpaid','canceled'))) {
    $PMDR->get('Invoices')->changeStatus($_GET['id'],$_GET['action']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_invoices'))),'update');
    redirect(rebuild_url(array('action'=>'edit'),array('action')));
}

if($_GET['action'] == 'print') {
    $template_content = $PMDR->get('Invoices')->getPrintTemplate($_GET['id'],PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_invoices_print.tpl');
    echo $template_content->render();
    exit();
}

if($_GET['action'] == 'pdf') {
    $PMDR->get('Invoices')->getPDF($_GET['id'],true);
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'/admin_invoices.tpl');

if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('invoices'));
}

if(!isset($_GET['action']) OR $_GET['action'] == 'search') {
    $template_content->set('title',$PMDR->getLanguage('admin_invoices'));

    $form_search = $PMDR->get('Form');
    $form_search->method = 'GET';
    $form_search->addFieldSet('invoice_search',array('style'=>'border: 0px'));
    $form_search->addField('id','text',array('label'=>$PMDR->getLanguage('admin_invoices_id'),'fieldset'=>'invoice_search','help'=>false));
    $form_search->addField('date','date',array('label'=>$PMDR->getLanguage('admin_invoices_date'),'fieldset'=>'invoice_search','help'=>false));
    $form_search->addField('date_due','date',array('label'=>$PMDR->getLanguage('admin_invoices_date_due'),'fieldset'=>'invoice_search','help'=>false));
    $form_search->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_method'),'fieldset'=>'invoice_search','first_option'=>'','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1"),'help'=>false));
    $form_search->addField('total','text',array('label'=>$PMDR->getLanguage('admin_invoices_total'),'fieldset'=>'invoice_search','help'=>false));
    $form_search->addField('status','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_status'),'fieldset'=>'invoice_search','first_option'=>'','options'=>array('paid'=>$PMDR->getLanguage('admin_invoices_paid'),'unpaid'=>$PMDR->getLanguage('admin_invoices_unpaid'),'canceled'=>$PMDR->getLanguage('admin_invoices_canceled')),'help'=>false));
    $form_search->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_search',$form_search);

    $table_list = $PMDR->get('TableList');
    $table_list->addColumn('id',$PMDR->getLanguage('admin_invoices_id'),true);
    $table_list->addColumn('order_id',$PMDR->getLanguage('admin_invoices_order_id'),true);
    if(empty($_GET['user_id'])) {
        $table_list->addColumn('user',$PMDR->getLanguage('admin_invoices_user'));
    }
    $table_list->addColumn('date',$PMDR->getLanguage('admin_invoices_date'),true);
    $table_list->addColumn('date_due',$PMDR->getLanguage('admin_invoices_date_due'),true);
    $table_list->addColumn('total',$PMDR->getLanguage('admin_invoices_total'),true);
    $table_list->addColumn('gateway_id',$PMDR->getLanguage('admin_invoices_payment_method'),true);
    $table_list->addColumn('status',$PMDR->getLanguage('admin_invoices_payment_status'),true);
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'),false,true);
    $checkbox_options =
    array(
        ''=>'',
        'paid'=>$PMDR->getLanguage('admin_invoices_paid'),
        'unpaid'=>$PMDR->getLanguage('admin_invoices_unpaid'),
        'canceled'=>$PMDR->getLanguage('admin_invoices_canceled'),
        'reminder'=>$PMDR->getLanguage('admin_invoices_send_reminder'),
        'delete'=>$PMDR->getLanguage('admin_delete')
    );
    $table_list->addCheckbox(array('select'=>array('name'=>'action','options'=>$checkbox_options)));

    $where[] = 'i.user_id=u.id';
    if(!empty($_GET['status'])) {
        if($_GET['status'] == 'overdue') {
            $where[] = 'i.date_due < CURDATE()';
            $where[] = "status='unpaid'";
        } elseif($_GET['status'] == 'due_today') {
            $where[] = 'DATE(i.date_due) = CURDATE()';
        } else {
            $where[] = "i.status = ".$PMDR->get('Cleaner')->clean_db($_GET['status']);
        }
    }
    if(!empty($_GET['id'])) {
        $where[] = "i.id = ".$PMDR->get('Cleaner')->clean_db($_GET['id']);
    }
    if(!empty($_GET['user_id'])) {
        $where[] = "i.user_id = ".$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
    }
    if(!empty($_GET['date'])) {
        $where[] = "i.date = ".$PMDR->get('Cleaner')->clean_db($_GET['date']);
    }
    if(!empty($_GET['date_due'])) {
        $where[] = "i.date_due = ".$PMDR->get('Cleaner')->clean_db($_GET['date_due']);
    }
    if(!empty($_GET['total'])) {
        $where[] = "i.total = ".$PMDR->get('Cleaner')->clean_db($_GET['total']);
    }
    if(!empty($_GET['gateway_id'])) {
        $where[] = "i.gateway_id = ".$PMDR->get('Cleaner')->clean_db($_GET['gateway_id']);
    }
    if(!empty($_GET['order_id'])) {
        $where[] = "i.order_id = ".$PMDR->get('Cleaner')->clean_db($_GET['order_id']);
    }

    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    }

    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS i.*, u.user_first_name, u.user_last_name, u.login FROM ".T_INVOICES." i, ".T_USERS." u $where ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')." LIMIT ?,?",array($table_list->paging->limit1,$table_list->paging->limit2));
    $table_list->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        if(is_null($record['order_id'])) {
            $records[$key]['order_id'] = '-';
        } else {
            $records[$key]['order_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_orders.php?action=edit&id='.$record['order_id'].'&user_id='.$record['user_id'].'">'.$record['order_id'].'</a>';
        }
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDate($record['date']);
        $records[$key]['date_due'] = $PMDR->get('Dates_Local')->formatDate($record['date_due']);
        $records[$key]['status'] = '<span class="label label-'.$record['status'].'">'.$PMDR->getLanguage('admin_invoices_'.$record['status']).'</span>';
        $records[$key]['total'] = format_number_currency($record['total']);
        if(empty($_GET['user_id'])) {
            $records[$key]['user'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
            $records[$key]['user'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
            $records[$key]['user'] .= '</a> (ID: '.$record['user_id'].')';
        }
        $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id'],'user_id'=>$record['user_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('money_add',array('label'=>$PMDR->getLanguage('admin_invoices_add_payment'),'href'=>'admin_transactions.php?action=add&invoice_id='.$record['id'].'&user_id='.$record['user_id']));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('print',array('label'=>$PMDR->getLanguage('admin_invoices_print'),'href'=>'JavaScript:newWindow(\''.BASE_URL_ADMIN.'/admin_invoices.php?action=print&id='.$record['id'].'\',\'popup\',650,600,\'\')'));
        $records[$key]['manage'] .= $PMDR->get('HTML')->icon('pdf',array('label'=>$PMDR->getLanguage('admin_invoices_download_pdf'),'href'=>'admin_invoices.php?action=pdf&id='.$record['id']));
        if(!empty($_GET['user_id'])) {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id'],'user_id'=>$_GET['user_id']));
        } else {
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    $PMDR->get('Authentication')->checkPermission('admin_invoices_edit');
    if($_GET['action'] == 'edit') {
        $invoice = $PMDR->get('Invoices')->get($_GET['id']);

        $template_order = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_invoices_view.tpl');

        $template_content->set('title',$PMDR->getLanguage('admin_invoices_invoice').' #'.$invoice['id']);
        $template_order->set('id',$invoice['id']);
        $template_order->set('user_id',$invoice['user_id']);
        $template_order->set('date',$PMDR->get('Dates_Local')->formatDate($invoice['date']));
        $template_order->set('date_due',$PMDR->get('Dates_Local')->formatDate($invoice['date_due']));
        $template_order->set('date_paid',$PMDR->get('Dates_Local')->formatDate($invoice['date_paid']));
        $template_order->set('balance',$invoice['balance']);
        $template_order->set('balance_formatted',format_number_currency($invoice['balance']));
        $template_order->set('total',format_number_currency($invoice['total']));
        $template_order->set('status',$invoice['status']);
        $template_order->set('notes',$invoice['notes']);
        $template_order->set('gateway_id',$invoice['gateway_id']);
        $template_order->set('description',$invoice['description']);
        $template_order->set('subtotal',format_number_currency($invoice['subtotal']));
        if($invoice['tax'] > 0) {
            $template_order->set('tax',format_number_currency($invoice['tax']));
            $template_order->set('tax_rate',(float) $invoice['tax_rate']);
        }
        if($invoice['tax2'] > 0) {
            $template_order->set('tax2',format_number_currency($invoice['tax2']));
            $template_order->set('tax_rate2',(float) $invoice['tax_rate2']);
        }
        $email_form = $PMDR->getNew('Form');
        $email_options = $db->GetAssoc("SELECT id, id FROM ".T_EMAIL_TEMPLATES." WHERE type='invoice' AND id NOT LIKE 'admin_%'");
        foreach($email_options AS $id) {
            $email_options[$id] = $PMDR->getLanguage('email_templates_'.$id.'_name');
        }
        $email_form->addField('email','select',array('label'=>'','first_option'=>'Select Email..','options'=>$email_options));
        $email_form->addField('email_submit','submit',array('label'=>'Send Email','fieldset'=>'submit'));
        $template_order->set('email_form',$email_form);

        $form = $PMDR->getNew('Form');
        $form->addFieldSet('invoice_information');
        if($PMDR->getConfig('user_select') == 'select_window') {
            $form->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_invoices_user'),'fieldset'=>'invoice_information','icon'=>'users_search','type'=>'select_user'));
        } else {
            $form->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_invoices_user'),'fieldset'=>'invoice_information','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
        }
        $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_status'),'fieldset'=>'invoice_information','value'=>'unpaid','options'=>array('paid'=>$PMDR->getLanguage('admin_invoices_paid'),'unpaid'=>$PMDR->getLanguage('admin_invoices_unpaid'),'canceled'=>$PMDR->getLanguage('admin_invoices_canceled'))));
        $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_invoices_description'),'fieldset'=>'invoice_information'));
        $form->addField('date','date',array('label'=>$PMDR->getLanguage('admin_invoices_date'),'fieldset'=>'invoice_information','value'=>$PMDR->get('Dates')->dateNow()));
        $form->addField('date_due','date',array('label'=>$PMDR->getLanguage('admin_invoices_date_due'),'fieldset'=>'invoice_information','value'=>$PMDR->get('Dates')->dateNow()));
        $form->addField('tax_rate','text',array('label'=>$PMDR->getLanguage('admin_invoices_tax_rate'),'fieldset'=>'invoice_information','value'=>'0.00'));
        $form->addField('tax_rate2','text',array('label'=>$PMDR->getLanguage('admin_invoices_tax_rate2'),'fieldset'=>'invoice_information','value'=>'0.00'));
        $form->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_method'),'fieldset'=>'invoice_information','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1")));
        $form->addField('subtotal','text',array('label'=>$PMDR->getLanguage('admin_invoices_subtotal'),'fieldset'=>'invoice_information'));
        $form->addField('notes','textarea',array('label'=>$PMDR->getLanguage('admin_invoices_notes'),'fieldset'=>'invoice_information'));
        $form->addValidator('date',new Validate_Date());
        $form->addValidator('date_due',new Validate_Date());
        $form->addValidator('tax_rate',new Validate_NonEmpty());
        $form->addValidator('tax_rate2',new Validate_NonEmpty());
        $form->addValidator('subtotal',new Validate_NonEmpty());
        $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

        $form_payment = $PMDR->getNew('Form');
        $form_payment->addFieldSet('payment');
        $form_payment->addField('invoice_date','date',array('label'=>$PMDR->getLanguage('admin_transactions_date'),'fieldset'=>'payment','help'=>$PMDR->getLanguage('admin_transactions_invoice_date_help')));
        $form_payment->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_transactions_gateway'),'fieldset'=>'payment','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1"),'help'=>$PMDR->getLanguage('admin_transactions_gateway_id_help')));
        $form_payment->addField('transaction_id','text',array('label'=>$PMDR->getLanguage('admin_transactions_id'),'fieldset'=>'payment','help'=>$PMDR->getLanguage('admin_transactions_transaction_id_help')));
        $form_payment->addField('amount','text',array('label'=>$PMDR->getLanguage('admin_transactions_amount'),'fieldset'=>'payment','help'=>$PMDR->getLanguage('admin_transactions_amount_help')));
        $form_payment->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_transactions_description'),'fieldset'=>'payment','help'=>$PMDR->getLanguage('admin_transactions_description_help')));
        $form_payment->addField('notify','checkbox',array('label'=>$PMDR->getLAnguage('admin_transactions_email'),'fieldset'=>'payment','value'=>'1','help'=>$PMDR->getLanguage('admin_transactions_notify_help')));
        $form_payment->addField('comments','textarea',array('label'=>$PMDR->getLanguage('admin_transactions_comments'),'fieldset'=>'transaction'));
        $form_payment->addField('submit_payment','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
        $form_payment->addValidator('invoice_date',new Validate_Date());
        $form_payment->addValidator('amount',new Validate_NonEmpty());
        $form_payment->addValidator('transaction_id',new Validate_NonEmpty());

        $table_list = $PMDR->get('TableList');
        $table_list->addColumn('date',$PMDR->getLanguage('admin_transactions_date'));
        $table_list->addColumn('gateway_id',$PMDR->getLanguage('admin_transactions_gateway'));
        $table_list->addColumn('transaction_id',$PMDR->getLanguage('admin_transactions_id'));
        $table_list->addColumn('amount',$PMDR->getLanguage('admin_transactions_amount'));
        $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
        $table_list->setTotalResults($db->GetOne("SELECT COUNT(*) FROM ".T_TRANSACTIONS." WHERE invoice_id=?",array($_GET['id'])));
        $records = $PMDR->get('Transactions')->getByInvoiceID($_GET['id'],$table_list->page_data['limit1'],$table_list->page_data['limit2']);
        foreach($records as &$record) {
            $record['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
            $record['manage'] = $PMDR->get('HTML')->icon('edit',array('href'=>'admin_transactions.php?action=edit&id='.$record['id'].'&user_id='.$record['user_id']));
            $record['manage'] .= $PMDR->get('HTML')->icon('delete',array('href'=>URL_NOQUERY.'?action=delete_transaction&id='.$record['id'].'&invoice_id='.$_GET['id'].'&user_id='.$record['user_id']));
        }
        $table_list->addRecords($records);
        $template_order->set('transactions',$table_list->render());

        $form->loadValues($invoice);

        if($form_payment->wasSubmitted('submit_payment')) {
            $data = $form_payment->loadValues();
            if(!$form_payment->validate()) {
                $PMDR->addMessage('error',$form_payment->parseErrorsForTemplate());
            } else {
                $transaction_id = $PMDR->get('Invoices')->insertTransaction($_GET['id'],$data['transaction_id'],$data['amount'],$data['invoice_date'],$data['description'],$data['comments'],$data['gateway_id']);
                if($data['notify']) {
                    $PMDR->get('Email_Templates')->send('invoice_payment',array('to'=>$data['user_id'],'invoice_id'=>$_GET['id']));
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($data['amount'],$PMDR->getLanguage('admin_transactions'))),'insert');
                redirect(URL);
            }
        }

        if($form->wasSubmitted('submit')) {
            $data = $form->loadValues();
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                $data = $form->getValues(array('user_id','status','description','date','date_due','tax_rate','tax_rate2','gateway_id','subtotal','total','notes'));

                if($data['tax_rate'] != 0.00) {
                    if($PMDR->getConfig('tax_type') == 'exclusive') {
                        $data['tax'] = round($data['subtotal']*($data['tax_rate']/100),2);
                        if($data['tax_rate2'] != 0.00) {
                            if($PMDR->getConfig('compound_tax')) {
                                $data['tax2'] = round(($data['subtotal']+$data['tax'])*($data['tax_rate2']/100),2);
                            } else {
                                $data['tax2'] = round($data['subtotal']*($data['tax_rate2']/100),2);
                            }
                        }
                    } else {
                        $data['tax'] = round($data['subtotal'] - ($data['subtotal']*100/($data['tax_rate']+100)),2);
                        if($data['tax_rate2'] != 0.00) {
                            $data['tax2'] = round($data['subtotal'] - ($data['subtotal']*100/($data['tax_rate2']+100)),2);
                        }
                        $data['subtotal'] = $data['subtotal'] - $data['tax'] - $data['tax2'];
                    }
                }

                $data['total'] = round($data['tax'] + $data['tax2'] + $data['subtotal'],2);

                $PMDR->get('Invoices')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($_GET['id'],$PMDR->getLanguage('admin_invoices'))),'update');
                redirect(URL);
            }
        }

        if($email_form->wasSubmitted('email_submit')) {
            $data = $email_form->loadValues();
            if(!$email_form->validate()) {
                $PMDR->addMessage('error',$email_form->parseErrorsForTemplate());
            } else {
                if($data['email'] == 'invoice_created') {
                    $PMDR->get('Invoices')->sendInvoiceCreatedEmail($invoice['id']);
                } else {
                    $PMDR->get('Email_Templates')->send($data['email'],array('invoice_id'=>$invoice['id']));
                }
                $PMDR->addMessage('success','Email sent');
                redirect(URL);
            }
        }

        $template_order->set('payment_form',$form_payment->toHTML());
        $template_order->set('form',$form->toHTML());
        $template_content->set('content',$template_order);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_invoices_add'));
        $form = $PMDR->get('Form');
        $form->addFieldSet('invoice_information',array('legend'=>$PMDR->getLanguage('admin_invoices_invoice')));
        if(!isset($_GET['order_id'])) {
            if($PMDR->getConfig('user_select') == 'select_window') {
                $form->addField('user_id','select_window',array('label'=>$PMDR->getLanguage('admin_invoices_user'),'fieldset'=>'invoice_information','icon'=>'users_search','options'=>'select_user'));
            } else {
                $form->addField('user_id','select',array('label'=>$PMDR->getLanguage('admin_invoices_user'),'fieldset'=>'invoice_information','options'=>$db->GetAssoc("SELECT id, CONCAT(login, ' (',user_email,')') FROM ".T_USERS." ORDER BY login")));
            }
        }
        if(!empty($_GET['user_id'])) {
            $form->setFieldAttribute('user_id','value',$_GET['user_id']);
        }
        $form->addField('status','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_status'),'fieldset'=>'invoice_information','value'=>'unpaid','options'=>array('paid'=>'Paid','unpaid'=>'Unpaid','canceled'=>'Canceled')));
        $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_invoices_description'),'fieldset'=>'invoice_information'));
        $form->addField('date','date',array('label'=>$PMDR->getLanguage('admin_invoices_date'),'fieldset'=>'invoice_information','value'=>$PMDR->get('Dates')->dateNow()));
        $form->addField('date_due','date',array('label'=>$PMDR->getLanguage('admin_invoices_date_due'),'fieldset'=>'invoice_information','value'=>$PMDR->get('Dates')->dateNow()));
        $form->addField('tax_rate','text',array('label'=>$PMDR->getLanguage('admin_invoices_tax_rate'),'fieldset'=>'invoice_information','value'=>'0.00'));
        $form->addField('tax_rate2','text',array('label'=>$PMDR->getLanguage('admin_invoices_tax_rate2'),'fieldset'=>'invoice_information','value'=>'0.00'));
        $form->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_invoices_payment_method'),'fieldset'=>'invoice_information','first_option'=>'','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1")));
        $form->addField('subtotal','text',array('label'=>$PMDR->getLanguage('admin_invoices_subtotal'),'fieldset'=>'invoice_information'));
        $form->addField('notify','checkbox',array('label'=>$PMDR->getLanguage('admin_invoices_send_email'),'fieldset'=>'invoice_information'));
        $form->addField('notes','textarea',array('label'=>$PMDR->getLanguage('admin_invoices_notes'),'fieldset'=>'invoice_information'));
        $form->addValidator('date',new Validate_NonEmpty());
        $form->addValidator('date_due',new Validate_NonEmpty());
        $form->addValidator('tax_rate',new Validate_NonEmpty());
        $form->addValidator('tax_rate2',new Validate_NonEmpty());
        $form->addValidator('subtotal',new Validate_NonEmpty());
        $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
        if($form->wasSubmitted('submit')) {
            $data = $form->loadValues();
            if(!$form->validate()) {
                $PMDR->addMessage('error',$form->parseErrorsForTemplate());
            } else {
                if($data['tax_rate'] != 0.00) {
                    if($PMDR->getConfig('tax_type') == 'exclusive') {
                        $data['tax'] = round($data['subtotal']*($data['tax_rate']/100),2);
                        if($data['tax_rate2'] != 0.00) {
                            if($PMDR->getConfig('compound_tax')) {
                                $data['tax2'] = round(($data['subtotal']+$data['tax'])*($data['tax_rate2']/100),2);
                            } else {
                                $data['tax2'] = round($data['subtotal']*($data['tax_rate2']/100),2);
                            }
                        }
                    } else {
                        $data['tax'] = round($data['subtotal'] - ($data['subtotal']*100/($data['tax_rate']+100)),2);
                        if($data['tax_rate2'] != 0.00) {
                            $data['tax2'] = round($data['subtotal'] - ($data['subtotal']*100/($data['tax_rate2']+100)),2);
                        }
                        $data['subtotal'] = $data['subtotal'] - $data['tax'] - $data['tax2'];
                    }
                }

                $data['total'] = round($data['tax'] + $data['tax2'] + $data['subtotal'],2);

                if(isset($_GET['order_id'])) {
                    $data['order_id'] = $_GET['order_id'];
                }
                if(!empty($_GET['user_id'])) {
                    $data['user_id'] = $_GET['user_id'];
                }
                $invoice_id = $PMDR->get('Invoices')->insert($data);
                if($data['notify']) {
                    $PMDR->get('Invoices')->sendInvoiceCreatedEmail($invoice_id);
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($invoice_id,$PMDR->getLanguage('admin_invoices'))),'insert');
                if(isset($_GET['order_id'])) {
                    redirect(BASE_URL_ADMIN.'/admin_orders.php?action=edit&id='.$_GET['order_id']);
                } elseif(!empty($_GET['user_id'])) {
                    redirect(null,array('user_id'=>$_GET['user_id']));
                } else {
                    redirect();
                }
            }
        }
        $template_content->set('content',$form->toHTML());
    }
}

if(!isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_invoices_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>