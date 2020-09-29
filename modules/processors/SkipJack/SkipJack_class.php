<?php
/**
* Skipjack payment gateway class
* 4445999922225 or 4003000123456781 Test Card, Visa
* CVV 999 or 123
*/
class SkipJack extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'SkipJack';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://developer.skipjackic.com/scripts/evolvcc.dll?AuthorizeAPI';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.skipjackic.com/scripts/evolvcc.dll?AuthorizeAPI';
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;
    
    var $error_codes = array(
        '1'   => 'Success (Valid Data)',
        '-35' => 'Invalid credit card number',
        '-37' => 'Error failed communication',
        '-39' => 'Error length serial number',
        '-51' => 'Invalid Billing Zip Code',
        '-52' => 'Invalid Shipto zip code',
        '-53' => 'Invalid expiration date',
        '-54' => 'Error length account number date',
        '-55' => 'Invalid Billing Street Address',
        '-56' => 'Invalid Shipto Street Address',
        '-57' => 'Error length transaction amount',
        '-58' => 'Invalid Name',
        '-59' => 'Error length location',
        '-60' => 'Invalid Billing State',
        '-61' => 'Invalid Shipto State',
        '-62' => 'Error length order string',
        '-64' => 'Invalid Phone Number',
        '-65' => 'Empty name',
        '-66' => 'Empty email',
        '-67' => 'Empty street address',
        '-68' => 'Empty city',
        '-69' => 'Empty state',
        '-79' => 'Error length customer name',
        '-80' => 'Error length shipto customer name',
        '-81' => 'Error length customer location',
        '-82' => 'Error length customer state',
        '-83' => 'Invalid Phone Number',
        '-84' => 'Pos error duplicate ordernumber',
        '-91' => 'Pos_error_CVV2',
        '-92' => 'Pos_error_Error_Approval_Code',
        '-93' => 'Pos_error_Blind_Credits_Not_Allowed',
        '-94' => 'Pos_error_Blind_Credits_Failed',
        '-95' => 'Pos_error_Voice_Authorizations_Not_Allowed'
    );
    
    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return PayPalProUS
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
        include(PMDROOT.'/includes/country_codes.php');
        include(PMDROOT.'/includes/state_codes.php');
        $country_codes_inverse = array_flip($country_codes);
        
        $this->parameters = array(
            'OrderNumber'=>$data['invoice_id'],
            'SJName'=>$data['user_first_name'].' '.$data['user_last_name'],
            'Email'=>$data['user_email'],
            'StreetAddress'=>$data['user_address1'],
            'StreetAddress2'=>$data['user_address2'],
            'City'=>$data['user_city'],
            'State'=>(isset($state_codes_inverse[$data['user_state']]) ? $state_codes_inverse[$data['user_state']] : $data['user_state']),
            'Country'=>(isset($country_codes_inverse[$data['user_country']]) ? $country_codes_inverse[$data['user_country']] : $data['user_country']),
            'ZipCode'=>$data['user_zip'],
            'Phone'=>$data['user_phone'], 
            'ShipToPhone'=>$data['user_phone'],
            'OrderString'=>$data['invoice_id'].'~'.$this->PMDR->getConfig('invoice_company').' - Invoice '.$data['invoice_id'].'~'.$data['balance'].'~1~N~||',
            'AccountNumber'=>$data['cc_number'],
            'Month'=>$data['cc_expire_month'],
            'Year'=>$data['cc_expire_year'],
            'CVV2'=>$data['cc_cvv2'],
            'TransactionAmount'=>$data['balance'],
            'InvoiceNumber'=>$data['invoice_id'],
            'Taxable'=>'N',
            'Quantity'=>1,
            'ItemCost'=>$data['balance'],
            'ItemDescription'=>$this->PMDR->getConfig('invoice_company').' - Invoice '.$data['invoice_id'],
            'ItemNumber'=>$data['invoice_id']
        );
        
        if($this->test_mode) {
            $this->parameters['DeveloperSerialNumber'] = $this->settings['skipjack_developer_serial'];    
        } else {
            $this->parameters['SerialNumber'] = $this->settings['skipjack_serial'];    
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
            parent::processPayment($this->response['szOrderNumber'],$this->response['szTransactionFileName'],$this->response['szTransactionAmount']);
        }
        return $this->parameters['szOrderNumber'];
    }
    
    
    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['szIsApproved'] == 1) {
                $this->result = 'success';
                $this->result_amount = $this->response['szTransactionAmount'];
            } else {
                $this->result = 'failed';
                $this->errors[] = $this->error_codes[$this->response['szReturnCode']];
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
        if($this->response['szTransactionAmount'] != str_replace('.','',$this->parameters['TransactionAmount'])) {
            $this->errors[] = 'Invalid amount';
            return false;
        }
        return true;
    }
     
    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
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
            $response = explode("\r", $response);
            $header = explode('","', $response[0]);
            $data = explode('","', $response[1]);
            
            foreach($header as $i => $array) {
                $this->response[str_replace(array("\r","\r\n","\n",'"'), "", $array)] = str_replace(array("\r","\r\n","\n",'"'), "", $data[$i]);
            }
            unset($response,$header,$data);
        }
        curl_close($ch);
        return $this->response;
    }
}
?>