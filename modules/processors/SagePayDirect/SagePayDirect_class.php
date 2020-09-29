<?php
class SagePayDirect extends PaymentAPIHandler {
    // Test Visa CC: 4929000000006
    // CVV: 123
    // Address: 88
    // Postcode: 412
    
    var $gateway_name = 'SagePayDirect';
    var $test_url = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
    var $url = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
    
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;
    
    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->settings['simulator'] AND $this->settings['testmode']) {
            $this->url = 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp';
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
            'CardHolder'=>$data['user_first_name'].' '.$data['user_last_name'],
            'CardNumber'=>$data['cc_number'],
            'ExpiryDate'=>$data['cc_expire_month'].substr($data['cc_expire_year'],2),
            'CV2'=>$data['cc_cvv2'],
            'CardType'=>$data['cc_type'],
            'Apply3DSecure'=>2, // Do not for 3D secure
            'CustomerEMail'=>$data['user_email'],
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
            'Basket'=>'1:'.$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'].':1:'.$data['subtotal'].':'.$data['tax'].':'.$data['balance'].':'.$data['balance'],
            'ApplyAVSCV2'=>'0',        
        );
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
                    parent::processPayment($this->parameters['VendorTxCode'],$this->response['VPSTxId'],$this->parameters['Amount']);
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
    
    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        if(array_key_exists('Status',$this->response)) {
            return true;
        } else {
            $this->errors[] = 'Status not found';
            return false;
        }
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
                    $this->result_amount = $this->parameters['Amount'];
                    break;
                case 'REJECTED':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'NOTAUTHED':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case '3DAUTH':
                    $this->result = 'declined';
                    break;
                case 'MALFORMED':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'PPREDIRECT':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'AUTHENTICATED':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'REGISTERED':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'INVALID':
                    $this->errors[] = $this->response['StatusDetail'];
                    break;
                case 'ERROR':
                    $this->errors[] = $this->response['StatusDetail'];
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
    * Load response
    * Load the response into class variable for processing
    * @return void
    */
    function loadResponse() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$this->url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->parameters));

        $response = curl_exec($ch);
        if(!$response) {
            $this->errors[] = curl_error($ch).' ('.curl_errno($ch).')';
        } else {
            $parts = explode("\n",$response);
            foreach($parts as $part) {
                $value = explode('=',$part);
                $this->response[$value[0]] = trim(urldecode($value[1]));
            }
        }    
        curl_close($ch);
        return $this->response;
    }
}
?>