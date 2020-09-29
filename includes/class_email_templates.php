<?php
/**
* Email templates class
*/
class Email_Templates extends TableGateway {
    /**
    * Registry
    * @var object Registry
    */
    var $PMDR;

    /**
    * Database
    * @var object Database
    */
    var $db;

    /**
    * Email Templates Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->table = T_EMAIL_TEMPLATES;
    }

    /**
    * Insert email template
    * @param array $data Email template data
    * @return void
    */
    function insert($data) {
        $phrases = $this->PMDR->get('Phrases');
        $data['id'] = Strings::strtolower($data['id']);
        $phrases->insert(array('variablename'=>'email_templates_'.$data['id'].'_name','content'=>$data['name']),'email_templates');
        $phrases->insert(array('variablename'=>'email_templates_'.$data['id'].'_subject','content'=>$data['subject']),'email_templates');
        $phrases->insert(array('variablename'=>'email_templates_'.$data['id'].'_body_html','content'=>$data['body_html']),'email_templates');
        $phrases->insert(array('variablename'=>'email_templates_'.$data['id'].'_body_plain','content'=>$data['body_plain']),'email_templates');

        $attachment_names = array();
        if(is_array($data['attachments'])) {
            foreach($data['attachments']['name'] AS $attachment_index=>$name) {
                if($data['attachments']['error'][$attachment_index] == 0) {
                    move_uploaded_file($data['attachments']['tmp_name'][$attachment_index],TEMP_UPLOAD_PATH.$name);
                    $attachment_names[] = $name;
                }
            }
        }
        $data['attachments'] = serialize(array_unique($attachment_names));

        // Depending on the email template, we might not have moderate set, default to 0
        if(!isset($data['moderate'])) {
            $data['moderate'] = 0;
        }

        $this->db->Execute("INSERT INTO ".T_EMAIL_TEMPLATES." SET id=?, from_address=?, from_name=?,reply_address=?, recipients=?, disable=?, type=?, custom=?, attachments=?, moderate=?", array($data['id'],$data['from_address'],$data['from_name'],$data['reply_address'],$data['recipients'],$data['disable'],$data['type'],$data['custom'],$data['attachments'],$data['moderate']));
    }

    /**
    * Delete email template
    * @param string $id Email template ID
    * @return void
    */
    function delete($id) {
        $phrases = $this->PMDR->get('Phrases');
        $phrases->delete('email_templates_'.$id.'_name','email_templates');
        $phrases->delete('email_templates_'.$id.'_subject','email_templates');
        $phrases->delete('email_templates_'.$id.'_body_html','email_templates');
        $phrases->delete('email_templates_'.$id.'_body_plain','email_templates');
        $this->PMDR->get('Email_Schedules')->delete(array('email_template_id'=>$id));
        $this->PMDR->get('Email_Queue')->delete(array('template_id'=>$id));
        parent::delete($id);

    }

    /**
    * Update email template
    * @param array $data Email template data
    * @param string $id Email template ID
    * @return void
    */
    function update($data, $id) {
        $template = $this->db->GetRow("SELECT * FROM ".T_EMAIL_TEMPLATES." WHERE id=?",array($id));

        $phrases = $this->PMDR->get('Phrases');
        $phrases->updatePhrase($this->PMDR->getConfig('language_admin'),'email_templates','email_templates_'.$id.'_name',$data['name']);
        $phrases->updatePhrase($this->PMDR->getConfig('language_admin'),'email_templates','email_templates_'.$id.'_subject',$data['subject']);
        $phrases->updatePhrase($this->PMDR->getConfig('language_admin'),'email_templates','email_templates_'.$id.'_body_html',$data['body_html']);
        $phrases->updatePhrase($this->PMDR->getConfig('language_admin'),'email_templates','email_templates_'.$id.'_body_plain',$data['body_plain']);

        if(is_array($data['attachments']) AND count($data['attachments'])) {
            if(!empty($template['attachments'])) {
                $attachment_names = unserialize($template['attachments']);
                if($data['attachments_current'] AND count($data['attachments_current'])) {
                    foreach($data['attachments_current'] AS $attachment_current) {
                        unlink_file(TEMP_UPLOAD_PATH.$attachment_current);
                    }
                    $attachment_names = array_diff($attachment_names,$data['attachments_current']);
                }
            } else {
                $attachment_names = array();
            }
            foreach($data['attachments']['name'] AS $attachment_index=>$name) {
                if($data['attachments']['error'][$attachment_index] == 0) {
                    move_uploaded_file($data['attachments']['tmp_name'][$attachment_index],TEMP_UPLOAD_PATH.$name);
                    $attachment_names[] = $name;
                }
            }
        }
        $data['attachments'] = serialize(array_unique($attachment_names));

        // Depending on the email template, we might not have moderate set, default to 0
        if(!isset($data['moderate'])) {
            $data['moderate'] = 0;
        }

        $this->db->Execute("UPDATE ".T_EMAIL_TEMPLATES." SET from_address=?, from_name=?, reply_name=?, reply_address=?, recipients=?, recipients_bcc=?, disable=?, type=?, attachments=?, moderate=? WHERE id=?",array($data['from_address'],$data['from_name'],$data['reply_name'],$data['reply_address'],$data['recipients'],$data['recipients_bcc'],$data['disable'],$data['type'],$data['attachments'],$data['moderate'],$id));
    }

