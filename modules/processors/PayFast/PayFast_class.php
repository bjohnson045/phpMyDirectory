<?php
class PayFast extends PaymentAPIHandler {
    var $gateway_name = 'PayFast';
    var $test_url = 'https://sandbox.payfast.co.za/eng/process';
    var $url = 'https://www.payfast.co.za/eng/process';

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
            'merchant_id'=>$this->settings['payfast_merchant_id'],
            'merchant_key'=>$this->settings['payfast_merchant_key'],
            'return_url'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'cancel_url'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php',
            'notify_url'=>BASE_URL.'/modules/processors/PayFast/PayFast_ipn.php',
            'name_first'=>$data['user_first_name'],
            'name_last'=>$data['user_last_name'],
            'email_address'=>$data['user_email'],
            'm_payment_id'=>$data['invoice_id'],
            'amount'=>$data['balance'],
            'item_name'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
        );

        $this->parameters['signature'] = md5(http_build_query($this->parameters));
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
        return $this->parameters['m_payment_id'];
    }

    function loadResult() {
        $this->result = 'pending';
        return $this->result;
    }

    function loadResponse() {
        // Payfast does not give an immediate response
        return true;
    }

}

class PayFast_Notification extends PayFast {
    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['m_payment_id'],$this->response['pf_payment_id'],$this->response['amount_gross']);
        }
        return $this->response['m_payment_id'];
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
        foreach($_POST AS $key=>$value) {
            $this->response[$key] = stripslashes($value);
        }
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['payment_status']) {
                case 'COMPLETE':
                    $this->result = 'success';
                    $this->result_amount = $this->response['amount_gross'];
                    break;
                default:
                    $this->result = 'failed';
                    break;
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        $param_string = '';
        foreach($this->response AS $key=>$value) {
            if($key != 'signature') {
                $param_string .= $key.'='.urlencode($value).'&';
            }
        }
        $param_string = substr($param_string,0,-1);
        $signature = md5($param_string);
        if($signature!=$this->response['signature']) {
            $this->errors[] = 'Invalid signature';
            return false;
        }
        $validHosts = array(
            'www.payfast.co.za',
            'sandbox.payfast.co.za',
            'w1w.payfast.co.za',
            'w2w.payfast.co.za',
        );
        $validIps = array();
        foreach($validHosts as $pfHostname) {
            $ips = gethostbynamel($pfHostname);
            if($ips !== false) {
                $validIps = array_merge($validIps,$ips);
            }
        }
        $validIps = array_unique($validIps);
        if(!in_array($_SERVER['REMOTE_ADDR'],$validIps)) {
            $this->errors[] = 'Invalid IP';
            return false;
        }
        if(!$this->loadStoredParameters($this->response['m_payment_id'])) {
            $this->errors[] = 'Invalid invoice number (m_payment_id)';
            return false;
        }
        if(abs(floatval($this->parameters['amount']) - floatval($this->response['amount_gross'])) > 0.01) {
            $this->errors[] = 'Amount mismatch';
            return false;
        }
        if(strtolower($this->parameters['merchant_id']) != strtolower($this->response['merchant_id'])) {
            $this->errors[] = 'Invalid merchant ID: '.strtolower($this->parameters['merchant_id']).' != '.strtolower($this->response['merchant_id']);
            return false;
        }
        return true;
    }

}
?>