<?php
/**
* Beanstream payment gateway class
* Test CC: 4030000010001234
* Testing requires contacting bean stream
* Must display return paramter 'authCode' to the user
* Parameter rbAccountId or billingId (on recurrences) used for recurring billing ID
* trnApproved = 1 or 0
*/
class Beanstream extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'Beanstream';
    /**
    * Test URL
    * @var string
    */
    var $test_url = '';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.beanstream.com/scripts/process_transaction.asp';
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;

    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return Beanstream
    */
    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->settings['beanstream_hosted']) {
            $this->on_site_payment = false;
            $this->url = 'https://www.beanstream.com/scripts/payment/payment.asp';
        }
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
            'requestType'=>'BACKEND',
            'errorPage'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php', // error URL with response errorFields and errorMessage, messageText
            'merchant_id'=>$this->settings['beanstream_merchant_id'],
            'trnOrderNumber'=>$data['invoice_id'],
            'paymentMethod'=>'CC',
            'trnType'=>'P',
            'username'=>$this->settings['beanstream_username'],
            'password'=>$this->settings['beanstream_password'],
            'approvedPage'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'declinedPage'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'trnComments'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'trnCardOwner'=>$data['user_first_name'].' '.$data['user_last_name'],
            'trnCardNumber'=>$data['cc_number'],
            'trnExpYear'=>(strlen($data['cc_expire_year']) == 2 ? $data['cc_expire_year'] : substr($data['cc_expire_year'],2)),
            'trnExpMonth'=>$data['cc_expire_month'],
            'ordName'=>$data['user_first_name'].' '.$data['user_last_name'],
            'ordEmailAddress'=>$data['user_email'],
            'ordPhoneNumber'=>$data['user_phone'],
            'ordAddress1'=>$data['user_address1'],
            'ordAddress2'=>$data['user_address2'],
            'ordCity'=>$data['user_city'],
            'ordState'=>$data['user_state'],
            'ordPostalCode'=>$data['user_zip'],
            'ordCountry'=>$country_codes_inverse[$data['user_country']],
            'ordItemPrice'=>$data['balance'],
            'trnAmount'=>$data['balance'],
            'ordTax1Price'=>$data['invoice_tax']
        );
        if(in_array(strtoupper($data['user_state']),$state_codes)) {
            $this->parameters['ordProvince'] = $state_codes_inverse[$data['user_state']];
        }
        if($this->settings['beanstream_cvd']) {
            $this->parameters['trnCardCvd'] = $data['cc_cvv2'];
        }
        // Need to set recurring billing response in account
        if($this->settings['beanstream_recurring']) {
            $this->parameters['trnRecurring'] = 1;
            $this->parameters['rbBillingPeriod'] = strtoupper(substr($data['period'],0,1));
            $this->parameters['rbBillingIncrement'] = $data['period_count'];
            $this->parameters['rbSecondBilling'] = date('mdY',mktime(0,0,0,date('m'),date('d')+$data['days_until_due'],date('Y')));
            $this->parameters['rbApplyTax1'] = 1;
        }
        if($this->settings['beanstream_hashvalue'] != '' AND $this->settings['beanstream_hashencryption'] != '') {
            if($this->settings['beanstream_hashencryption'] == 'md5') {
                $this->parameters['hashValue'] = md5(http_build_query($this->parameters).$this->settings['beanstream_hashvalue']);
            } else {
                $this->parameters['hashValue'] = sha1(http_build_query($this->parameters).$this->settings['beanstream_hashvalue']);
            }
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
        return $this->parameters['trnOrderNumber'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['trnApproved']) {
                case 0:
                    $this->result = 'declined';
                    break;
                case 1:
                    $this->result = 'success';
                    $this->result_amount = $this->response['trnAmount'];
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
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($this->parameters));
        $response = curl_exec($ch);
        if(!$response) {
            $this->errors[] = curl_error($ch).' ('.curl_errno($ch).')';
        } else {
            $parts = explode('&',$response);
            foreach($parts as $part) {
                $value = explode('=',$part);
                $this->response[$value[0]] = urldecode($value[1]);
            }
        }
        curl_close($ch);
        return $this->response;
    }
}

/**
* Beanstream payment gateway notification class
*/
class Beanstream_Notification extends Beanstream {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['trnOrderNumber'],$this->response['trnId'],$this->response['trnAmount']);
        }
        return $this->response['trnOrderNumber'];
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
        $this->response = $_POST;
    }

    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['trnOrderNumber'])) {
            $this->errors[] = 'Invalid invoice number (trnOrderNumber)';
            return false;
        }
        return true;
    }
}
?>