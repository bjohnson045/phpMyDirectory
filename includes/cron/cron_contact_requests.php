<?php
if(!IN_PMD) exit('File '.__FILE__.' can not be accessed directly.');

function cron_contact_requests($j) {
    global $PMDR, $db;

    $requests = $db->GetAll("SELECT c.*, CONCAT(user_first_name,' ',user_last_name) AS name, user_phone AS phone, user_email AS email FROM ".T_CONTACT_REQUESTS." c INNER JOIN ".T_USERS." u ON c.user_id=u.id WHERE c.status='approved' ORDER BY c.id ASC LIMIT 50");

    foreach($requests AS $request) {
        $where = '';
        $where_parts = array();
        $where_parts[] = 'contact_requests_allow=1';
        $where_parts[] = "id IN(SELECT list_id FROM ".T_LISTINGS_CATEGORIES." WHERE cat_id IN(".$request['categories']."))";
        $where_parts[] = 'user_id !='.$db->Clean($request['user_id']);
        if(!empty($request['location_id'])) {
            $where_parts[] = 'location_id ='.$db->Clean($request['location_id']);
        }
        if(!empty($where_parts)) {
            $where = 'WHERE '.implode(' AND ',$where_parts);
        }
        $data = array(
            'variables'=>array(
                'contact_name'=>$request['name'],
                'contact_email'=>$request['email'],
                'available'=>$request['available'],
                'contact_phone'=>$request['phone'],
                'preferred_contact'=>$request['preferred_contact'],
                'message'=>$request['message']
            )
        );
        if($PMDR->getConfig('contact_requests_messages')) {
            $db->Execute("INSERT INTO ".T_MESSAGES." (user_id_from,user_id_to,contact_request_id,title,date_sent) SELECT ".$request['user_id'].", user_id, ".$request['id'].", 'Contact Request', NOW() FROM ".T_LISTINGS." $where");
            $db->Execute("INSERT INTO ".T_MESSAGES_POSTS." (message_id,user_id,content,date_sent) SELECT id, user_id_from, ?, NOW() FROM ".T_MESSAGES." WHERE contact_request_id=?",array($request['message'],$request['id']));
        } else {
            $db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued,data) SELECT user_id, 'listing', id, 'contact_request', NOW(), ? FROM ".T_LISTINGS."
            $where",array(serialize($data)));
        }
        $db->Execute("UPDATE ".T_CONTACT_REQUESTS." SET status='processed' WHERE id=?",array($request['id']));
    }
    return array('status'=>true);
}

// Add the CRON job to the queue and set it to run based on the backup CRON days setting
$cron['cron_contact_requests'] = array('day'=>-1,'hour'=>-1,'minute'=>0,'run_order'=>20);
?>