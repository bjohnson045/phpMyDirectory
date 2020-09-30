<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_events($j) {
    global $PMDR, $db;

    $expired_events = $db->GetAll("SELECT id, user_id FROM ".T_EVENTS." WHERE expired_sent=0 AND COALESCE(recurring_end,date_end,date_start) < '".$j['current_run_date']."'");
    foreach($expired_events AS $event) {
        $PMDR->get('Email_Templates')->queue('events_expired',array('event_id'=>$event['id'],'to'=>$event['user_id']));
        $PMDR->get('Email_Templates')->queue('admin_events_expired',array('event_id'=>$event['id']));
        $db->Execute("UPDATE ".T_EVENTS." SET expired_sent=1 WHERE id=?",array($event['id']));
    }

    $upcoming_events = $db->GetAll("SELECT e.id, user_id, ed.date_start, ed.date_end FROM ".T_EVENTS." e INNER JOIN ".T_EVENTS_DATES." ed ON e.id=ed.event_id WHERE ed.rsvp_reminder_sent=0 AND DATE_SUB(ed.date_start, INTERVAL 5 DAY) < '".$j['current_run_date']."'");
    foreach($expired_events AS $event) {
        $rsvps = $db->GetAll("SELECT user_id FROM ".T_EVENTS_RSVP." WHERE event_id=?",array($event['id']));
        foreach($rsvps AS $rsvp) {
            $PMDR->get('Email_Templates')->queue('events_rsvp_reminder',array('event_id'=>$event['id'],'to'=>$rsvp['user_id']));
        }
        $db->Execute("UPDATE ".T_EVENTS_DATES." SET rsvp_reminder_sent=1 WHERE event_id=? AND date_start=? AND date_end=?",array($event['id'],$event['date_start'],$event['date_end']));
    }

    return array('status'=>true);
}
$cron['cron_events'] = array('day'=>-1,'hour'=>0,'minute'=>0,'run_order'=>6);
?>