<?php
/**
* Class PaymentAPIHandler
* Base class for payment gateway handling
*/
class PaymentAPIHandler {
    /**
    * @var Registry
    */
    var $PMDR;
    /**
    * @var Database
    */
    var $db;
    /**
    * The gateway handles payments on site
    * @var boolean
    */
    var $on_site_payment = false;
    /**
    * The incoming payment parameters
    * @var array
    */
    var $parameters = array();
    /**
    * Turn on/off test mode
    * @var boolean
    */
    var $test_mode = false;
    /**
    * Gateway response
    * @var array
    */
    var $response = array();
    /**
    * Gateway settings
    * @var array
    */
    var $settings = array();
    /**
    * Gateway errors
    * @var array
    */
    var $errors = array();
    /**
    * Gateway result
    * @var string
    */
    var $result;
    /**
    * The amount processed
    * @var mixed Float or int amount
    */
    var $result_amount;
    /**
    * The full descriptive result message
    * @var string
    */
    var $result_message;

    /**
    * PaymentAPIHander Constructor
    * @param object $PMDR Registry
    * @return void
    */
    function __construct($PMDR) {
        $this->PMDR = $PMDR;
        $this->db = $this->PMDR->get('DB');
        $this->loadSettings();
        $this->PMDR->loadLanguage(array('email_templates'));
    }

    /**
    * Load gateway settings from database
    * @return void
    */
    function loadSettings() {
        $gateway = $this->db->GetRow("SELECT id, display_name, settings FROM ".T_GATEWAYS." WHERE id=?",array($this->gateway_name));
        $settings = unserialize($gateway['settings']);
        unset($gateway['settings']);
        if(is_array($settings)) {
            if($settings['testmode']) $this->enableTestMode();
            $this->settings = array_merge($gateway,$settings);
        }
    }

    /**
    * Load stored parameters from an invoice
    * @param int $invoice_id
    * @return false|void
    */
    function loadStoredParameters($invoice_id) {
        $data = $this->db->GetRow("SELECT u.*, i.id AS invoice_id, i.total, i.total-IFNULL(SUM(t.amount),0.00) AS balance FROM ".T_USERS." u INNER JOIN ".T_INVOICES." i ON u.id = i.user_id LEFT JOIN ".T_TRANSACTIONS." t ON i.id=t.invoice_id WHERE i.id=? GROUP BY i.id",array($invoice_id));
        return (!$data) ? false : $this->loadParameters($data);
    }

    /**
    * Generic process payment function
    * This is ran after a specific gateway has processed the payment.  From here, we know the invoice ID and have the response
    * so we can process the order in a uniform fashion
    * @return void
    */
    function processPayment($invoice_id, $transaction_id, $amount, $description = '') {
        if(!$this->transactionExists($transaction_id)) {
            $this->PMDR->get('Invoices')->insertTransaction($invoice_id,$transaction_id,$amount,date('Y-m-d'),$description,'',$this->settings['id']);
            $this->PMDR->get('Email_Templates')->send('invoice_payment',array('to'=>$user['user_email'],'invoice_id'=>$invoice_id));
            $this->PMDR->get('Email_Templates')->send('admin_invoice_payment',array('invoice_id'=>$invoice_id));
        }
        return $invoice_id;
    }

    /**
    * Process a subscription payment
    * @param int $order_id Order ID
    * @param string $transaction_id Subcription ID
    * @param float $amount Amount to process
    * @param strong $description Payment description
    * @return boolean
    */
    function processSubscriptionPayment($order_id, $transaction_id, $amount, $description = '') {
        $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.subscription_id=? ORDER BY i.date_due ASC",array($this->response['recurring_payment_id']));
        return $this->processPayment($invoice_id,$transaction_id,$amount,$description);
    }

    /**
    * Process a refund
    * @param string $transaction_id Transaction ID to refund
    * @return bool
    */
    function processRefund($transaction_id) {
        if($transaction = $this->db->GetRow("SELECT invoice_id, amount FROM ".T_TRANSACTIONS." WHERE transaction_id=?",array($transaction_id))) {
            $invoice = $this->PMDR->get('Invoices')->get($transaction['invoice_id']);
            if($invoice['balance'] - $transaction['amount'] < 0) {
                $invoice_update['status'] = 'paid';
                $this->PMDR->get('Invoices')->update($invoice_update,$transaction['invoice_id']);
            }
            $this->db->Execute("DELETE t FROM ".T_TRANSACTIONS." t WHERE t.transaction_id=? AND t.gateway_id=?",array($transaction_id,$this->gateway_name));
        }
        return true;
    }

    /**
    * Get an order ID by subscription ID
    * @param string $subscription_id Subscription ID
    * @return int Order ID
    */
    function getSubscriptionOrderID($subscription_id) {
        return $this->db->GetOne("SELECT id FROM ".T_ORDERS." WHERE subscription_id=?",array($subscription_id));
    }

    /**
    * Setup a subscription by assigning the subscription ID to an order
    * @param int $order_id Order ID
    * @param string $subscription_id Subscription ID
    */
    function setupSubscription($order_id, $subscription_id) {
        $this->db->Execute("UPDATE ".T_ORDERS." SET subscription_id=? WHERE id=?",array($subscription_id,$order_id));
    }

    /**
    * Cancel a subscription
    * @param int $order_id Order ID
    */
    function cancelSubscription($order_id) {
        $this->db->Execute("UPDATE ".T_ORDERS." SET subscription_id='' WHERE id=?",array($order_id));
    }

