<?php
/**
* Class ErrorHandler
* Wrapper for PHP's error handler to allow custom errors and logging
*/
class ErrorHandler {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var string Log File Name
    */
    var $error_log_filename = '';
    /**
    * @var string Email Address to send log to
    */
    var $to_address;
    /**
    * @var string Mail buffer to hold string until emailing
    */
    var $mail_buffer;
    /**
    * @var boolean Log errors to a file
    */
    var $log_errors_to_file;
    /**
    * @var boolean Log warnings to a file
    */
    var $log_warnings_to_file;
    /**
    * @var boolean Log notices to a file
    */
    var $log_notices_to_file;
    /**
    * @var boolean Send errors to email
    */
    var $send_errors_to_mail;
    /**
    * @var boolean Send warnings to email
    */
    var $send_warnings_to_mail;
    /**
    * @var boolean Send notices to email
    */
    var $send_notices_to_mail;
    /**
    * Value used to detect uncaught exceptions
    */
    const E_UNCAUGHT_EXCEPTION = 3;

    /**
    * ErrorHandler Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $PMDR->get('db');
        // Set up the default logging options (file, database, email)
        $this->setFlags();
        // Log everything to the error_log file in the root
        $this->error_log_filename = PMDROOT.'/error_log';
        // All errors are routed through the error method
        set_error_handler(array($this,'error'));
        // Uncaught exceptions are handled through the exception method
        set_exception_handler(array($this,'exception'));
        // Catch fatal errors by using a shutdown function
        register_shutdown_function(array($this,'errorHandlerDestruct'));
    }

    /**
    * Set filename for error logs to be stored in
    * @param string $filename File name
    */
    function setFilename($filename) {
        $this->error_log_filename = $filename;
    }

    /**
    * Set flags for error logging
    * @param boolean $error_flag Log errors to file
    * @param boolean $warning_flag Log warnings to file
    * @param boolean $notice_flag Log notices to file
    * @param boolean $error_mailflag Mail errors
    * @param boolean $warning_mailflag Mail warnings
    * @param boolean $notice_mailflag Mail notices
    * @return void
    */
    function setFlags($error_flag = true, $warning_flag = true, $notice_flag = false, $error_mailflag = false, $warning_mailflag = false, $notice_mailflag = false, $error_database_flag = true, $warning_database_flag = true, $notice_database_flag = false) {
        $this->log_errors_to_file = $error_flag;
        $this->log_warnings_to_file = $warning_flag;
        $this->log_notices_to_file = $notice_flag;
        $this->send_errors_to_mail = $error_mailflag;
        $this->send_warnings_to_mail = $warning_mailflag;
        $this->send_notices_to_mail = $notice_mailflag;
        $this->log_errors_to_database = $error_database_flag;
        $this->log_warnings_to_database = $warning_database_flag;
        $this->log_notices_to_database = $notice_database_flag;
    }

    /**
    * Process an error
    * @param int $code
    * @param string $message
    * @param string $file
    * @param int $line
    */
    function error($code, $message, $file = NULL, $line = NULL, $context = NULL, $trace = NULL, $exception = false) {
        // If error reporting is turned off or a bad code is received return false
        if(error_reporting() == 0 OR !$code) {
            return false;
        }

        // Check if this is a notice, if so we don't want to throw an exception because it will halt execution
        if($code == E_NOTICE OR $code == E_USER_NOTICE OR $code == E_STRICT) {
            if($this->log_notices_to_file) {
                $this->logToFile($this->formatError($code,$message,$file,$line));
            }
            if($this->log_notices_to_database) {
                $this->logToDatabase($code,$message,$file,$line);
            }
            if($this->send_notices_to_mail) {
                $this->mailError($this->formatError($code,$message,$file,$line));
            }
        // Check if this is a warning, if so we don't want to throw an exception because it will halt execution
        } elseif($code == E_WARNING OR $code == E_USER_WARNING OR $code == @E_DEPRECATED OR $code == @E_USER_DEPRECATED OR $code == @E_RECOVERABLE_ERROR) {
            // error() may receive a trace if triggered by an exception
            if(is_null($trace)) {
                $trace = debug_backtrace(false);
            }
            if($this->log_warnings_to_file) {
                $this->logToFile($this->formatError($code,$message,$file,$line));
            }
            if($this->log_warnings_to_database) {
                $this->logToDatabase($code,$message,$file,$line,$trace);
            }
            if($this->send_warnings_to_mail) {
                $this->mailError($this->formatError($code,$message,$file,$line),$trace);
            }
        // We catch fatal errors here because we can't catch this in the destructor function as exceptions can't be thrown there
        // Catch all other errors and treat them as fatal
        // $code == E_ERROR OR $code == E_USER_ERROR OR $code == self::E_UNCAUGHT_EXCEPTION
        } else {
            // error() may receive a trace if triggered by an exception
            if(is_null($trace)) {
                $trace = debug_backtrace(false);
            }
            if($this->log_errors_to_file) {
                $this->logToFile($this->formatError($code,$message,$file,$line));
            }
            if($this->log_errors_to_database) {
                $this->logToDatabase($code,$message,$file,$line,$trace);
            }
            if($this->send_errors_to_mail) {
                $this->mailError($this->formatError($code,$message,$file,$line),$trace);
            }
            // If in the admin or in debug mode just output the error
            if(PMD_SECTION == 'admin' OR DEBUG_MODE) {
                // The fatal error (code == 1) will already be output so don't duplicate it
                if($code == E_USER_ERROR OR $exception) {
                    echo $this->formatError($code,$message,$file,$line,true).'<br />';
                }
                exit();
            } elseif(headers_sent()) {
                include(PMDROOT.'/error.html');
                exit();
            } else {
                redirect(BASE_URL.'/error.html');
            }
        }
        // Do not return to normal PHP error handling
        return true;
    }

