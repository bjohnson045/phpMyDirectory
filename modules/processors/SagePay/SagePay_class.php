<?php
class SagePay extends PaymentAPIHandler {
    var $gateway_name = 'SagePay';
    var $test_url = 'https://test.sagepay.com/gateway/service/vspform-register.vsp';
    var $url = 'https://live.sagepay.com/gateway/service/vspform-register.vsp';
    
    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->settings['simulator'] AND $this->settings['testmode']) {
            $this->url = 'https://test.sagepay.com/Simulator/VSPFormGateway.asp';
        }
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        include(PMDROOT.'/includes/country_codes.php');
        $country_codes_inverse = array_flip($country_codes);
        if(isset($country_codes[$data['user_country']])) {
            $country = $data['user_country'];   
        } elseif(isset($country_codes_inverse[$data['user_country']])) {
            $country = $country_codes_inverse[$data['user_country']];    
        } else {
            $country = '';
        }
        $this->parameters = array(
            'VPSProtocol'=>'2.23',
            'TxType'=>'PAYMENT',
            'Vendor'=>$this->settings['sagepay_login'],
            'VendorTxCode'=>$data['invoice_id'], // Unique order number from us (invoice ID)
            'Amount'=>$data['balance'],
            'Currency'=>$this->settings['sagepay_currency'],
            'Description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'SuccessURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'FailureURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'CustomerName'=>$data['user_first_name'].' '.$data['user_last_name'],
            'CustomerEMail'=>$data['user_email'],
            'VendorEMail'=>$this->PMDR->getConfig('admin_email'),  // transaction notification email
            'BillingSurname'=>$data['user_last_name'],
            'BillingFirstnames'=>$data['user_first_name'],
            'BillingAddress1'=>$data['user_address1'],
            'BillingPostCode'=>$data['user_zip'],
            'BillingCity'=>$data['user_city'],
            'BillingCountry'=>$country,
            'DeliverySurname'=>$data['user_last_name'],
            'DeliveryFirstnames'=>$data['user_first_name'],
            'DeliveryAddress1'=>$data['user_address1'],
            'DeliveryCity'=>$data['user_city'],
            'DeliveryPostCode'=>$data['user_zip'],
            'DeliveryCountry'=>$country,
            'ContactNumber'=>$data['user_phone'],
            'Basket'=>'1:'.$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'].':1:'.$data['subtotal'].':'.$data['tax'].':'.$data['balance'].':'.$data['balance'],
            'ApplyAVSCV2'=>'0',
            'Apply3DSecure'=>'0'        
        );
        $crypt_string = '';
        foreach($this->parameters as $key=>$value) {
            $crypt_string .= $key.'='.$value.'&';
        }
        $this->parameters['Crypt'] = base64_encode($this->SimpleXor(rtrim($crypt_string,'&'),$this->settings['sagepay_encryption_password']));
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
            switch($this->response['Status']) {
                case 'OK':
                    parent::processPayment($this->response['VendorTxCode'],$this->response['VPSTxId'],$this->response['Amount']);
                    break;
                case 'REJECTED':
                case 'NOTAUTHED':
                case '3DAUTH':
                    break;
                case 'MALFORMED':
                case 'INVALID':
                case 'ERROR': 
                default:
                    break;
            }
        }
        return $this->response['VendorTxCode'];    
    }
    
    function verifyResponse() {
        if(array_key_exists('Status',$this->response)) {
            return true;
        } else {
            $this->errors[] = 'Status not found';
            return false;
        }
    }
    
    /**
    * Perform XOR on string using key
    * Provided by SagePay documentation for "crypt" field
    */
    function simpleXor($InString, $Key) {
        $KeyList = array();
        $output = "";
        for($i = 0; $i < strlen($Key); $i++){
            $KeyList[$i] = ord(substr($Key, $i, 1));
        }

        for($i = 0; $i < strlen($InString); $i++) {
            $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
        }
        return $output;
    }
    
    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['Status']) {
                case 'OK':
                    $this->result = 'success';
                    $this->result_amount = $this->response['Amount'];
                    break;
                case 'REJECTED':
                case 'NOTAUTHED':
                case '3DAUTH':
                    $this->result = 'declined';
                    break;
                case 'MALFORMED':
                case 'INVALID':
                case 'ERROR': 
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
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function loadResponse() {
        // $_GET['crypt'] is sent back from Sagepay,  so we must reverse encryption. decode, xor using encryption password
        // Once decrypted we have string like name=XXXX&transactionid=XXXX and so on, so we explode and rip it into an array        
        $parts = explode('&',$this->simpleXor(base64_decode(str_replace(' ','+',$_GET['crypt'])),$this->settings['sagepay_encryption_password']));
        $response = array();
        foreach($parts as $value) {
            $value = explode('=',$value);
            $response[$value[0]] = $value[1];
        }   
        $this->response = $response; 
    }
}
?>