<?php
class Skrill extends PaymentAPIHandler {
    // http://www.skrill.com/app/help.pl?s=m_manual
    var $gateway_name = 'Skrill';
    var $test_url = 'http://www.skrill.com/app/test_payment.pl';
    var $url = 'https://www.skrill.com/app/payment.pl';

    function __construct($PMDR) {
        parent::__construct($PMDR);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'pay_to_email'=>$this->settings['skrill_email'],
            'recipient_description'=>$this->PMDR->getConfig('invoice_company'),
            'logo_url'=>'',
            'detail1_description'=>'Invoice #',
            'detail1_text'=>$data['invoice_id'],
            'transaction_id'=>$data['invoice_id'],
            'return_url'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'cancel_url'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php',
            'status_url'=>BASE_URL.'/modules/processors/Skrill/Skrill_ipn.php',
            'hide_login'=>'',
            'pay_from_email'=>$data['user_email'],
            'amount'=>$data['balance'],
            'currency'=>$this->settings['skrill_currency'],
            'firstname'=>$data['user_first_name'],
            'lastname'=>$data['user_last_name'],
            'address'=>$data['user_address1'],
            'address2'=>$data['user_address2'],
            'phone_number'=>$data['user_phone'],
            'postal_code'=>$data['user_zip'],
            'city'=>$data['user_city'],
            'state'=>$data['user_state'],
            'country'=>$data['user_country']
        );
        return true;
    }

    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['custom'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['transaction_id'])) {
            $this->errors[] = 'Invalid invoice number (transaction_id)';
            return false;
        }
        if($this->response['msid'] != strtoupper(md5($this->settings['skrill_merchant_id'].$this->response['transaction_id'].strtoupper(md5($this->settings['skrill_secret_word']))))) {
            $this->errors[] = 'Invalid MSID hash';
            return false;
        }
        return true;
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            // We have no way to tell the payment status here so we must assume pending.
            $this->result = 'pending';
            $this->result_message = "Thank you for your payment. Skrill should authorize and process your payment shortly. When they do they will send a notification and your invoice will be marked as paid.";
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function loadResponse() {
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;
    }
}

class Skrill_Notification extends Skrill {
    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['status']) {
                case 2:
                    parent::processPayment($this->response['transaction_id'],$this->response['mb_transaction_id'],$this->response['amount']);
                    break;
                case -3:
                    parent::processRefund($this->response['transaction_id']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['transaction_id'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['transaction_id'])) {
            $this->errors[] = 'Invalid invoice number (transaction_id)';
            return false;
        }
        if($this->response['md5sig'] != strtoupper(md5($this->response['merchant_id'].$this->response['transaction_id'].strtoupper(md5($this->settings['skrill_secret_word'])).
        $this->response['mb_amount'].$this->response['mb_currency'].$this->response['status']))) {
            $this->errors[] = 'Invalid hash';
            return false;
        }
        return true;
    }

    /*
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['status']) {
                case 2:
                    $this->result = 'success';
                    $this->result_amount = $this->response['mb_amount'];
                    break;
                case 0:
                    $this->result = 'pending';
                    break;
                case -2:
                case -1:
                    $this->result = 'failed';
                    break;
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}
?>