    /**
    * Add the payment response to the database log
    * @return void
    */
    function addToLog() {
        // If response was sent back or prepared as an array, we set it up formmated for the gateway log, else we just insert the string (usually XML)
        if(!empty($this->response)) {
            $data = $this->getDebugInformation();
            $this->db->Execute("INSERT INTO ".T_GATEWAYS_LOG." (date,gateway,data,result,errors) VALUES (NOW(),?,?,?,?)",array($this->gateway_name,$data,$this->result,serialize($this->errors)));
        }
    }

    /**
    * Turn on test mode
    * Sets the main url to the test url if the gateway has a test url available
    * @return void
    */
    function enableTestMode() {
        $this->test_mode = true;
        if($this->test_url != '') {
            $this->url = $this->test_url;
        }
    }

    /**
    * Check if a transaction exists
    * @param string $transactionID
    * @return boolean
    */
    function transactionExists($transactionID) {
        return $this->db->GetOne("SELECT COUNT(*) AS count FROM ".T_TRANSACTIONS." WHERE transaction_id=?",array($transactionID));
    }

    /**
    * Generate the payment form or button depending on the class setting
    * @return object Form
    */
    function getPaymentForm($data = array()) {
        if(file_exists(PMDROOT.'/modules/processors/'.$this->gateway_name.'/payment_form.tpl')) {
            $this->payment_template = $this->PMDR->getNew('Template',PMDROOT.'/modules/processors/'.$this->gateway_name.'/payment_form.tpl');
        } elseif($this->on_site_payment) {
            $this->payment_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/payment_form_credit_card.tpl');
        } else {
            $this->payment_template = $this->PMDR->getNew('Template',PMDROOT.TEMPLATE_PATH.'members/blocks/payment_form.tpl');
        }
        if($this->on_site_payment) {
            $this->payment_template->set('form',$this->getCreditCardForm($data));
        } else {
            $this->payment_template->set('form',$this->getPaymentButton());
        }
        return $this->payment_template;
    }

    /**
    * Generate the payment button from the parameters
    * @return object Form
    */
    function getPaymentButton() {
        $form = $this->PMDR->getNew('Form');
        $form->setName('payment_button');
        $form->action = $this->url;
        $form->addFieldSet('hidden');
        foreach($this->parameters as $name=>$value) {
            $form->addField($name,'hidden',array('label'=>$name,'fieldset'=>'hidden','value'=>$value));
        }
        $form->addField('submit','submit',array('label'=>$this->PMDR->getLanguage('user_general_pay_now'),'fieldset'=>'submit'));
        return $form;
    }

    /**
    * Generate credit card payment form
    * @return object Form
    */
    function getCreditCardForm($data) {
        $form = $this->PMDR->getNew('Form');
        $form->action = BASE_URL.MEMBERS_FOLDER.'user_payment_return.php';
        $form->addFieldSet('credit_card',array('legend'=>'Credit Card Details'));
        if(isset($this->settings['credit_card_types']) AND count($this->settings['credit_card_types'])) {
            $credit_card_types = array_combine($this->settings['credit_card_types'],$this->settings['credit_card_types']);
        } else {
            $credit_card_types = array('Visa'=>'Visa','MasterCard'=>'MasterCard','Amex'=>'Amex','Discover'=>'Discover');
        }
        $form->addField('cc_type','select',array('label'=>'Credit Card Type','fieldset'=>'credit_card','options'=>$credit_card_types));
        $form->addField('cc_number','text',array('label'=>'Credit Card Number','fieldset'=>'credit_card'));
        $months = array('01','02','03','04','05','06','07','08','09','10','11','12');
        $form->addField('cc_expire_month','select',array('label'=>'Expiration Month','fieldset'=>'credit_card','options'=>array_combine($months,$months)));
        $form->addField('cc_expire_year','select',array('label'=>'Expiration Year','fieldset'=>'credit_card','options'=>array_combine(range(date('Y'),date('Y')+10),range(date('Y'),date('Y')+10))));
        $form->addField('cc_cvv2','text',array('label'=>'CVV2 Card Code','fieldset'=>'credit_card'));
        $form->addFieldSet('hidden');
        foreach($data as $name=>$value) {
            $form->addField($name,'hidden',array('label'=>$name,'fieldset'=>'hidden','value'=>$value));
        }
        $form->addField('submit_form','submit',array('label'=>'Continue','fieldset'=>'submit'));
        return $form;
    }

    /**
    * Convert array to XML format
    * @param array $array
    * @return string
    */
    function arrayToXML($array) {
        $xml = '';
        foreach($array as $key=>$value) {
            $xml .= '<'.$key.'>'.htmlentities($value).'</'.$key.'>';
        }
        return $xml;
    }
    
    /**
    * Get the current debug information including response details and any errors.
    * @return string
    */
    function getDebugInformation() {
        $text = print_r($this->response,true);
        if(count($this->errors)) {
            $text .= "\nErrors:".implode("\n",$this->errors);;
        }
        return $text;
    }

    /**
    * Send a payment debug email for easier debugging
    * @param string $email Email to send debug email to
    * @return void
    */
    function sendDebugEmail($email) {
        $mailer = $this->PMDR->get('Email_Handler');
        $mailer->flush();
        $mailer->addRecipient($email);
        $mailer->from_email = $this->PMDR->getConfig('admin_email');
        $mailer->from_name = 'Payment Notification Debugger';
        $mailer->subject = 'Payment Notification Debugger';
        $text = "Payment Notification Debugger\n\nThis is an automated email being sent because a payment notification was received from a payment gateway in test mode.\n\nBelow you will find the variables received from the payment notification which can be used for debugging purposes.\n\n";
        $text .= $this->getDebugInformation();
        $mailer->addMessagePart($text);
        $mailer->send();
    }
}
?>