    /**
    * Handle all errors replacing PHP error handler
    * @param string $error_type Type of error occuring
    * @param string $error_msg Error message
    * @param string $error_file File to log errors to.
    * @param string $error_line Line in file error occured on
    * @param string $error_context Where error occured
    * @return void
    */
    function exception($exception) {
        // If error has been supressed with an @ we exit
        if(error_reporting() == 0) {
            return false;
        }
        $code = $exception->getCode();
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTrace();
        // If $code = 0 this is an uncaught exception, set it to our custom error number so it can be identified
        if(!$code) {
            $code = self::E_UNCAUGHT_EXCEPTION;
        }
        // Convert the exception over using the correlated error data and set the "exception" flag to true
        // which allows an exception error to be identified and managed as a special case
        $this->error($code,$message,$file,$line,null,$trace,true);
    }

    /**
    * Log the error to file
    * @param string $message
    * @return boolean
    */
    function logToFile($message) {
        if(!function_exists('error_log')) {
            return false;
        }

        if(!file_exists($this->error_log_filename))  {
            if($error_log_file = @fopen($this->error_log_filename, 'w')) {
                fclose($error_log_file);
            }
        }

        if(file_exists($this->error_log_filename) AND is_writable($this->error_log_filename))  {
            return error_log('['.date('m-d-Y h:i:sa').'] '.$message.chr(10), 3, $this->error_log_filename);
        } else {
            return error_log('['.date('m-d-Y h:i:sa').'] '.$message.chr(10),0);
        }
    }

    /**
    * Log the error to the database
    * @param int $code
    * @param string $message
    * @param string $file
    * @param int $line
    */
    function logToDatabase($code,$message,$file,$line,$trace = '') {
        $trace = serialize($this->formatTrace($trace));
        $message = strip_tags(preg_replace('/<br\s*\/?>/i',"\n",$message));
        try {
            $this->db->Execute("INSERT INTO ".T_ERROR_LOG." (date,code,message,file,line,trace) VALUES (NOW(),?,?,?,?,?)",array($code,$message,$file,$line,$trace));
        } catch (Exception $e) {
            return false;
        }
    }

    /**
    * Format the error message
    * @param int $code
    * @param string $message
    * @param string $file
    * @param int $line
    */
    function formatError($code,$message,$file,$line,$pretty = false) {
        switch($code) {
            case E_USER_ERROR:
            case E_ERROR:
                $type = 'Fatal Error';
                break;
            case E_USER_WARNING:
            case E_WARNING:
                $type = 'Warning';
                break;
            case E_USER_NOTICE:
            case E_NOTICE:
            case @E_STRICT:
                $type = 'Notice';
                break;
            case @E_RECOVERABLE_ERROR:
                $type = 'Catchable';
                break;
            case self::E_UNCAUGHT_EXCEPTION:
                $type = 'Fatal Error (Uncaught Exception)';
                break;
            default:
                $type = 'Unknown Error ('.$code.')';
                break;
        }
        if(!$pretty) {
            return $type.': '.$message.' in '.$file.' on line '.$line;
        } else {
            return '<b>'.$type.':</b> '.$message.' in <b>'.$file.'</b> on line <b>'.$line.'</b>';
        }

    }

    /**
    * Check if an argument in a trace is a closure
    * @param mixed $argument
    * @return boolean
    */
    function isClosure($argument) {
        return is_callable($argument);
    }

    /**
    * Format a trace array
    * @param mixed $trace
    */
    function formatTrace($trace) {
        if(is_array($trace)) {
            $trace = array_slice($trace,1);

            foreach($trace AS $trace_key=>$trace_part) {
                unset($trace[$trace_key]['object']);
                if(is_object($trace_part)) {
                    unset($trace[$trace_key]);
                } elseif(isset($trace_part['args']) AND is_array($trace_part['args']) AND count($trace_part['args'])) {
                    foreach($trace_part['args'] AS $key=>$argument) {
                        if($this->isClosure($argument) or is_object($argument)) {
                            unset($trace[$trace_key]['args'][$key]);
                        }
                    }
                }
            }
        }
        return $trace;
    }

    /**
    * Set mail recipient for the errors
    * @param string $address Address to send errors to
    * @return void
    */
    function setMailRecipient($address) {
        $this->to_address = $address;
    }

    /**
    * Mail the errors
    * @param string $mail_body Text included in the email
    */
    function mailError($message, $trace = '') {
        $mailer = $this->PMDR->get('Email_Handler');
        $mailer->from_email = $this->PMDR->getConfig('admin_email');
        $mailer->from_name = 'Directory Error Handler';
        $mailer->subject = 'Error Report';
        if(!empty($trace)) {
            $message .= "\n\n".var_export($this->formatTrace($trace),true);
        }
        $mailer->addMessagePart($message);
        $mailer->addRecipient($this->to_address);
        $mailer->send();
    }

    /**
    * Restore the error handler
    * @return void
    */
    function restoreHandler() {
        restore_error_handler();
    }

    /**
    * Show back trace
    * @return void
    */
    function showBacktrace() {
        debug_print_backtrace();
    }

    /**
    * ErrorHandler Desctructor
    * @return void
    */
    function errorHandlerDestruct() {
        if($error = error_get_last()) {
            if($error['type'] == 1) {
                $this->error($error['type'],$error['message'],$error['file'],$error['line']);
            }
        }
    }
}
?>