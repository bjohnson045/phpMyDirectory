<?php
class GTBillQuickPay extends PaymentAPIHandler {
    var $gateway_name = 'GTBillQuickPay';
    var $test_url = '';
    var $url = 'https://sale.GTBill.com/quickpay.aspx';
    
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
            'MerchantID'=>$this->settings['gtbillquickpay_merchant_id'],
            'SiteID'=>$this->settings['gtbillquickpay_site_id'],
            'CurrencyID'=>$this->settings['gtbillquickpay_currency'],
            'AmountShipping'=>'0.00',
            'ShippingRequired'=>0,
            'ReturnURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'AmountTotal'=>$data['balance'],
            'ItemAmount[0]'=>$data['balance'],
            'ItemDesc[0]'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ItemName[0]'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ItemQuantity[0]'=>1,
            'ConfirmURL'=>BASE_URL.'/modules/processors/GTBillQuickPay/GTBillQuickPay_ipn.php',
            'FirstName'=>$data['user_first_name'],
            'LastName'=>$data['user_last_name'],
            'Address1'=>$data['user_address1'],
            'Address2'=>$data['user_address2'],
            'City'=>$data['user_city'],
            'State'=>$data['user_state'],
            'Country'=>$country,
            'PhoneNumber'=>$data['user_phone'],
            'PostalCode'=>$data['user_zip'],
            'Email'=>$data['user_email'],
            'MerchantReference'=>$data['invoice_id']
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
        if(!$this->verifyResponse()) {
            $this->result = 'failed';
        } else {
            $this->loadResult();
        }
        return $this->response['MerchantReference'];    
    }
    
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['MerchantReference'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if(!isset($this->response['TransactionID'])) {
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
        // GTBill does not return unless its a success
        if($this->verifyResponse()) {
            $this->result = 'success';
            $this->result_amount = $this->response['Amount'];
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

class GTBillQuickPay_Notification extends GTBillQuickPay {
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1
        );
        $response = $http_request->get('curl','https://sale.gtbill.com/ip_list.txt');
        if(!in_array(get_ip_address(),explode('|',$response))) {
            $this->errors[] = 'Invalid IP address';
            return false;
        }
        if(!$this->loadStoredParameters($this->response['MerchantReference'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if( $this->parameters['SiteID'] != $this->response['SiteID'] OR 
            $this->parameters['AmountTotal'] != $this->response['Amount']
            ) {
                $this->errors[] = 'Invalid site ID or amount';
                return false;    
            }
        return true;    
    }
    
    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['MerchantReference'],$this->response['TransactionID'],$this->response['Amount']);
        }
        return $this->response['MerchantReference'];    
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