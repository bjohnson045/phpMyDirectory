<?php
/**
* PayPalProUS payment gateway class
*/
class PayPalProUSRecurring extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'PayPalProUSRecurring';
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

        // Notify URL parameter does not work here so the account must use the IPN url.
        $this->parameters = array(
            'METHOD'=>'CreateRecurringPaymentsProfile',
            'SUBSCRIBERNAME'=>$data['user_first_name'].' '.$data['user_last_name'],
            'PROFILESTARTDATE'=>date('Y-m-d',strtotime('+'.$data['period_count'].' '.$data['period'])).'T0:0:0',
            'PROFILEREFERENCE'=>$data['order_id'],
            'DESC'=>$this->PMDR->getConfig('invoice_company').' - Order #'.$data['order_number'],
            'AUTOBILLOUTAMT'=>'NoAutoBill',
            'BILLINGPERIOD'=>ucfirst(rtrim($data['period'],'s')),
            'BILLINGFREQUENCY'=>$data['period_count'],
            'AMT'=>$data['amount_recurring'],
            'INITAMT'=>$data['amount_recurring'],
            'VERSION'=>'56.0',
            'USER'=>$this->settings['paypalpro_api_username'],
            'PWD'=>$this->settings['paypalpro_api_password'],
            'SIGNATURE'=>$this->settings['paypalpro_api_signiture'],
            'CREDITCARDTYPE'=>$data['cc_type'], // Visa, MasterCard, Amex, Discover
            'ACCT'=>$data['cc_number'], // credit card number
            'EXPDATE'=>$data['cc_expire_month'].$data['cc_expire_year'],  // MONTHYEAR
            'CVV2'=>$data['cc_cvv2'],
            'CURRENCYCODE'=>$this->settings['paypalpro_currency'],
            'COUNTRYCODE'=>(isset($country_codes_inverse[$data['user_country']]) ? $country_codes_inverse[$data['user_country']] : $data['user_country']),
            'EMAIL'=>$data['user_email'],
            'BUSINESS'=>$data['user_organization'],
            'FIRSTNAME'=>$data['user_first_name'],
            'LASTNAME'=>$data['user_last_name'],
            'STREET'=>$data['user_address1'],
            'STREET2'=>$data['user_address2'],
            'PHONENUM'=>$data['user_phone'],
            'CITY'=>$data['user_city'],
            'STATE'=>(isset($state_codes_inverse[$data['user_state']]) ? $state_codes_inverse[$data['user_state']] : $data['user_state']),
            'ZIP'=>$data['user_zip'],
            'IPADDRESS'=>get_ip_address(),
            'BUTTONSOURCE'=>'AccomplishTechnology_SP',
        );
        if($data['days_until_due'] > 0 AND $data['days_until_due'] <= 90) {
            $this->parameters['INITAMT'] = $data['total'];
            $this->parameters['PROFILESTARTDATE'] = date('Y-m-d',strtotime('+'.$data['days_until_due'].' days')).'T0:0:0';
        } elseif($data['total'] != $data['amount_recurring']) {
            $this->parameters['INITAMT'] = $data['total'];
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
        $this->setupSubscription($this->parameters['PROFILEREFERENCE'],$this->response['PROFILEID']);
        return $this->parameters['PROFILEREFERENCE'];
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
                // Paypal doesn't return the amount for the initial response so we have to rely on the parameters
                $this->result_amount = $this->parameters['amount_recurring'];
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
* PayPalProUS payment gateway notification class
*/
class PayPalProUSRecurring_Notification extends PayPalProUSRecurring {
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
            if($this->response['txn_type'] == 'recurring_payment_profile_created') {
                if($this->response['initial_payment_status'] == 'Completed') {
                    parent::processSubscriptionPayment($this->response['rp_invoice_id'],$this->response['initial_payment_txn_id'],$this->response['amount']);
                }
            } elseif($this->response['txn_type'] == 'recurring_payment_profile_cancel') {
                parent::cancelSubscription($this->response['rp_invoice_id']);
            } elseif($this->response['txn_type'] == 'recurring_payment') {
                if($this->response['payment_status'] == 'Completed') {
                    parent::processSubscriptionPayment($this->response['rp_invoice_id'],$this->response['txn_id'],$this->response['amount']);
                }
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
            CURLOPT_POSTFIELDS=>$parameters
        );
        if($http_request->get('curl',$this->url) != 'VERIFIED') {
            $this->errors[] = 'Invalid response';
            return false;
        }

        $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.subscription_id=? ORDER BY i.date_due ASC",array($this->response['recurring_payment_id']));
        if(!$this->loadStoredParameters($invoice_id)) {
            $this->errors[] = 'Invalid invoice number (invoice)';
            return false;
        }
        if($this->parameters['CURRENCYCODE'] != $this->response['currency_code']) {
            $this->errors[] = 'Invalid currency';
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
            if($this->response['txn_type'] == 'recurring_payment_profile_cancel') {
                $this->result = 'success';
            } elseif(isset($this->response['recurring_payment_id'])) {
                if(isset($this->response['initial_payment_status'])) {
                    $payment_status = $this->response['initial_payment_status'];
                } else {
                    $payment_status = $this->response['payment_status'];
                }
                switch($payment_status) {
                    case 'Completed':
                        $this->result = 'success';
                        $this->result_amount = $this->response['amount'];
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
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }
}
?>