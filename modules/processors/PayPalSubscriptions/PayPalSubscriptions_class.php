<?php
class PayPalSubscriptions extends PaymentAPIHandler {
    var $gateway_name = 'PayPalSubscriptions';
    var $test_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    var $url = 'https://www.paypal.com/cgi-bin/webscr';

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
            'cmd'=>'_xclick-subscriptions',
            'business'=>$this->settings['paypal_email'],    // directory owners email address
            'return'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',     // returned here after payment
            'notify_url'=>BASE_URL.'/modules/processors/PayPalSubscriptions/PayPalSubscriptions_ipn.php',     // IPN notification url
            'cancel_return'=>BASE_URL.MEMBERS_FOLDER.'user_invoices.php',   // go here if the user cancels
            'custom'=>$data['order_id'],   // used to store the invoice ID we are paying
            'currency_code'=>$this->settings['paypal_currency'],    // currency code loaded from paypal settings
            'item_name'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],    // set item name by company name and invoice ID
            'quantity'=>'1',    // order only 1
            'no_shipping'=>'1', // do not use shipping
            'rm'=>'2',          // sent back response as $_POST
            'address1'=>$data['user_address1'],      // address of payer
            'address2'=>$data['user_address2'],      // address of payer line 2
            'city'=>$data['user_city'],          // city of payer
            'email'=>$data['user_email'],         // email of payer
            'first_name'=>$data['user_first_name'],    // first name of payer
            'last_name'=>$data['user_last_name'],     // last name of payer
            'zip'=>$data['user_zip'],           // zip of payer
            'sra'=>'1', // attempt after failed attempt
            'ButtonSource'=>'AccomplishTechnology_SP',
        );
        if(!$data['period_count']) {
            $data['period_count'] = '1';
            $this->parameters['src'] = '0';
        } else {
            $this->parameters['src'] = '1';
        }

        // Do not include the set up fee in the recurring payment
        $this->parameters['a3'] = $data['amount_recurring'];
        $this->parameters['p3'] = $data['period_count'];
        $this->parameters['t3'] = strtoupper(substr($data['period'],0,1));

        if($data['days_until_due'] > 0 AND $data['days_until_due'] <= 90) {
            $this->parameters['a1'] = $data['total'];
            $this->parameters['p1'] = $data['days_until_due'];
            $this->parameters['t1'] = 'D';
        } elseif($data['total'] != $data['amount_recurring']) {
            $this->parameters['a1'] = $data['total'];
            $this->parameters['p1'] = $data['period_count'];
            $this->parameters['t1'] = strtoupper(substr($data['period'],0,1));
        }

        return true;
    }

    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['custom'];
    }

    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function processNotification() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['txn_type']) {
                case 'subscr_payment':
                    switch($this->response['payment_status']) {
                        case 'Completed':
                            if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_INVOICES." i WHERE i.order_id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($this->response['custom']))) {
                                parent::processPayment($invoice_id,$this->response['txn_id'],$this->response['mc_gross']);
                            } else {
                                // give credit
                            }
                            break;
                        case 'Refunded':
                        case 'Reversed':
                            parent::processRefund($this->response['txn_id']);
                            break;
                        default:
                            return false;
                    }
                    break;
                case 'subscr_signup':
                    $this->setupSubscription($this->response['custom'],$this->response['subscr_id']);
                    break;
                case 'subscr_cancel':
                    $this->cancelSubscription($this->response['custom']);
                    break;
            }
        }
        return $this->response['custom'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(isset($this->response['tx'])) {
            $parameters = 'cmd=_notify-synch&tx='.urlencode($_GET['tx']).'&at='.urlencode($this->settings['paypal_pdt_token']);
            $http_request = $this->PMDR->get('HTTP_Request');
            $http_request->settings = array(
                CURLOPT_HEADER=>0,
                CURLOPT_RETURNTRANSFER=>1,
                CURLOPT_POST=>1,
                CURLOPT_POSTFIELDS=>$parameters,
                CURLOPT_CAINFO => PMDROOT.'/includes/cacert.pem',
                CURLOPT_HTTPHEADER=>array('Host: www.paypal.com')
            );
            $response = $http_request->get('curl',$this->url);
            //echo $response;
            $response_parts = explode("\n", preg_replace('/\r\n|\r/', "\n", $response));
            //print_array($response_parts);
            if(array_shift($response_parts) != 'SUCCESS') {
                $this->errors[] = 'Invalid response';
                return false;
            }
            foreach($response_parts as $part) {
                $value = explode('=',$part);
                if($value[0] != '') {
                    $this->response[$value[0]] = urldecode($value[1]);
                }
            }
        } else {
            $parameters_ignore = array('cmd');
            $parameters = 'cmd=_notify-validate';
            foreach ($this->response as $key=>$value) {
                if(in_array($key,$parameters_ignore)) {
                    continue;
                }
                $parameters .= "&$key=".urlencode(stripslashes($value));
            }
            unset($parameters_ignore);
            $http_request = $this->PMDR->get('HTTP_Request');
            $http_request->settings = array(
                CURLOPT_HEADER=>0,
                CURLOPT_RETURNTRANSFER=>1,
                CURLOPT_POST=>1,
                CURLOPT_POSTFIELDS=>$parameters,
                CURLOPT_CAINFO => PMDROOT.'/includes/cacert.pem',
                CURLOPT_HTTPHEADER=>array('Host: www.paypal.com')
            );
            if($http_request->get('curl',$this->url) != 'VERIFIED') {
                $this->errors[] = 'Invalid response';
                return false;
            }
        }
        if(!$this->loadStoredParameters($this->response['custom'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if( $this->parameters['currency_code'] != $this->response['mc_currency'] OR
            strtolower($this->parameters['business']) != strtolower($this->response['business'])
            ) {
                $this->errors[] = 'Invalid email address';
                return false;
            }
        return true;
    }

    function loadStoredParameters($order_id) {
        $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.id=? ORDER BY i.date_due ASC",array($order_id));
        return parent::loadStoredParameters($invoice_id);
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['txn_type']) {
                case 'subscr_signup' OR 'subscr_cancel' OR 'subscr_eot' OR 'subscr_modify' OR 'subscr_failed':
                    $this->result = 'success';
                    break;
                case 'subscr_payment':
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
        if(isset($_GET['tx'])) {
            if(!count($_GET)) {
                $this->errors[] = 'GET empty';
            }
            $this->response = $_GET;
        } else {
            if(!count($_POST)) {
                $this->errors[] = 'POST empty';
            }
            $this->response = $_POST;
        }
    }
}
?>