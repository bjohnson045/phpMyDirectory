<?php
/**
* Class Email_Handler
* Used to encapsulate mail object in order to remain uniform
*/
class Email_Handler {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * @var object Swift Mailer
    */
    var $mailer;
    /**
    * @var object Swift Mailer Logger
    */
    var $logger;
    /**
    * @var string $from_name Email from name
    */
    var $from_name;
    /**
    * @var string $from_email From email address
    */
    var $from_email;
    /**
    * @var array $recipients Recipients
    */
    var $recipients = array();
    /**
    * @var array $recipients_cc CC Recipients
    */
    var $recipients_cc = array();
    /**
    * @var array $recipients_bcc BCC Recipients
    */
    var $recipients_bcc = array();
    /**
    * @var array $recipients_all All recipients
    */
    var $recipients_all = array();
    /**
    * @var array $replyto Reply-To addresses
    */
    var $replyto = array();
    /**
    * @var string $return_path Return path email (bounce)
    */
    var $return_path;
    /**
    * @var string $subject Email subject
    */
    var $subject;
    /**
    * @var array $attachments Array of attachments
    */
    var $attachments = array();
    /**
    * @var array $message_parts Array of message part objects
    */
    var $message_parts = array();
    /**
    * @var boolean $logging Logging on/off
    */
    var $error_logging = false;
    /**
    * @var boolean $force_connection Force a specific connection
    */
    var $options;
    /**
    * @var array $log_data Additional data to add to the email log
    */
    var $log_data = array();