    /**
    * Get email template data from database
    * @param string $id Email template ID
    * @return array
    */
    function getRow($id) {
        if(!$template = parent::getRow($id)) {
            return false;
        }
        return array_merge($template,$this->getFromLanguage($id));
    }

    /**
    * Get email template data from language variables
    * @param string $id Email template ID
    * @return array
    */
    function getFromLanguage($id) {
        $new_variables = array();
        $new_variables['name'] = $this->PMDR->getLanguage('email_templates_'.$id.'_name');
        $new_variables['subject'] = $this->PMDR->getLanguage('email_templates_'.$id.'_subject');
        $new_variables['body_plain'] = $this->PMDR->getLanguage('email_templates_'.$id.'_body_plain');
        $new_variables['body_html'] = $this->PMDR->getLanguage('email_templates_'.$id.'_body_html');
        return $new_variables;
    }

    /**
    * Queue an email template batch
    * @param string $id Email template ID
    * @param array $variables Variables to replace in email template
    */
    function queueAll($id) {
        $template = $this->db->GetRow("SELECT id, type FROM ".T_EMAIL_TEMPLATES." WHERE id=?",array($id));
        $user_field = 'user_id';
        switch($template['type']) {
            case 'user':
                $table = T_USERS;
                $user_field = 'id';
                break;
            case 'listing':
                $table = T_LISTINGS;
                break;
            case 'order':
                $table = T_ORDERS;
                break;
            case 'invoice':
                $table = T_INVOICES;
                break;
            case 'review':
                $table = T_REVIEWS;
                break;
            case 'classified':
                $table = T_CLASSIFIEDS;
                break;
            default:
                return false;
        }

        return $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued) SELECT $user_field,?,id,?,NOW() FROM $table",array($template['type'],$id));
    }

    /**
    * Queue an email template in the mail queue
    * @param string $id Email template ID
    * @param array $variables Variables to replace in email template
    */
    function queue($id, $parameters = array(), $moderate = false) {
        $template = $this->GetRow("SELECT moderate, disable FROM ".T_EMAIL_TEMPLATES." WHERE id=?",array($id));
        if($template['disable']) {
            return false;
        }
        // If we don't force moderation, fall back to the template setting for moderation
        if(!$moderate) {
            $moderate = $template['moderate'];    
        }
        if(isset($parameters['listing_id'])) {
            $type = 'listing';
            $type_id = $parameters['listing_id'];
            $user_id = $this->db->GetOne("SELECT user_id FROM ".T_LISTINGS." WHERE id=?",array($parameters['listing_id']));
        } elseif(isset($parameters['order_id'])) {
            $type = 'order';
            $type_id = $parameters['order_id'];
            $user_id = $this->db->GetOne("SELECT user_id FROM ".T_ORDERS." WHERE id=?",array($parameters['order_id']));
        } elseif(isset($parameters['invoice_id'])) {
            $type = 'invoice';
            $type_id = $parameters['invoice_id'];
            $user_id = $this->db->GetOne("SELECT user_id FROM ".T_INVOICES." WHERE id=?",array($parameters['invoice_id']));
        } elseif(isset($parameters['user_id'])) {
            $type = 'user';
            $type_id = $parameters['user_id'];
            $user_id = $parameters['user_id'];
        } elseif(isset($parameters['review_id'])) {
            $type = 'review';
            $type_id = $parameters['review_id'];
            $user_id = $this->db->GetOne("SELECT user_id FROM ".T_REVIEWS." WHERE id=?",array($parameters['review_id']));
        } elseif(isset($parameters['classified_id'])) {
            $type = 'classified';
            $type_id = $parameters['classified_id'];
            $user_id = $this->db->GetOne("SELECT user_id FROM ".T_CLASSIFIEDS." WHERE id=?",array($parameters['classified_id']));
        } else {
            return false;
        }

        return $this->db->Execute("INSERT INTO ".T_EMAIL_QUEUE." (user_id,type,type_id,template_id,date_queued,data,moderate) VALUES (?,?,?,?,NOW(),?,?)",array($user_id,$type,$type_id,$id,serialize($parameters),$moderate));
    }

    /**
    * Check if the template is an admin area template
    * @param string $id Template ID
    * @return bool
    */
    function isAdminTemplate($id) {
        return (substr($id,0,6) === 'admin_');
    }

    /**
    * Process an email template
    * @param string $id Template ID
    * @param array $parameters
    * @return mixed False on failure
    */
    function process($id, $parameters = array()) {
        if(isset($parameters['template'])) {
            $template = $parameters['template'];
        } else {
            $template = $this->getRow($id);
        }

        if(!$template) {
            if(isset($parameters['template'])) {
                throw new Exception('Email template '.$parameters['template'].' does not exist.');
            } else {
                throw new Exception('Email template '.$id.' does not exist.');
            }
        }

        if(!isset($parameters['variables'])) {
            $parameters['variables'] = array();
        }

        $variables = array();
        // If we need to send the email template to a different user not attached to the specific record ID
        // such as a review submitter we can optionally also specify "user_id" in combination with the record ID
        if(isset($parameters['order_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getOrderVariables($parameters['order_id']));
        }
        if(isset($parameters['invoice_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getInvoiceVariables($parameters['invoice_id']));
        }
        if(isset($parameters['listing_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getListingVariables($parameters['listing_id']));
        }
        if(isset($parameters['classified_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getClassifiedVariables($parameters['classified_id']));
        }
        if(isset($parameters['review_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getReviewVariables($parameters['review_id']));
        }
        if(isset($parameters['user_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getUserVariables($parameters['user_id']));
        }
        if(isset($parameters['blog_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getBlogVariables($parameters['blog_id']));
        }
        if(isset($parameters['event_id'])) {
            $variables = array_merge($variables,$this->PMDR->get('Email_Variables')->getEventVariables($parameters['event_id']));
        }

        if(!isset($parameters['to'])) {
            if($this->isAdminTemplate($id)) {
                if(trim($this->PMDR->getConfig('admin_email')) == '') {
                    throw new Exception('"to" parameter not set and default admin_email setting not set for admin email template ID '.$id);
                }
                $parameters['to'] = $this->PMDR->getConfig('admin_email');
            } else {
                $parameters['to'] = $variables['user_email'];
            }
        } elseif(is_numeric($parameters['to'])) {
            if($email = $this->db->GetOne("SELECT user_email FROM ".T_USERS." WHERE id=? AND user_email!=''",array($parameters['to']))) {
                $parameters['to'] = $email;
                unset($email);
            }
        }

        $email_data = array();

        $email_data['variables'] = array_merge($this->PMDR->get('Email_Variables')->getGeneralVariables(),$variables,$parameters['variables']);

        $extra_recipients = array();
        if($this->PMDR->getConfig('email_recipients')) {
            $extra_recipients += preg_split("/[,\s]+/",$this->PMDR->getConfig('email_recipients'),-1,PREG_SPLIT_NO_EMPTY);
        }

        if(!empty($parameters['recipients']))  {
            $extra_recipients += preg_split("/[,\s]+/",$parameters['recipients'],-1,PREG_SPLIT_NO_EMPTY);
        }

        if(!empty($template['recipients']))  {
            $extra_recipients += preg_split("/[,\s]+/",$template['recipients'],-1,PREG_SPLIT_NO_EMPTY);
        }

        // Get rid of any duplicate email addresses
        $extra_recipients = array_unique($extra_recipients);
        $extra_recipients_count = count($extra_recipients);

        // If we exclude user, use the first extra recipient as main email address
        if(count($parameters['to']) AND !is_null($parameters['to'])) {
            $email_data['recipients'][] = $parameters['to'];
        } elseif($extra_recipients_count > 0) {
            $email_data['recipients'][] = array_shift($extra_recipients);
        } else {
            throw new Exception('No recipients set for template '.$template_id);
        }

        // Add the extra recipients
        if($extra_recipients_count > 0) {
            foreach($extra_recipients as $recipient) {
                // We use this here instead of BCC (swift mailer bug)
                $email_data['recipients'][] = $recipient;
            }
        }

        $recipients_bcc = array();
        if(!empty($parameters['recipients_bcc']))  {
            $recipients_bcc += preg_split("/[,\s]+/",$parameters['recipients_bcc'],-1,PREG_SPLIT_NO_EMPTY);
        }
        if(!empty($template['recipients_bcc']))  {
            $recipients_bcc += preg_split("/[,\s]+/",$template['recipients_bcc'],-1,PREG_SPLIT_NO_EMPTY);
        }
        // Get rid of any duplicate email addresses
        $recipients_bcc = array_unique($recipients_bcc);
        // Add the extra BCC recipients
        if(count($recipients_bcc))  {
            foreach($recipients_bcc as $recipient) {
                $email_data['recipients_bcc'][] = $recipient;
            }
        }

        // Setup mail properties
        $email_data['from_email'] = !empty($template['from_address']) ? $template['from_address'] : $this->PMDR->getConfig('email_from_address');
        $email_data['from_name'] = !empty($template['from_name']) ? $template['from_name'] : $this->PMDR->getConfig('email_from_name');

        if(!empty($template['reply_address'])) {
            $email_data['reply_to'][] = array('email'=>$template['reply_address'],'name'=>(!empty($template['reply_name']) ? $template['reply_name'] : ''));
        }
        $email_data['subject'] = $template['subject'];

        $email_data['message_parts'][] = array('message'=>$template['body_plain'],'type'=>'text/plain');
        if(trim($template['body_html']) != '') {
            $email_data['message_parts'][] = array('message'=>$template['body_html'],'type'=>'text/html');
        }

        if(!empty($template['attachments']) AND $template_attachments = unserialize($template['attachments']) AND count($template_attachments)) {
            foreach($template_attachments AS $template_attachment) {
                if(is_readable(TEMP_UPLOAD_PATH.$template_attachment)) {
                    $email_data['attachments'][] = array(
                        'file'=>TEMP_UPLOAD_PATH.$template_attachment,
                        'file_name'=>$template_attachment,
                        'file_type'=>get_file_format(TEMP_UPLOAD_PATH.$template_attachment),
                        'file_format'=>'file'
                    );
                }
            }
        }

        if(!empty($parameters['attachment'])) {
            if(is_array($parameters['attachment']['name'])) {
                foreach($parameters['attachment']['name'] AS $attachment_index=>$attachment) {
                    if(!empty($attachment) AND $parameters['attachment']['error'][$attachment_index] == 0 AND is_readable($parameters['attachment']['tmp_name'][$attachment_index])) {
                        $email_data['attachments'][] = array(
                            'file'=>$parameters['attachment']['tmp_name'][$attachment_index],
                            'file_name'=>$parameters['attachment']['name'][$attachment_index],
                            'file_type'=>$parameters['attachment']['type'][$attachment_index],
                            'file_format'=>'file'
                        );
                    }
                }
            } elseif($parameters['attachment']['error'] === 0 AND is_readable($parameters['attachment']['tmp_name'])) {
                $email_data['attachments'][] = array(
                    'file'=>$parameters['attachment']['tmp_name'],
                    'file_name'=>$parameters['attachment']['name'],
                    'file_type'=>$parameters['attachment']['type'],
                    'file_format'=>'file'
                );
            } elseif(isset($parameters['attachment']['data'])) {
                $email_data['attachments'][] = array(
                    'file'=>$parameters['attachment']['data'],
                    'file_name'=>$parameters['attachment']['name'],
                    'file_type'=>$parameters['attachment']['type'],
                    'file_format'=>'data'
                );
            }
        }

        if(is_array($email_data['variables'])) {
            foreach($email_data['variables'] as $variable_key=>$variable) {
                $email_data['subject'] = str_replace("*$variable_key*",$variable,$email_data['subject']);
                foreach($email_data['message_parts'] as $message_key=>$message_part) {
                    if($message_part['type'] == 'text/html') {
                        $variable = nl2br($variable);
                    } elseif($message_part['type'] == 'text/plain') {
                        $variable = $this->PMDR->get('Cleaner')->strip_tags(Strings::br2nl($variable));
                    }
                    $email_data['message_parts'][$message_key]['message'] = str_replace("*$variable_key*",$variable,$message_part['message']);
                }
                if(isset($email_data['reply_to']) AND count($email_data['reply_to'])) {
                    foreach($email_data['reply_to'] AS &$replyto) {
                        $replyto['name'] = str_replace("*$variable_key*",$variable,$replyto['name']);
                        $replyto['email'] = str_replace("*$variable_key*",$variable,$replyto['email']);
                    }
                }
                $email_data['from_email'] = str_replace("*$variable_key*",$variable,$email_data['from_email']);
                $email_data['from_name'] = str_replace("*$variable_key*",$variable,$email_data['from_name']);
            }
        }

        $email_data['subject'] = parse_php($email_data['subject']);
        if(is_array($email_data['message_parts'])) {
            foreach($email_data['message_parts'] as $message_key=>$message_part) {
                $email_data['message_parts'][$message_key]['message'] = parse_php($message_part['message']);
            }
        }
        return $email_data;
    }

    /**
    * Build the email from processed data
    * @param array $data
    * @return Mailer object
    */
    function buildEmail($data) {
        $mailer = $this->PMDR->getNew('Email_Handler');
        if(is_array(value($data,'recipients'))) {
            foreach($data['recipients'] AS $recipient) {
                $mailer->addRecipient($recipient);
            }
        }
        if(is_array(value($data,'recipients_bcc'))) {
            foreach($data['recipients_bcc'] AS $recipient_bcc) {
                $mailer->addBCC($recipient_bcc);
            }
        }
        if(is_array(value($data,'reply_to'))) {
            foreach($data['reply_to'] AS $reply_to) {
                $mailer->addReplyTo($reply_to['email'],$reply_to['name']);
            }
        }
        $mailer->from_email = $data['from_email'];
        $mailer->from_name = $data['from_name'];
        $mailer->subject = $data['subject'];
        if(is_array(value($data,'message_parts'))) {
            foreach($data['message_parts'] AS $message_part) {
                $mailer->addMessagePart($message_part['message'],$message_part['type']);
            }
        }
        if(is_array(value($data,'attachments'))) {
            foreach($data['attachments'] AS $attachment) {
                $mailer->addAttachment($attachment['file'],$attachment['file_name'],$attachment['file_type'],$attachment['file_format']);
            }
        }
        return $mailer;
    }

    /**
    * Send an email based on an email template
    * @param string $id Email template ID
    * @param array $variables Variables to replace in email template
    * @return int Number of emails sent
    */
    function send($id, $parameters = array()) {
        $moderate = $this->db->GetOne("SELECT moderate FROM ".T_EMAIL_TEMPLATES." WHERE id=?",array($id));
        if(($moderate AND (!isset($parameters['moderate']) OR $parameters['moderate'])) OR (isset($parameters['moderate']) AND $parameters['moderate'])) {
            return $this->queue($id,$parameters,true);
        } else {
            try {
                $email_data = $this->process($id,$parameters);
            } catch (Exception $e) {
                trigger_error($e->getMessage(),E_USER_WARNING);
                return false;
            }
            return $this->buildEmail($email_data)->send();
        }
    }

    /**
    * Get the email variables template with populated variables
    * @param string|null $template_id
    * @param string $type
    */
    function getVariablesTemplate($template_id, $type) {
        $template_page_menu = $this->PMDR->getNew('Template',PMDROOT_ADMIN.TEMPLATE_PATH_ADMIN.'blocks/admin_email_templates_variables.tpl');
        switch($type) {
            case 'listing':
                $template_page_menu->set('order_variables',$this->PMDR->get('Email_Variables')->getOrderKeys());
                $template_page_menu->set('listing_variables',$this->PMDR->get('Email_Variables')->getListingKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'classified':
                $template_page_menu->set('classified_variables',$this->PMDR->get('Email_Variables')->getClassifiedKeys());
                $template_page_menu->set('listing_variables',$this->PMDR->get('Email_Variables')->getListingKeys());
                break;
            case 'invoice':
                $template_page_menu->set('invoice_variables',$this->PMDR->get('Email_Variables')->getInvoiceKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'order':
                $template_page_menu->set('order_variables',$this->PMDR->get('Email_Variables')->getOrderKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'user':
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'review':
                $template_page_menu->set('review_variables',$this->PMDR->get('Email_Variables')->getReviewKeys());
                $template_page_menu->set('listing_variables',$this->PMDR->get('Email_Variables')->getListingKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'blog':
                $template_page_menu->set('blog_variables',$this->PMDR->get('Email_Variables')->getBlogKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;
            case 'event':
                $template_page_menu->set('listing_variables',$this->PMDR->get('Email_Variables')->getListingKeys());
                $template_page_menu->set('event_variables',$this->PMDR->get('Email_Variables')->getEventKeys());
                $template_page_menu->set('user_variables',$this->PMDR->get('Email_Variables')->getUserKeys());
                break;

        }
        $template_page_menu->set('general_variables',$this->PMDR->get('Email_Variables')->getGeneralKeys());

        switch($template_id) {
            case 'admin_contact_submission':
            case 'contact_response':
                $specific_variables = array('ip_address','name','email','confirm_email','comments');
                $specific_variables = array_merge($specific_variables,$this->db->GetCol("SELECT CONCAT('custom_',f.id) AS id FROM ".T_FIELDS." f, ".T_FIELDS_GROUPS." fg WHERE f.group_id=fg.id AND fg.type='contact'"));
                break;
            case 'password_reset_request':
                $specific_variables = array('user_password_reminder_url');
                break;
            case 'password_reset':
                $specific_variables = array('user_new_password');
                break;
            case 'user_registration':
                $specific_variables = array('user_url','user_password');
                break;
            case 'listings_send_email':
            case 'admin_listings_send_email':
            case 'listings_send_email_copy':
                $specific_variables = array('message','from_name','from_email','ip_address');
                $specific_variables = array_merge($specific_variables,$this->db->GetCol("SELECT CONCAT('custom_',f.id) AS id FROM ".T_FIELDS." f, ".T_FIELDS_GROUPS." fg WHERE f.group_id=fg.id AND fg.type='send_message'"));
                break;
            case 'listings_send_email_friend':
            case 'admin_listings_send_email_friend':
                $specific_variables = array('message','from_name','from_email','ip_address');
                $specific_variables = array_merge($specific_variables,$this->db->GetCol("SELECT CONCAT('custom_',f.id) AS id FROM ".T_FIELDS." f, ".T_FIELDS_GROUPS." fg WHERE f.group_id=fg.id AND fg.type='send_message_friend'"));
                break;
            case 'listings_claim':
            case 'admin_listings_claim':
                $specific_variables = array('comments');
                $specific_variables = array_merge($specific_variables,$this->db->GetCol("SELECT CONCAT('custom_',f.id) AS id FROM ".T_FIELDS." f, ".T_FIELDS_GROUPS." fg WHERE f.group_id=fg.id AND fg.type='claim_listing'"));
                break;
            case 'listings_suggestion':
            case 'admin_listings_suggestion':
                $specific_variables = array('comments');
                break;
            case 'invoice_payment':
            case 'admin_invoice_payment':
                $specific_variables = array();
                break;
            case 'listing_claim_denied':
            case 'listing_claim_approved':
                $specific_variables = array('claim_id');
                break;
            case 'order_status_change':
                $specific_variables = array();
                break;
            case 'admin_password_reset':
                $specific_variables = array('admin_new_password');
                break;
            case 'admin_password_reset_request':
                $specific_variables = array('admin_password_reminder_url');
                break;
            case 'updates_approved':
            case 'updates_rejected':
                $specific_variables = array('message');
                break;
            case 'admin_bot_detection':
                $specific_variables = array('bot','url','date');
                break;
            case 'admin_cron_summary':
                $specific_variables = array('cron_messages');
                break;
            case 'user_email_confirmation_reminder':
                $specific_variables = array('user_url');
                break;
            case 'admin_order_changed':
                $specific_variables = array('order_change_type','order_old_value','order_new_value');
                break;
            case 'admin_import_status':
                $specific_variables = array('import_name','import_status');
                break;
            case 'message_new_reply':
                $specific_variables = array('message_id');
                break;
            case 'contact_request':
                $specific_variables = array('contact_name','contact_email','contact_phone','available','preferred_contact','message');
                break;
            case 'contact_request_approved':
            case 'admin_contact_request_submitted':
                $specific_variables = array('contact_request_id');
                break;
            case 'admin_blog_comment_submitted':
                $specific_variables = array('comment','view_comment_url','approve_comment_url');
                break;
            case 'blog_comment_submitted':
                $specific_variables = array('comment');
                break;
        }
        $template_page_menu->set('specific_variables',$specific_variables);
        return $template_page_menu;
    }
}
?>