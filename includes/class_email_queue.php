<?php
/**
* Mail Queue class used to handle the construction and sending of mail queues based on batches.
* @package Directory
*/
class Email_Queue extends TableGateway {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database Database object
    */
    var $db;

    /**
    * Email_Queue constructor
    * @param object $PMDR Registry
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->table = T_EMAIL_QUEUE;
    }

    /**
    * Get the email data from a queued email
    * @param int $id
    * @return mixed false|array Email Data
    */
    function getPreview($id) {
        if($email = $this->db->GetRow("SELECT * FROM ".T_EMAIL_QUEUE." WHERE id=?",array($id))) {
            $queue_data = unserialize($email['data']);
            if(!isset($queue_data['to'])) {
                $queue_data['to'] = $email['user_id'];
            }
            if(!is_null($email['campaign_id'])) {
                $campaign = $this->db->GetRow("SELECT * FROM ".T_EMAIL_CAMPAIGNS." WHERE id=?",array($email['campaign_id']));
                $template_content = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_queue_email.tpl');
                $template_content->set('subject',$campaign['subject']);
                $template_content->set('from_name',$campaign['from_name']);
                $template_content->set('from_email',$campaign['from_email']);
                $template_content->set('message_parts',array(array('type'=>'text/plain','message'=>$campaign['body_text']),array('type'=>'text/html','message'=>$campaign['body_html'])));
                if($email['type'] == 'user') {
                    $variables = $this->PMDR->get('Email_Variables')->getUserVariables($email['type_id']);
                } elseif($email['type'] == 'listing') {
                    $variables = $this->PMDR->get('Email_Variables')->getListingVariables($email['type_id']);
                }
                $template_content->set('recipients',array($variables['user_email']));
                return $template_content;
            } elseif($email_data = $this->PMDR->get('Email_Templates')->process($email['template_id'],$queue_data)) {
                $template_content = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_queue_email.tpl');
                $template_content->set('subject',$email_data['subject']);
                $template_content->set('from_name',$email_data['from_name']);
                $template_content->set('from_email',$email_data['from_email']);
                $template_content->set('recipients',$email_data['recipients']);

                $template_content->set('recipients',$email_data['recipients']);
                foreach($email_data['message_parts'] AS &$part) {
                    if($part['type'] == 'text/plain') {
                        $part['message'] = nl2br($part['message']);
                    }
                }
                $template_content->set('message_parts',$email_data['message_parts']);
                return $template_content;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
    * Process an email according to the queue ID
    * @param int $id
    */
    function processEmail($id) {
        return $this->processQueue(1,array('id'=>$id));
    }

    /**
    * Process the email queue, sending emails in the queue
    *
    * @param integer $process_number Number of emails to process
    * @param integer $batch_id Batch ID used to only send emails from a specific batch
    * @return Number of emails sent
    */
    function processQueue($process_number,$parameters = array()) {
        $where = array();
        if(isset($parameters['id'])) {
            $emails = $this->db->GetAll("SELECT * FROM ".T_EMAIL_QUEUE." WHERE id=?",array($parameters['id']));
        } else {
            $emails = $this->db->GetAll("SELECT * FROM ".T_EMAIL_QUEUE." WHERE moderate=0 LIMIT ".$process_number);
        }
        if(count($emails) > 0) {
            /** @var Email_Handler */
            $mailer = $this->PMDR->get('Email_Handler');
            $sent_number = 0;
            foreach($emails as $email) {
                if(!is_null($email['campaign_id'])) {
                    if(!$campaign = $this->db->GetRow("SELECT * FROM ".T_EMAIL_CAMPAIGNS." WHERE id=?",array($email['campaign_id']))) {
                        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE id=?",array($email['id']));
                        continue;
                    }
                    if($email['type'] == 'user') {
                        $variables = $this->PMDR->get('Email_Variables')->getUserVariables($email['type_id']);
                    } elseif($email['type'] == 'listing') {
                        $variables = $this->PMDR->get('Email_Variables')->getListingVariables($email['type_id']);
                    }
                    $variables = array_merge($this->PMDR->get('Email_Variables')->getGeneralVariables(),$variables);
                    $mailer->from_email = $campaign['from_email'];
                    $mailer->from_name = $campaign['from_name'];
                    if(!empty($campaign['reply_email'])) {
                        $mailer->addReplyTo($campaign['reply_email']);
                    }
                    if(!empty($campaign['bounce_email'])) {
                        $mailer->addReturnPath($campaign['bounce_email']);
                    }
                    if($campaign['attachment'] != '') {
                        $mailer->addAttachment(TEMP_UPLOAD_PATH.$campaign['attachment'],$campaign['attachment'],$campaign['attachment_mimetype']);
                    }
                    $mailer->subject = $this->PMDR->get('Email_Variables')->replace($campaign['subject'],$variables);
                    $mailer->addMessagePart($this->PMDR->get('Email_Variables')->replace($campaign['body_text'],$variables));
                    if($campaign['body_html'] != '') {
                        $mailer->addMessagePart($this->PMDR->get('Email_Variables')->replace($campaign['body_html'],$variables,true), "text/html");
                    }

                    if(!empty($variables['user_email'])) {
                        $mailer->addRecipient($variables['user_email']);
                        if($mailer->send()) {
                            $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE id=?",array($email['id']));
                            $sent_number++;
                        }
                    } else {
                        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE id=?",array($email['id']));
                    }
                } elseif(!is_null($email['template_id'])) {
                    if(!$data = unserialize($email['data'])) {
                        $data = array();
                    }
                    $data['moderate'] = false;
                    $data[$email['type'].'_id'] = $email['type_id'];
                    if(!isset($data['to']) AND !$this->PMDR->get('Email_Templates')->isAdminTemplate($email['template_id'])) {
                        $data['to'] = $email['user_id'];
                    }
                    if($this->PMDR->get('Email_Templates')->send($email['template_id'],$data)) {
                        $this->db->Execute("DELETE FROM ".T_EMAIL_QUEUE." WHERE id=?",array($email['id']));
                        $sent_number++;
                    }
                }
                $mailer->flush();
            }
            return $sent_number;
        } else {
            return 0;
        }
    }

    /**
    * Empty the email queue
    * @return boolean
    */
    function emptyQueue() {
        return $this->db->Execute("TRUNCATE ".T_EMAIL_QUEUE);
    }

    /**
    * Approve an email that requires moderation
    * @param int $id Email queue ID
    * @return boolean
    */
    function approve($id) {
        return $this->db->Execute("UPDATE ".T_EMAIL_QUEUE." SET moderate=0 WHERE id=?",array($id));
    }

    /**
    * Get number of emails that are currently moderated
    * @return int
    */
    function getCountModerated() {
        return $this->getCount(array('moderate'=>'1'));
    }
}
?>