<?php
/**
* eWay Hosted payment gateway class
* Test CC: 4444333322221111
*/
class eWayHosted extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'eWayHosted';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://www.eway.com.au/gateway/xmltest/testpage.asp';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.eway.com.au/gateway/xmlpayment.asp';
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;
    
    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return eWayHosted
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
        $this->parameters = array(
            'ewayCustomerID'=>$this->settings['eway_customer_id'],
            'ewayTotalAmount'=>str_replace('.','',number_format($data['balance'],2)), //eWay uses whole
            'ewayCustomerInvoiceDescription'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ewayCustomerInvoiceRef'=>$data['invoice_id'],
            'ewayCardHoldersName'=>$data['user_first_name'].' '.$data['user_first_name'],
            'ewayCardNumber'=>$data['cc_number'],
            'ewayCardExpiryMonth'=>$data['cc_expire_month'],
            'ewayCardExpiryYear'=>substr($data['cc_expire_year'],-2,2),
            'ewayTrxnNumber'=>$data['invoice_id'],
            'ewayCustomerLastName'=>$data['user_first_name'],
            'ewayCustomerFirstName'=>$data['user_first_name'],
            'ewayCustomerEmail'=>$data['user_email'],
            'ewayCustomerAddress'=>$data['user_address1'],
            'ewayCustomerPostcode'=>$data['user_zip'],
            'ewayOption1'=>'',
            'ewayOption2'=>'',
            'ewayOption3'=>''
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
        if($this->response['ewayReturnAmount'] != $this->parameters['ewayTotalAmount']) {
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
            if($this->response['ewayTrxnStatus'] == 'True') {
                $this->result = 'success';
                $this->result_amount = $this->response['ewayReturnAmount'];
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        $xml = '<ewaygateway>';
        foreach($this->parameters as $key=>$value) {
            $xml .= "<$key>$value</$key>";
        }
        $xml .= "</ewaygateway>";

        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_TIMEOUT, 4,
            CURLOPT_POSTFIELDS=>$xml,
            CURLOPT_SSL_VERIFYPEER=>0
        );
        if($response = $http_request->get('curl',$this->url)) {
            $xmlobject = simplexml_load_string(trim($response));
            $this->response['ewayTrxnStatus'] = (string) $xmlobject->{'ewayTrxnStatus'};
            $this->response['ewayTrxnNumber'] = (string) $xmlobject->{'ewayTrxnNumber'};
            $this->response['ewayTrxnReference'] = (string) $xmlobject->{'ewayTrxnReference'};
            $this->response['ewayAuthCode'] = (string) $xmlobject->{'ewayAuthCode'};
            $this->response['ewayReturnAmount'] = (string) $xmlobject->{'ewayReturnAmount'};
            $this->response['ewayTrxnError'] = (string) $xmlobject->{'ewayTrxnError'};
        } else {
            return false;
        }
    }    
}
?>