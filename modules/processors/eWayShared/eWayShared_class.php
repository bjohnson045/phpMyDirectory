<?php
/**
* eWay Shared payment gateway class
* Test CC: 4444333322221111
* Test ID: 87654321
* Test Amount: 1000
*/
class eWayShared extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'eWayShared';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://www.eway.com.au/gateway/payment.asp';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.eway.com.au/gateway/payment.asp';
    
    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return eWayShared
    */
    function __construct($PMDR) {
        parent::__construct($PMDR);
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return mixed
    */
    function loadParameters($data) {
        $this->parameters = array(
            'ewayCustomerID'=>$this->settings['eway_customer_id'],
            'ewayTotalAmount'=>str_replace('.','',number_format($data['balance'],2)), // eWay uses whole
            'ewayCustomerInvoiceDescription'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ewayCustomerInvoiceRef'=>$data['invoice_id'],
            'ewayTrxnNumber'=>$data['invoice_id'],
            'ewayURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'ewayAutoRedirect'=>1, // auto redirect to url
            'ewaySiteTitle'=>'', // site title
            'ewayCustomerLastName'=>$data['user_first_name'],
            'ewayCustomerFirstName'=>$data['user_first_name'],
            'ewayCustomerEmail'=>$data['user_email'],
            'ewayCustomerAddress'=>$data['user_address1'],
            'ewayCustomerPostcode'=>$data['user_zip'],
            'ewaySiteTitle'=>$this->PMDR->getConfig('title'),
            
        );
        if($this->settings['testmode']) {
            if(substr($data['balance'],-2,2) != '00') {
                $this->parameters['ewayTotalAmount'] = '1000';
            }
            $this->parameters['ewayCustomerID'] = '87654321';
        }
        
        foreach($this->parameters as $key=>$value) {
            $this->parameters[$key] = htmlentities($value);
        }
        
        return true;        
    }
    
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        // ewayTrxnNumber gets passed back as ewayTrxnReference
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['ewayTrxnReference'],$this->response['ewayTrxnNumber'],substr($this->response['eWAYReturnAmount'],1));
        }
        return $this->response['ewayTrxnReference'];    
    }
    
    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['ewayTrxnReference'])) {
            $this->errors[] = 'Invalid invoice number (ewayTrxnReference)';
            return false;
        }
        if(str_replace('.','',substr($this->response['eWAYReturnAmount'],1)) != $this->parameters['ewayTotalAmount']) {
            $this->errors[] = 'Invalid amount';
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
            if($this->response['ewayTrxnStatus'] == 'True' AND $this->response['eWAYresponseCode'] == '00') {
                $this->result = 'success';
                $this->result_amount = $this->response['eWAYReturnAmount'];
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
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        return $this->response = $_POST;
    }    
}
?>