    /**
    * Email_Handler Constructor
    * @param object $PMDR Registry
    */
    function __construct($PMDR, $parameters = array()) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('DB');
        $this->options = array (
            'force_connection'=>false
        );
        $this->options = array_merge($this->options,$parameters);
        $this->mailer = Swift_Mailer::newInstance($this->getConnection());
        if($this->error_logging) {
            $this->logger = new Swift_Plugins_Loggers_ArrayLogger();
            $this->mailer->registerPlugin(new Swift_Plugins_LoggerPlugin($this->logger));
        }
    }

    /**
    * Add recipient
    * @param string $email Email Address
    * @param string $name Recipient Name
    * @return void
    */
    function addRecipient($email, $name='') {
        if(empty($email)) return false;
        $this->recipients[] = array('email'=>$email,'name'=>$name);
        $this->recipients_all[] = array('email'=>$email,'name'=>$name);
    }

    /**
    * Add reply to recipient
    * @param string $email Email Address
    * @param string $name Recipient Name
    * @return void
    */
    function addReplyTo($email, $name='') {
        if(empty($email)) return false;
        $this->replyto[] = array('email'=>$email,'name'=>$name);
    }

    /**
    * Add return path recipient
    * @param string $email Email Address
    * @param string $name Recipient Name
    * @return void
    */
    function addReturnPath($email) {
        if(empty($email)) return false;
        $this->return_path = $email;
    }

    /**
    * Add CC Address
    * @param string $email Email Address
    * @param string $name Recipient Name
    * @return void
    */
    function addCC($email, $name='') {
        if(empty($email)) return false;
        $this->recipients_cc[] = array('email'=>$email,'name'=>$name);
        $this->recipients_all[] = array('email'=>$email,'name'=>$name);
    }

    /**
    * Add BCC Address
    * @param string $email Email Address
    * @param string $name Recipient Name
    * @return void
    */
    function addBCC($email, $name='') {
        if(empty($email)) return false;
        $this->recipients_bcc[] = array('email'=>$email,'name'=>$name);
        $this->recipients_all[] = array('email'=>$email,'name'=>$name);
    }

    /**
    * Add Attachment
    * @param string $file File Name
    * @param string $filename File Name used in the email
    * @param string $mimetype Mime type of attachment
    * @return void
    */
    function addAttachment($file, $filename, $mimetype='application/octet-stream', $type='path') {
        $this->attachments[] = array('file'=>$file,'filename'=>$filename,'mimetype'=>$mimetype,'type'=>$type);
    }

    /**
    * Add Message Part
    * @param string $message Message Text
    * @param string $mimetype Mime type of message part
    * @return void
    */
    function addMessagePart($message, $mimetype='text/plain') {
        $this->message_parts[] = array('message'=>$message,'mimetype'=>$mimetype);
    }

    /**
    * Get the connection used to send the email
    */
    function getConnection() {
        // If config contains a SMTP host
        if($this->PMDR->getConfig('email_smtp_host') != '') {
            $smtp_connection = Swift_SmtpTransport::newInstance($this->PMDR->getConfig('email_smtp_host'),25);
            $smtp_connection->setTimeout(15);
            if($this->PMDR->getConfig('email_smtp_port') != '') {
                $smtp_connection->setPort($this->PMDR->getConfig('email_smtp_port'));
            }
            if($this->PMDR->getConfig('email_smtp_encryption') != 'none') {
                $smtp_connection->setEncryption($this->PMDR->getConfig('email_smtp_encryption'));
            }

            // If we require auth and user/pass are both entered
            if($this->PMDR->getConfig('email_smtp_require_auth') AND $this->PMDR->getConfig('email_smtp_user') != '' AND $this->PMDR->getConfig('email_smtp_pass') !='') {
                $smtp_connection->setUsername($this->PMDR->getConfig('email_smtp_user'));
                $smtp_connection->setPassword($this->PMDR->getConfig('email_smtp_pass'));
            }
        }
        // If sendmail path is not empty add it as a connection type
        if(trim($this->PMDR->getConfig('email_sendmail_path')) != '') {
            $sendmail_connection = Swift_SendmailTransport::newInstance($this->PMDR->getConfig('email_sendmail_path').' -bs');
        }
        //Add standard mail() to connection list as fallback
        $mail_connection = Swift_MailTransport::newInstance();

        $connections = array();
        switch($this->PMDR->getConfig('email_preferred_connection')) {
            case 'smtp':
                $connections[] =&$smtp_connection;
                if(!$this->options['force_connection']) {
                    if($sendmail_connection) {
                        $connections[] =&$sendmail_connection;
                    }
                    $connections[] =&$mail_connection;
                }
                break;
            case 'sendmail':
                $connections[] =&$sendmail_connection;
                if(!$this->options['force_connection']) {
                    if($smtp_connection) {
                        $connections[] =&$smtp_connection;
                    }
                    $connections[] =&$mail_connection;
                }
                break;
            case 'mail':
                $connections[] =&$mail_connection;
                break;
        }

        if(count($connections) > 1) {
            $connection = Swift_FailoverTransport::newInstance($connections);
        } else {
            $connection = $connections[0];
        }
        return $connection;
    }

    /**
    * Flush all recipients
    * @return void
    */
    function flush() {
        $this->message_parts = array();
        $this->attachments = array();
        $this->recipients = array();
        $this->recipients_cc = array();
        $this->recipients_bcc = array();
        $this->recipients_all = array();
        $this->subject = '';
        $this->from_email = '';
        $this->from_name = '';
    }

    /**
    * Send an email
    * Acts as a wrapper to sendEmail because the SWIFT mailer has an issue sending emails if mbstring.func_overload equals 2
    */
    function send() {
        if(function_exists('mb_internal_encoding') AND ((int) ini_get('mbstring.func_overload')) === 2) {
            $encoding = mb_internal_encoding();
            mb_internal_encoding('ASCII');
        }
        $status = $this->sendEmail();
        if(isset($encoding)) {
            mb_internal_encoding($encoding);
        }
        return $status;
    }

    /**
    * Send email(s)
    * @return integer Number of emails sent
    */
    function sendEmail() {
        try {
            $message = Swift_Message::newInstance()->setCharset(CHARSET);
            $message->setSubject($this->subject);

            try {
                $message->setFrom(array($this->from_email=>$this->from_name));
            } catch(Swift_RfcComplianceException $e) {
                trigger_error('From email address not valid ('.$this->from_email.') for email with subject: '.$this->subject,E_USER_WARNING);
                $this->error = 'From email address not valid';
                return -1;
            }

            try {
                foreach($this->replyto AS $replyto) {
                    if($replyto['name'] != '') {
                        $message->addReplyTo($replyto['email'],$replyto['name'],E_USER_WARNING);
                    } else {
                        $message->addReplyTo($replyto['email']);
                    }
                }
            } catch(Swift_RfcComplianceException $e) {
                trigger_error('Reply to email address not valid: '.$replyto['email'],E_USER_WARNING);
                $this->error = 'Reply to email address not valid';
            }

            try {
                $message->setReturnPath($this->return_path);
            } catch(Swift_RfcComplianceException $e) {
                trigger_error('Return path email address not valid',E_USER_WARNING);
                $this->error = 'Return path email address not valid';
            }

            // Add message parts
            foreach($this->message_parts as $key=>$message_part) {
                // Attach first message part as body, all of the rest as parts
                if($message_part['mimetype'] == 'text/html' AND preg_match_all("/\< *[img][^\>]*[src] *= *[\"\']{0,1}([^\"\'\ >]*)/si",$message_part['message'],$matches)) {
                    foreach($matches[1] AS $match) {
                        if(@file_exists($match) OR url_exists($match)) {
                            $message_part['message'] = str_replace($match,$message->embed(Swift_Image::fromPath($match)),$message_part['message']);
                        }
                    }
                }
                $message->addPart($message_part['message'],$message_part['mimetype'],CHARSET);
            }

            // Add attachments
            foreach($this->attachments as $attachment) {
                if($attachment['type'] == 'data') {
                    $message->attach(Swift_Attachment::newInstance($attachment['file'],$attachment['mimetype'])->setFilename($attachment['filename']));
                } else {
                    $message->attach(Swift_Attachment::fromPath($attachment['file'],$attachment['mimetype'])->setFilename($attachment['filename']));
                }
            }

            try {
                // Add recipients
                foreach($this->recipients AS $recipient) {
                    if($recipient['name'] != '') {
                        $message->addTo(array($recipient['email']=>$recipient['name']));
                    } else {
                        $message->addTo($recipient['email']);
                    }
                }
            } catch (Swift_RfcComplianceException $e) {
                trigger_error('Email Address Format Error: '.$e->getMessage(),E_USER_WARNING);
                $this->error = 'Email Address Format Error: '.$e->getMessage();
                return -1;
            }
            try {
                // Add CC's
                foreach($this->recipients_cc AS $recipient) {
                    if($recipient['name'] != '') {
                        $message->addCc(array($recipient['email']=>$recipient['name']));
                    } else {
                        $message->addCc($recipient['email']);
                    }
                }
            } catch (Swift_RfcComplianceException $e) {
                trigger_error('Email Address Format Error: '.$e->getMessage(),E_USER_WARNING);
                $this->error = 'Email Address Format Error: '.$e->getMessage();
            }
            try {
                // Add BCC's
                foreach($this->recipients_bcc AS $recipient) {
                    if($recipient['name'] != '') {
                        $message->addBcc(array($recipient['email']=>$recipient['name']));
                    } else {
                        $message->addBcc($recipient['email']);
                    }
                }
            } catch (Swift_RfcComplianceException $e) {
                trigger_error('Email Address Format Error: '.$e->getMessage(),E_USER_WARNING);
                $this->error = 'Email Address Format Error: '.$e->getMessage();
            }

            // Catch failed sends
            $failed = array();

            // If we successfully send the email, log it to the database
            if($sent = $this->mailer->send($message, $failed)) {
                $this->logEmail();
            } elseif(count($failed) > 0) {
                trigger_error('Unable to send.  Mailer may not be set up properly.',E_USER_WARNING);
                $this->error = 'Unable to send.  Mailer may not be set up properly.';
                return 0;
            }
        } catch (Swift_TransportException $e) {
            trigger_error('Connection Problem: '.$e->getMessage(),E_USER_WARNING);
            $this->error = 'Connection Problem: '.$e->getMessage();
            return -1;
        }

        return $sent;
    }

    /**
    * Log the email in the email log
    */
    function logEmail() {
        $body_plain = '';
        $body_html = '';
        foreach($this->message_parts as $message_key=>$message_part) {
            if($message_part['mimetype'] == 'text/plain') {
                $body_plain = $message_part['message'];
            } elseif($message_part['mimetype'] == 'text/html') {
                $body_html = $message_part['message'];
            }
        }
        // Get the user ID by querying the user table for matching email addresses
        foreach($this->recipients_all AS $email) {
            if(!$user_id = $this->db->GetOne("SELECT id FROM ".T_USERS." WHERE user_email=?",array($email['email']))) {
                $user_id = 'NULL';
            }
            $log_id = $this->db->Execute("INSERT INTO ".T_EMAIL_LOG." (user_id,date,to_name,to_email,from_name,from_email,subject,body_plain,body_html) VALUES (?,NOW(),?,?,?,?,?,?,?)",array($user_id,$email['email'],$email['name'],$this->from_name,$this->from_email,$this->subject,$body_plain,$body_html));
        }
        return $log_id;
    }

    /**
    * Get the email log
    */
    function getLog() {
        if($this->error_logging) {
            return $this->logger->dump();
        } else {
            return false;
        }
    }
}
?>