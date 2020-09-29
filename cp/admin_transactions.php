<?php
define('PMD_SECTION', 'admin');

include('../defaults.php');

$PMDR->loadLanguage(array('admin_invoices','admin_users','admin_transactions','admin_gateways','email_templates'));

$PMDR->get('Authentication')->authenticate();

$PMDR->get('Authentication')->checkPermission('admin_transactions');

if($PMDR->getConfig('disable_billing')) {
    $PMDR->addMessage('notice',$PMDR->getLanguage('admin_general_disable_billing'));
}

if(value($_GET,'action') == 'download') {
    $PMDR->get('ServeFile')->serve(TEMP_UPLOAD_PATH.'transactions_export.csv');
}

$template_content = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'admin_transactions.tpl');

if(!empty($_GET['user_id'])) {
    $template_content->set('users_summary_header',$PMDR->get('User',$_GET['user_id'])->getAdminSummaryHeader('transactions'));
}

if($_GET['action'] == 'delete') {
    $PMDR->get('Transactions')->delete($_GET['id']);
    $PMDR->addMessage('success',$PMDR->getLanguage('messages_deleted',array($_GET['id'],$PMDR->getLanguage('admin_transactions'))),'delete');
    if(!empty($_GET['user_id'])) {
        redirect(null,array('user_id'=>$_GET['user_id']));
    } else {
        redirect();
    }
}

