<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_invoices_reminders($j) {
    global $PMDR, $db;

    if($PMDR->getConfig('disable_billing')) {
        return array(
            'status'=>true,
            'data'=>array(
                'invoice_reminders_sent'=>array(),
                'invoice_overdue_1_sent'=>array(),
                'invoice_overdue_2_sent'=>array(),
                'invoice_overdue_3_sent'=>array()
            )
        );
    }

    // Invoice Reminder
    $cron_invoices = array();
    if($PMDR->getConfig('invoice_reminder_days')) {
        // Get invoices where the due date is X days away
        $invoices = $db->GetAll("SELECT id, user_id, date_due, date_paid, date, total, IF(date_due <= CURDATE(),1,0) AS skip FROM ".T_INVOICES." WHERE status='unpaid' AND DATE_SUB(date_due,INTERVAL ".$PMDR->getConfig('invoice_reminder_days')." DAY) <= '".$j['current_run_date']."' AND reminder_sent=0");
        foreach($invoices as $invoice) {
            // Skip invoices where the due date has past or is today (i.e. new orders)
            // If skip the email here will never send because the second part of the 'if' statement is never evaluated
            if($invoice['skip'] OR $PMDR->get('Email_Templates')->queue('invoice_reminder',array('to'=>$invoice['user_id'],'invoice_id'=>$invoice['id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET reminder_sent=1 WHERE id=?",array($invoice['id']));
                if(!$invoice['skip']) {
                    $cron_invoices[] = $invoice['id'];
                }
            }
        }
    }
    $cron_data['data']['invoice_reminders_sent'] = $cron_invoices;

    // Overdue Invoice Reminders
    // Reminder 1
    $cron_invoices = array();
    if($PMDR->getConfig('invoice_overdue_days_1')) {
        $invoices = $db->GetAll("SELECT id, user_id, date_due, date_paid, date, total FROM ".T_INVOICES." WHERE status='unpaid' AND DATE_ADD(date_due,INTERVAL ".$PMDR->getConfig('invoice_overdue_days_1')." DAY) <= '".$j['current_run_date']."' AND overdue_1_sent=0");
        foreach($invoices as $invoice) {
            if($db->GetOne("SELECT disable_overdue_notices FROM ".T_USERS." WHERE id=?",array($invoice['user_id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_1_sent=1 WHERE id=?",array($invoice['id']));
                continue;
            }
            if($PMDR->get('Email_Templates')->queue('invoice_overdue_1',array('to'=>$invoice['user_id'],'invoice_id'=>$invoice['id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_1_sent=1 WHERE id=?",array($invoice['id']));
                $cron_invoices[] = $invoice['id'];
            }
        }
    }
    $cron_data['data']['invoice_overdue_1_sent'] = $cron_invoices;

    // Reminder 2
    $cron_invoices = array();
    if($PMDR->getConfig('invoice_overdue_days_2')) {
        $invoices = $db->GetAll("SELECT id, user_id, date_due, date_paid, date, total FROM ".T_INVOICES." WHERE status='unpaid' AND DATE_ADD(date_due,INTERVAL ".$PMDR->getConfig('invoice_overdue_days_2')." DAY) <= '".$j['current_run_date']."' AND overdue_2_sent=0");
        foreach($invoices as $invoice) {
            if($db->GetOne("SELECT disable_overdue_notices FROM ".T_USERS." WHERE id=?",array($invoice['user_id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_2_sent=1 WHERE id=?",array($invoice['id']));
                continue;
            }
            if($PMDR->get('Email_Templates')->queue('invoice_overdue_2',array('to'=>$invoice['user_id'],'invoice_id'=>$invoice['id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_2_sent=1 WHERE id=?",array($invoice['id']));
                $cron_invoices[] = $invoice['id'];
            }
        }
    }
    $cron_data['data']['invoice_overdue_2_sent'] = $cron_invoices;

    // Reminder 3
    $cron_invoices = array();
    if($PMDR->getConfig('invoice_overdue_days_3')) {
        $invoices = $db->GetAll("SELECT id, user_id, date_due, date_paid, date, total FROM ".T_INVOICES." WHERE status='unpaid' AND DATE_ADD(date_due,INTERVAL ".$PMDR->getConfig('invoice_overdue_days_3')." DAY) <= '".$j['current_run_date']."' AND overdue_3_sent=0");
        foreach($invoices as $invoice) {
            if($db->GetOne("SELECT disable_overdue_notices FROM ".T_USERS." WHERE id=?",array($invoice['user_id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_3_sent=1 WHERE id=?",array($invoice['id']));
                continue;
            }
            if($PMDR->get('Email_Templates')->queue('invoice_overdue_3',array('to'=>$invoice['user_id'],'invoice_id'=>$invoice['id']))) {
                $db->Execute("UPDATE ".T_INVOICES." SET overdue_3_sent=1 WHERE id=?",array($invoice['id']));
                $cron_invoices[] = $invoice['id'];
            }
        }
    }
    $cron_data['data']['invoice_overdue_3_sent'] = $cron_invoices;

    $cron_data['status'] = true;

    unset($invoices);
    unset($invoice);

    return $cron_data;
}
$cron['cron_invoices_reminders'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>10);
?>