<?php
class PayPal extends PaymentAPIHandler {
    var $gateway_name = 'PayPal';
    var $test_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    var $url = 'https://www.paypal.com/cgi-bin/webscr';

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
            'cmd'=>'_xclick',
            'business'=>$this->settings['paypal_email'],    // directory owners email address
            'return'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',     // returned here after payment
            'notify_url'=>BASE_URL.'/modules/processors/PayPal/PayPal_ipn.php',     // IPN notification url
            'cancel_return'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php',   // go here if the user cancels
            'custom'=>$data['invoice_id'],   // used to store the invoice ID we are paying
            'currency_code'=>$this->settings['paypal_currency'],    // currency code loaded from paypal settings
            'item_name'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],    // set item name by company name and invoice ID
            'quantity'=>'1',    // order only 1
            'amount'=>$data['balance'],       // amount of payment -- We need to change this to use 'balance' instead.
            'no_shipping'=>'1', // do not use shipping
            'rm'=>'2',          // sent back response as $_POST
            'address1'=>$data['user_address1'],      // address of payer
            'address2'=>$data['user_address2'],      // address of payer line 2
            'city'=>$data['user_city'],          // city of payer
            'email'=>$data['user_email'],         // email of payer
            'first_name'=>$data['user_first_name'],    // first name of payer
            'last_name'=>$data['user_last_name'],     // last name of payer
            'zip'=>$data['user_zip'],          // zip of payer
            'charset'=>CHARSET,
            'ButtonSource'=>'AccomplishTechnology_SP',
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
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function processNotification() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['payment_status']) {
                case 'Completed':
                    parent::processPayment($this->response['custom'],$this->response['txn_id'],$this->response['mc_gross']-$this->response['tax']);
                    break;
                case 'Refunded':
                case 'Reversed':
                    parent::processRefund($this->response['txn_id']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['custom'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(isset($this->response['tx'])) {
            if(!empty($this->settings['paypal_pdt_token'])) {
                $parameters = 'cmd=_notify-synch&tx='.urlencode($_GET['tx']).'&at='.urlencode($this->settings['paypal_pdt_token']);
                $http_request = $this->PMDR->get('HTTP_Request');
                $http_request->settings = array(
                    CURLOPT_HEADER=>0,
                    CURLOPT_RETURNTRANSFER=>1,
                    CURLOPT_POST=>1,
                    CURLOPT_POSTFIELDS=>$parameters,
                    CURLOPT_CAINFO => PMDROOT.'/includes/cacert.pem',
                    CURLOPT_HTTPHEADER=>array('Host: www.paypal.com'),
                    CURLOPT_SSL_VERIFYPEER=>1,
                    CURLOPT_SSL_VERIFYHOST=>2
                );
                $response = $http_request->get('curl',$this->url);
                $response_parts = explode("\n", preg_replace('/\r\n|\r/', "\n", $response));
                if($verified_response = array_shift($response_parts) != 'SUCCESS') {
                    if(!is_null($http_request->error_number)) {
                        $this->errors[] = $http_request->error_message;
                    }
                    $this->errors[] = 'Invalid response: '.$verified_response;
                    $this->errors[] = 'Sent Parameters: '.$parameters;
                    unset($verified_response);
                    return false;
                }
                unset($verified_response);
                foreach($response_parts as $part) {
                    $value = explode('=',$part);
                    if($value[0] != '') {
                        $this->response[$value[0]] = urldecode($value[1]);
                    }
                }
            } elseif($this->response['st'] != 'Completed') {
                return false;
            }
        } else {
            $parameters_ignore = array('cmd');
            $parameters = 'cmd=_notify-validate';
            foreach ($this->response as $key=>$value) {
                if(in_array($key,$parameters_ignore)) {
                    continue;
                }
                $parameters .= "&$key=".urlencode(stripslashes($value));
            }
            unset($parameters_ignore);
            $http_request = $this->PMDR->get('HTTP_Request');
            $http_request->settings = array(
                CURLOPT_HEADER=>0,
                CURLOPT_RETURNTRANSFER=>1,
                CURLOPT_POST=>1,
                CURLOPT_POSTFIELDS=>$parameters,
                CURLOPT_CAINFO => PMDROOT.'/includes/cacert.pem',
                CURLOPT_HTTPHEADER=>array('Host: www.paypal.com'),
                CURLOPT_SSL_VERIFYPEER=>1,
                CURLOPT_SSL_VERIFYHOST=>2
            );
            if(($verified_response = $http_request->get('curl',$this->url)) != 'VERIFIED') {
                if(!is_null($http_request->error_number)) {
                    $this->errors[] = $http_request->error_message;
                }
                $this->errors[] = 'Invalid response: '.$verified_response;
                $this->errors[] = 'Sent Parameters: '.$parameters;
                unset($verified_response);
                return false;
            }
            unset($verified_response);
        }
        if(!$this->loadStoredParameters($this->response['custom'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if($this->parameters['currency_code'] != $this->response['mc_currency']) {
            $this->errors[] = 'Invalid currency code: '.$this->parameters['currency_code'].' != '.$this->response['mc_currency'];
            return false;
        }
        if(strtolower($this->parameters['business']) != strtolower($this->response['business'])) {
            $this->errors[] = 'Invalid email: '.strtolower($this->parameters['business']).' != '.strtolower($this->response['business']);
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
            switch($this->response['payment_status']) {
                case 'Completed':
                    $this->result = 'success';
                    $this->result_amount = $this->response['mc_gross']-$this->response['tax'];
                    break;
                case 'Pending':
                    $this->result = 'pending';
                    break;
                case 'Failed':
                    $this->result = 'failed';
                    break;
                case 'Denied':
                    $this->result = 'declined';
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
        if(isset($_GET['tx'])) {
            if(!count($_GET)) {
                $this->errors[] = 'GET empty';
            }
            $this->response = $_GET;
        } else {
            if(!count($_POST)) {
                $this->errors[] = 'POST empty';
            }
            $this->response = $_POST;
        }
    }
}
?>