if(!isset($_GET['action']) OR $_GET['action'] == 'export') {
    $form_export = $PMDR->get('Form');
    $form_export->method = 'get';
    $form_export->addFieldSet('transaction',array('legend'=>'Transaction Information'));
    $years = $db->GetAssoc("SELECT YEAR(date), YEAR(date) AS value FROM ".T_TRANSACTIONS." GROUP BY date ORDER BY date DESC");
    $form_export->addField('year','select',array('label'=>'Year','fieldset'=>'transaction','first_option'=>'','options'=>$years));
    $form_export->addField('export_start','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));
    $template_content->set('form_export',$form_export);

    $template_content->set('title',$PMDR->getLanguage('admin_transactions'));
    $table_list = $PMDR->get('TableList');
    if(empty($_GET['user_id'])) {
        $table_list->addColumn('user_id',$PMDR->getLanguage('admin_transactions_userid'),true);
    }
    $table_list->addColumn('date',$PMDR->getLanguage('admin_transactions_date'),true);
    $table_list->addColumn('gateway_id',$PMDR->getLanguage('admin_transactions_gateway'),true);
    $table_list->addColumn('amount',$PMDR->getLanguage('admin_transactions_amount'),true);
    $table_list->addColumn('description',$PMDR->getLanguage('admin_transactions_description'));
    $table_list->addColumn('manage',$PMDR->getLanguage('admin_manage'));
    $where = array();
    if(!empty($_GET['user_id'])) {
        $where[] = "t.user_id = ".$PMDR->get('Cleaner')->clean_db($_GET['user_id']);
    }
    if(!empty($_GET['date'])) {
        $where[] = "t.date = ".$PMDR->get('Cleaner')->clean_db($_GET['date']);
    }
    if(!empty($_GET['amount'])) {
        $where[] = "t.amount = ".$PMDR->get('Cleaner')->clean_db($_GET['amount']);
    }
    if(!empty($_GET['gateway_id'])) {
        $where[] = "t.gateway_id = ".$PMDR->get('Cleaner')->clean_db($_GET['gateway_id']);
    }
    if(!empty($where)) {
        $where = 'WHERE '.implode(' AND ',$where);
    } else {
        $where = '';
    }
    $records = $db->GetAll("SELECT SQL_CALC_FOUND_ROWS t.*, u.user_first_name, u.user_last_name, u.login FROM ".T_TRANSACTIONS." t INNER JOIN ".T_USERS." u ON t.user_id=u.id $where
    ".$db->OrderBy($_GET['sort'],$_GET['sort_direction'],'date DESC')."
    LIMIT ?,?",array($table_list->paging->limit1,$table_list->paging->limit2));
    $table_list->setTotalResults($db->FoundRows());
    foreach($records as $key=>$record) {
        if(empty($_GET['user_id'])) {
            $records[$key]['user_id'] = '<a href="'.BASE_URL_ADMIN.'/admin_users_summary.php?id='.$record['user_id'].'">';
            $records[$key]['user_id'] .= trim($record['user_first_name'].' '.$record['user_last_name']) != '' ? trim($record['user_first_name'].' '.$record['user_last_name']) : $record['login'];
            $records[$key]['user_id'] .= '</a> (ID: '.$record['user_id'].')';
        }
        $description = array();
        if(!empty($record['description'])) {
            $description[] = $PMDR->get('Cleaner')->clean_output($record['description']);
        }
        if($record['transaction_id'] != '') {
            $description[] = $PMDR->getLanguage('admin_transactions_id').': '.$record['transaction_id'];
        }
        if(!is_null($record['invoice_id'])) {
            $description[] = $PMDR->getLanguage('admin_transactions_invoice_id').': <a href="'.BASE_URL_ADMIN.'/admin_invoices.php?id='.$record['invoice_id'].'&action=edit&user_id='.$record['user_id'].'">'.$record['invoice_id'].'</a>';
        }
        $records[$key]['description'] = implode('<br />',$description);
        $records[$key]['amount'] = format_number_currency($record['amount']);
        $records[$key]['date'] = $PMDR->get('Dates_Local')->formatDateTime($record['date']);
        if(!empty($_GET['user_id'])) {
            $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id'],'user_id'=>$_GET['user_id']));
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id'],'user_id'=>$_GET['user_id']));
        } else {
            $records[$key]['manage'] = $PMDR->get('HTML')->icon('edit',array('id'=>$record['id']));
            $records[$key]['manage'] .= $PMDR->get('HTML')->icon('delete',array('id'=>$record['id']));
        }
    }
    $table_list->addRecords($records);
    $template_content->set('content',$table_list->render());
} else {
    if(isset($_GET['invoice_id'])) {
        $invoice = $db->GetRow("SELECT * FROM ".T_INVOICES." WHERE id=?",array($_GET['invoice_id']));
    } elseif($_GET['action'] == 'edit') {
        $transaction = $PMDR->get('Transactions')->getRow($_GET['id']);
        if(!empty($transaction['invoice_id'])) {
            $invoice = $db->GetRow("SELECT * FROM ".T_INVOICES." WHERE id=?",array($_GET['invoice_id']));
        }
    }

    $form = $PMDR->get('Form');
    $form->addFieldSet('transaction',array('legend'=>'Transaction Information'));
    $form->addField('invoice_id','custom',array('label'=>$PMDR->getLanguage('admin_transactions_invoice_id'),'fieldset'=>'transaction','value'=>$_GET['invoice_id']));
    $form->addField('user_id','custom',array('label'=>$PMDR->getLanguage('admin_transactions_userid'),'fieldset'=>'transaction','value'=>$invoice['user_id']));
    $form->addField('date','date',array('label'=>$PMDR->getLanguage('admin_transactions_date'),'fieldset'=>'transaction','value'=>$PMDR->get('Dates')->dateNow()));
    $form->addField('gateway_id','select',array('label'=>$PMDR->getLanguage('admin_transactions_gateway'),'fieldset'=>'transaction','options'=>$db->GetAssoc("SELECT id, id FROM ".T_GATEWAYS." WHERE enabled=1")));
    $form->addField('transaction_id','text',array('label'=>$PMDR->getLanguage('admin_transactions_id'),'fieldset'=>'transaction'));
    $form->addField('amount','text',array('label'=>$PMDR->getLanguage('admin_transactions_amount'),'fieldset'=>'transaction'));
    $form->addField('description','textarea',array('label'=>$PMDR->getLanguage('admin_transactions_description'),'fieldset'=>'transaction'));
    $form->addField('notify','checkbox',array('label'=>$PMDR->getLAnguage('admin_transactions_email'),'fieldset'=>'transaction','value'=>'1'));
    $form->addField('comments','textarea',array('label'=>$PMDR->getLanguage('admin_transactions_comments'),'fieldset'=>'transaction'));
    $form->addField('submit','submit',array('label'=>$PMDR->getLanguage('admin_submit'),'fieldset'=>'submit'));

    $form->addValidator('amount',new Validate_NonEmpty());
    $form->addValidator('transaction_id',new Validate_NonEmpty());
    $form->addValidator('date',new Validate_NonEmpty());
    $form->addValidator('date',new Validate_Date_Range($invoice['date'],null));

    if($_GET['action'] == 'edit') {
        $template_content->set('title',$PMDR->getLanguage('admin_transactions_edit'));
        $form->deleteField('notify');
        $form->loadValues($transaction);
    } else {
        $template_content->set('title',$PMDR->getLanguage('admin_transactions_add'));
    }

    if($form->wasSubmitted('submit')) {
        $data = $form->loadValues();
        if(!$form->validate()) {
            $PMDR->addMessage('error',$form->parseErrorsForTemplate());
        } else {
            if($_GET['action']=='add') {
                $transaction_id = $PMDR->get('Invoices')->insertTransaction($data['invoice_id'],$data['transaction_id'],$data['amount'],$data['date'],$data['description'],$data['comments'],$data['gateway_id']);
                if($data['notify']) {
                    $PMDR->get('Email_Templates')->send('invoice_payment',array('to'=>$data['user_id'],'invoice_id'=>$data['invoice_id']));
                }
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_inserted',array($form->getFieldValue('transaction_id'),$PMDR->getLanguage('admin_transactions'))),'insert');
            } elseif($_GET['action'] == 'edit') {
                $PMDR->get('Transactions')->update($data, $_GET['id']);
                $PMDR->addMessage('success',$PMDR->getLanguage('messages_updated',array($form->getFieldValue('transaction_id'),$PMDR->getLanguage('admin_transactions'))),'update');
            }
            if(!empty($_GET['user_id'])) {
                redirect(null,array('user_id'=>$_GET['user_id']));
            } else {
                redirect();
            }
        }
    }
    $template_content->set('content', $form->toHTML());
}

if(!isset($_GET['user_id'])) {
    $template_page_menu = $PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_invoices_menu.tpl');
}
include(PMDROOT_ADMIN.'/includes/template_setup.php');
?>