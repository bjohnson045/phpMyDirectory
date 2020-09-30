<?php
/**
* Pay.nl payment gateway class
*/
class Paynl extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'Paynl';
    /**
    * Test URL
    * @var string
    */
    var $test_url = '';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'http://www.pay.nl/betalen/';
    /**
    * API URL
    * @var string
    */
    var $api_url = 'https://api.pay.nl';
    
    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return Paynl
    */
    function __construct($PMDR) {
        parent::__construct($PMDR);
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return boolean
    */
    function loadParameters($data) {
        $session_variables = array(
            'mode'=>'SessionCreateSession',
            'resultType'=>'txt',
            'program_id'=>$this->settings['program_id'],
            'website_id'=>$this->settings['website_id'],
            'website_location_id'=>$this->settings['website_location_id'],
            'amount'=>$data['balance'],
            'object'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'extra1'=>$data['invoice_id']
        );
        
        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>http_build_query($session_variables)
        );
        $response = $http_request->get('curl',$this->api_url);
        parse_str($response,$response);
        $this->parameters = array(
            'payment_session_id'=>$response['payment_session_id']
        );
        if($this->test_mode) {
            $this->parameters['testMode'] = $this->settings['testmode'];
        }  
        return true;        
    }
    
    /**
    * Get Payment form
    * Loads the payment form from the payment API and then changes to GET method
    * @return object Form
    */
    function getPaymentButton() {
        $form = parent::getPaymentButton();
        $form->method = 'GET';
        return $form;
    }
    
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['extra1'];    
    }
    
    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['extra1'])) {
            $this->errors[] = 'Invalid invoice number (extra1)';
            return false;
        }
        return true;
    }
    
    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(isset($_GET['extra1']) AND is_numeric($_GET['extra1'])) {
                $this->result = 'success';
                $this->result_amount = $this->response['amount']; 
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        return $this->response = $_GET;
    }    
}

/**
* Pay.nl payment gateway notification class
*/
class Paynl_Notification extends Paynl {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['extra1'],$this->response['order_id'],$this->response['amount']);
        }
        return $this->response['extra1'];
    }
    
    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        return $this->response = $_POST;
    }
    
    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['action'] == 'add') {
                $this->result = 'success';
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }
    
    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        $ip_addresses = array(
            '85.158.206.17',
            '85.158.206.18',
            '85.158.206.19',
            '85.158.206.20',
            '85.158.206.21'
        );
        if(!in_array(get_ip_address(),$ip_addresses)) {
            $this->errors[] = 'Invalid IP address ('.get_ip_address().')';
            return false;
        }
        if(!$this->loadStoredParameters($this->response['extra1'])) {
            $this->errors[] = 'Invalid invoice number (extra1)';
            return false;
        }
        return true;
    }   
}
?>