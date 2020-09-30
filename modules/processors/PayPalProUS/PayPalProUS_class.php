<?php
/**
* PayPalProUS payment gateway class
*/
class PayPalProUS extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'PayPalProUS';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://api-3t.sandbox.paypal.com/nvp';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://api-3t.paypal.com/nvp';
    /**
    * On site payment
    * Displays credit card form and handles on site processing
    * @var boolean
    */
    var $on_site_payment = true;

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
            'PAYMENTACTION'=>'Sale',
            'METHOD'=>'doDirectPayment',
            'VERSION'=>'2.3',
            'USER'=>$this->settings['paypalpro_api_username'],
            'PWD'=>$this->settings['paypalpro_api_password'],
            'SIGNATURE'=>$this->settings['paypalpro_api_signiture'],
            'AMT'=>$data['balance'],
            'CREDITCARDTYPE'=>$data['cc_type'], // Visa, MasterCard, Amex, Discover
            'ACCT'=>$data['cc_number'], // credit card number
            'EXPDATE'=>$data['cc_expire_month'].$data['cc_expire_year'],  // MONTHYEAR
            'CVV2'=>$data['cc_cvv2'],
            'BUSINESS'=>$data['user_organization'],
            'FIRSTNAME'=>$data['user_first_name'],
            'LASTNAME'=>$data['user_last_name'],
            'STREET'=>$data['user_address1'],
            'STREET2'=>$data['user_address2'],
            'PHONENUM'=>$data['user_phone'],
            'CITY'=>$data['user_city'],
            'STATE'=>(isset($state_codes_inverse[$data['user_state']]) ? $state_codes_inverse[$data['user_state']] : $data['user_state']),
            'ZIP'=>$data['user_zip'],
            'COUNTRYCODE'=>(isset($country_codes_inverse[$data['user_country']]) ? $country_codes_inverse[$data['user_country']] : $data['user_country']),
            'CURRENCYCODE'=>$this->settings['paypalpro_currency'],
            'INVNUM'=>$data['invoice_id'],
            'IPADDRESS'=>get_ip_address(),
            'NOTIFYURL'=>BASE_URL.'/modules/processors/PayPalProUS/PayPalProUS_ipn.php',
            'TAXAMT'=>'',
            'DESC'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'EMAIL'=>$data['user_email'],
            'BUTTONSOURCE'=>'AccomplishTechnology_SP',
        );

        return true;
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->parameters['INVNUM'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(in_array(strtoupper($this->response['ACK']),array('SUCCESS','SUCCESSWITHWARNING'))) {
                $this->result = 'success';
                $this->result_amount = $this->response['mc_gross'];
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
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($this->parameters));

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
* PayPalProUS payment gateway notification class
*/
class PayPalProUS_Notification extends PayPalProUS {
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.paypal.com/cgi-bin/webscr';

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['payment_status']) {
                case 'Completed':
                    parent::processPayment($this->response['invoice'],$this->response['txn_id'],$this->response['mc_gross']);
                    break;
                case 'Refunded':
                case 'Reversed':
                    parent::processRefund($this->response['txn_id']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['invoice'];
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        $this->response = $_POST;
    }

    /**
    * Verify Response
    * Determines whether the response is valid
    * @return boolean
    */
    function verifyResponse() {
        $parameters = 'cmd=_notify-validate';
        foreach ($this->response as $key=>$value) {
            $parameters .= "&$key=".urlencode(stripslashes($value));
        }
        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$parameters,
            CURLOPT_SSL_VERIFYPEER=>1,
            CURLOPT_SSL_VERIFYHOST=>2
        );
        if($http_request->get('curl',$this->url) != 'VERIFIED') {
            $this->errors[] = 'Invalid response';
            return false;
        }

        if(!$this->loadStoredParameters($this->response['invoice'])) {
            $this->errors[] = 'Invalid invoice number (invoice)';
            return false;
        }
        if( $this->parameters['CURRENCYCODE'] != $this->response['mc_currency']
            //$this->parameters['AMT'] != $this->response['mc_gross'] OR
            ) {
                $this->errors[] = 'Invalid currency';
                return false;
            }
            // Add back in AMT once we pass the payment fields properly making on site payment the same
            // as off site payment.  Problem was AMT was not set because invoice_total is not gotten when we load parameters.
        return true;
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['payment_status']) {
                case 'Completed':
                    $this->result = 'success';
                    $this->result_amount = $this->response['mc_gross'];
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
}
?>