<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_cron_report($j) {
    global $PMDR, $db;

    // Get all of the CRON logs from the last run date
    // We use the current run date value because we might have "jumped" several CRON cycles if it got behind for some reason
    if($logs = $db->GetAll("SELECT * FROM ".T_CRON_LOG." WHERE date >= '".$j['last_run_date']."' AND date < '".$j['current_run_date']."'")) {
        // Set the default report values
        $report = array(
            'backup'=>0,
            'links_valid'=>0,
            'links_dead'=>0,
            'links_reciprocal_valid'=>0,
            'links_reciprocal_invalid'=>0,
            'links_reciprocal_required_invalid'=>0,
            'website_screenshots'=>0,
            'cleanup'=>0,
            'queue_sent'=>0,
            'listing_counts'=>0,
            'review_counts'=>0,
            'coordinates_calculated'=>0,
            'coordinates_failed'=>array(),
            'sitemaps_submitted'=>array(),
            'invoices_created'=>array(),
            'invoice_reminders_sent'=>array(),
            'invoice_overdue_1_sent'=>array(),
            'invoice_overdue_2_sent'=>array(),
            'invoice_overdue_3_sent'=>array(),
            'orders_suspended'=>array(),
            'orders_canceled'=>array(),
            'orders_deleted'=>array(),
            'orders_changed'=>array(),
            'website_screenshots'=>0
        );
        // Loop through the logs and update any report values
        foreach($logs as $log) {
            $data = unserialize($log['data']);
            switch($log['id']) {
                // Backup
                case 'cron_backup':
                    if($log['status']) {
                        $report['backup'] = 1;
                    }
                    break;
                // Link Checker
                case 'cron_link_checker':
                    $report['links_dead'] += $data['dead'];
                    $report['links_valid'] += $data['valid'];
                    if(ADDON_LINK_CHECKER) {
                        $report['links_reciprocal_valid'] += $data['reciprocal_valid'];
                        $report['links_reciprocal_required_invalid'] += $data['reciprocal_required_invalid'];
                        $report['links_reciprocal_invalid'] += $data['reciprocal_invalid'];
                    }
                    break;
                // Cleanup
                case 'cron_cleanup':
                    if($log['status']) {
                        $report['cleanup'] = 1;
                    }
                    break;
                // Cleanup
                case 'cron_reminders':
                    if($log['status']) {
                        $report['reminders'] = 1;
                    }
                    break;
                // Mail queue
                case 'cron_email_queue':
                    $report['queue_sent'] += $data['queue_sent'];
                    break;
                // Listing Counts
                case 'cron_listing_counters':
                    if($log['status']) {
                        $report['listing_counts'] = 1;
                    }
                    break;
                // Invoices
                case 'cron_invoices':
                    $report['invoices_created'] = array_merge($report['invoices_created'],$data['invoices_created']);
                    break;
                // Review Counts
                case 'cron_reviews':
                    if($log['status']) {
                        $report['review_counts'] = 1;
                    }
                    break;
                // Geocoding
                case 'cron_geocoding':
                    $report['coordinates_calculated'] += $data['coordinates_calculated'];
                    $report['coordinates_failed'] = array_merge($report['coordinates_failed'],$data['coordinates_failed']);
                    break;
                // Sitemaps
                case 'cron_sitemaps':
                    $report['sitemaps_submitted'] = array_merge($report['sitemaps_submitted'],$data['sitemaps_submitted']);
                    break;
                // Invoice reminders
                case 'cron_invoices_reminders':
                    $report['invoice_reminders_sent'] = array_merge($report['invoice_reminders_sent'],$data['invoice_reminders_sent']);
                    $report['invoice_overdue_1_sent'] = array_merge($report['invoice_overdue_1_sent'],$data['invoice_overdue_1_sent']);
                    $report['invoice_overdue_2_sent'] = array_merge($report['invoice_overdue_2_sent'],$data['invoice_overdue_3_sent']);
                    $report['invoice_overdue_3_sent'] = array_merge($report['invoice_overdue_3_sent'],$data['invoice_overdue_3_sent']);
                    break;
                // Status changes
                case 'cron_status_changes':
                    $report['orders_suspended'] = array_merge($report['orders_suspended'],$data['orders_suspended']);
                    $report['orders_canceled'] = array_merge($report['orders_canceled'],$data['orders_canceled']);
                    $report['orders_deleted'] = array_merge($report['orders_deleted'],$data['orders_deleted']);
                    $report['orders_changed'] = array_merge($report['orders_changed'],$data['orders_changed']);
                // Website screenshots
                case 'cron_website_screenshots':
                    $report['website_screenshots'] += value($data,'website_screenshots',0);
                    break;
            }
        }

        // Construct the CRON report messages
        // We do not give an error for the backup not running as it is generally misunderstood by users.
        $cron_message = ($report['backup'] ? '- Database backup completed'."\n" : '');
        $cron_message .= '- '.(($report['cleanup']) ? 'Cleanup routine completed' : 'Cleanup routine not run')."\n";
        $cron_message .= '- '.(($report['reminders']) ? 'Reminders processed' : 'Reminders not processed')."\n";
        $cron_message .= '- '.(($report['listing_counts']) ? 'Listing counts recalculated for categories and locations' : 'Listings counts not calculated for categories and locations')."\n";
        $cron_message .= '- '.(($report['review_counts']) ? 'Review comments and helpfulness feedback updated' : 'Review comments and helpfulness not updated')."\n";
        if(!$PMDR->getConfig('disable_billing')) {
            $cron_message .= '- '.count(array_filter($report['invoices_created'])).' invoices created'."\n";
            $cron_message .= '- '.count(array_filter($report['invoice_reminders_sent'])).' invoice reminders sent'."\n";
            $cron_message .= '- '.count(array_filter($report['invoice_overdue_1_sent'])).' first invoice overdue reminders sent'."\n";
            $cron_message .= '- '.count(array_filter($report['invoice_overdue_2_sent'])).' second invoice overdue reminders sent'."\n";
            $cron_message .= '- '.count(array_filter($report['invoice_overdue_3_sent'])).' third invoice overdue reminders sent'."\n";
            $cron_message .= '- '.count(array_filter($report['orders_suspended'])).' orders suspended'."\n";
            $cron_message .= '- '.count(array_filter($report['orders_canceled'])).' orders canceled'."\n";
            $cron_message .= '- '.count(array_filter($report['orders_deleted'])).' orders deleted'."\n";
            $cron_message .= '- '.count(array_filter($report['orders_changed'])).' orders changed'."\n";
        }
        if(ADDON_LINK_CHECKER) {
            $cron_message .= '- '.$report['links_valid'].' links validated'."\n";
            $cron_message .= '- '.$report['links_dead'].' dead links found'."\n";
            $cron_message .= '- '.$report['links_reciprocal_valid'].' reciprocal links validated'."\n";
            $cron_message .= '- '.$report['links_reciprocal_invalid'].' reciprocal links not found'."\n";
            $cron_message .= '- '.$report['links_reciprocal_required_invalid'].' required reciprocal links not found'."\n";
        }
        $cron_message .= '- '.$report['website_screenshots'].' website screenshots retrieved.'."\n";
        $cron_message .= '- '.$report['queue_sent'].' queued emails sent'."\n";
        $cron_message .= '- '.$report['coordinates_calculated'].' listings with empty coordinates updated'."\n";
        $cron_message .= '- '.count(array_unique($report['coordinates_failed'])).' listings with empty coordinates failed to update.';
        if(count($report['coordinates_failed'])) {
            $cron_message .= ' ('.implode(',',array_unique($report['coordinates_failed'])).")\n";
        } else {
            $cron_message .= "\n";
        }

        if(count($sitemap_array = array_filter(array_unique($report['sitemaps_submitted'])))) {
            $cron_message .= '- XML sitemap submitted to search engines ('.implode(',',$sitemap_array).')'."\n";
        }
        if($error_count = $db->GetOne("SELECT COUNT(*) FROM ".T_ERROR_LOG)) {
            $cron_message .= '- There are '.$error_count.' errors in the error log'."\n";
        }
        // Send the CRON report email
        $PMDR->get('Email_Templates')->send('admin_cron_summary',array('variables'=>array('cron_messages'=>$cron_message)));
    }
    return array('status'=>true);
}
// Add the CRON job to the queue and set it to run every day
$cron['cron_cron_report'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>12);
?>