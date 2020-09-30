<?php
class PSIGateXML extends PaymentAPIHandler {
    // https://dev.psigate.com
    // CID : 1000001
    // User: teststore
    // pass: testpass
    
    var $gateway_name = 'PSIGateXML';
    var $test_url = 'https://dev.psigate.com:7989/Messenger/XMLMessenger';
    var $url = 'https://secure.psigate.com:7934/Messenger/XMLMessenger';
    var $on_site_payment = true;
    
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
            'OrderID'=>time().'-'.$data['invoice_id'],
            'Subtotal'=>$data['invoice_subtotal'],
            'PaymentType'=>'CC',
            'CardAction'=>'0',
            'CardNumber'=>$data['cc_number'],
            'CardExpMonth'=>$data['cc_expire_month'],
            'CardExpYear'=>substr($data['cc_expire_year'],2),
            'CardIDNumber'=>$data['cc_cvv2'],
            'BName'=>$data['user_first_name'].' '.$data['user_last_name'], // Customer name
            'BCompany'=>$data['user_organization'],
            'Baddress1'=>$data['user_address1'],
            'Baddress2'=>$data['user_address2'],
            'Bcity'=>$data['user_city'],
            'Bprovince'=>$data['user_state'],
            'Bpostalcode'=>$data['user_zip'],
            'Bcountry'=>$data['user_country'],
            'Phone'=>$data['user_phone'],
            'Fax'=>$data['user_fax'],
            'Email'=>$data['user_email'],
            'Comments'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'Tax1'=>$data['invoice_tax'],
            'ShippingTotal'=>null,
            'CustomerIP'=>get_ip_address() // Used for fraud check
        );
        if($this->test_mode) { 
            $this->parameters['StoreID']='teststore';
            $this->parameters['Passphrase']='psigate1234';
        } else {
            $this->parameters['StoreID']=$this->settings['psigatexml_store_id'];
            $this->parameters['Passphrase']=$this->settings['psigatexml_passphrase'];
        }
    }
    
    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['Approved']) {
                case 'APPROVED':
                    parent::processPayment($this->response['OrderID'],$this->response['TransRefNumber'],$this->response['FullTotal']);
                    break;
                case 'DECLINED':
                    $this->result = 'declined';
                    break;
                case 'ERROR':
                    $this->result = 'failed';
                    break;
                default:
                    $this->result = 'failed';
                    return false;
            }
        }
        return $this->response['OrderID'];   
    }
    
    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['Approved']) {
                case 'APPROVED':
                    $this->result = 'success';
                    $this->result_amount = $this->response['FullTotal'];
                    break;
                case 'DECLINED':
                    $this->result = 'declined';
                    break;
                case 'ERROR':
                default:
                    $this->result = 'failed';
                    break;
            } 
            return $this->result;
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }
    
    function verifyResponse() {
        return true;
    }
    
    /**
    * Load response
    * Load the response into class variable for processing
    * @return mixed
    */
    function loadResponse() {
        $xml = '<Order>'.$this->arrayToXML($this->parameters).'</Order>';
 
        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_TIMEOUT, 4,
            CURLOPT_POSTFIELDS=>$xml
        );
        if($response = $http_request->get('curl',$this->url)) {
            $xmlobject = simplexml_load_string(trim($response));
            $this->response['OrderID'] = (string) array_pop(explode('-',$xmlobject->{'OrderID'}));
            $this->response['TransactionType'] = (string) $xmlobject->{'TransactionType'};
            $this->response['Approved'] = (string) $xmlobject->{'Approved'};
            $this->response['ReturnCode'] = (string) $xmlobject->{'ReturnCode'};
            $this->response['ErrMsg'] = (string) $xmlobject->{'ErrMsg'};
            $this->response['TransRefNumber'] = (string) $xmlobject->{'TransRefNumber'};
            $this->response['TransactionType'] = (string) $xmlobject->{'TransactionType'};
            $this->response['FullTotal'] = (string) $xmlobject->{'FullTotal'};
        } else {
            return false;
        }
    }
}
?>