<?php
/**
* MonsterPay payment gateway class
*/
class MonsterPay extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'MonsterPay';
    /**
    * Test URL
    * @var string
    */
    var $test_url = '';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.monsterpay.com/secure/';  // https://www2.2checkout.com/2co/buyer/purchase

    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return MonsterPay
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
            'ButtonAction'=>'buynow',
            'MerchantIdentifier'=>$this->settings['monsterpay_merchant_identifier'],
            'LIDSKU'=>$data['invoice_id'],
            'LIDDesc'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'LIDPrice'=>$data['balance'],
            'LIDQty'=>1,
            'ShippingRequired'=>0,
            'CurrencyAlphaCode'=>$this->settings['monsterpay_currency'],
            'MerchCustom'=>$data['invoice_id'],
            'BuyerInformation'=>1,
            'FirstName'=>$data['user_first_name'],
            'LastName'=>$data['user_last_name'],
            'Address1'=>$data['user_address1'],
            'Address2'=>$data['user_address2'],
            'City'=>$data['user_city'],
            'State'=>$data['user_state'],
            'PostalCode'=>$data['user_zip'],
            'Country'=>$data['user_country'],
            'Email'=>$data['user_email'],
            'HomeNumber'=>$data['user_phone']
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
        return $this->response['id'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['status'] == 'Complete') {
                $this->result = 'success';
                $this->result_amount = $this->response['amount_total'];
            } elseif($this->response['status'] == 'Pending') {
                $this->result = 'pending';
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
        $parameters = 'Method=order_synchro';
        $parameters .= '&identifier='.urlencode(stripslashes($this->settings['monsterpay_merchant_identifier']));
        $parameters .= '&usrname='.urlencode(stripslashes($this->settings['monsterpay_username']));
        $parameters .= '&pwd='.urlencode(stripslashes($this->settings['monsterpay_password']));
        $parameters .= '&txnid='.urlencode(stripslashes($this->response['txnid']));
        $parameters .= '&checksum='.urlencode(stripslashes($this->response['checksum']));
        $parameters .= '&parity='.urlencode(stripslashes($this->response['parity']));

        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_POST=>1,
            CURLOPT_POSTFIELDS=>$parameters
        );
        if(!$raw_response = $http_request->get('curl','https://www.monsterpay.com/secure/components/synchro.cfc?wsdl')) {
            return false;
        } else {
            $xmlobject = simplexml_load_string(trim($raw_response));
            $this->response['status'] = $xmlobject->{'outcome'}->items->item->{'status'};
            $this->response['error_number'] = $xmlobject->{'outcome'}->{'error_code'};
            $this->response['error_message'] = $xmlobject->{'outcome'}->{'error_desc'};
            $this->response['reference'] = $xmlobject->{'seller'}->{'reference'};
            $this->response['amount_total'] = $xmlobject->{'financial'}->{'amount_total'};
            $this->response['currency'] = $xmlobject->{'financial'}->{'currency'};
            $this->response['id'] = $xmlobject->{'outcome'}->{'order'}->{'id'};
        }
        if(!$this->loadStoredParameters($this->response['reference'])) {
            $this->errors[] = 'Invalid invoice number (reference)';
            return false;
        }
        if($this->response['currency'] != $this->settings['monsterpay_currency']) {
            $this->errors[] = 'Invalid currency';
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
        if(isset($_GET['txnid'])) {
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

/**
* MonsterPay payment gateway notification class
*/
class MonsterPay_Notification extends MonsterPay {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['status']) {
                case 'Completed':
                    parent::processPayment($this->response['id'],$this->response['reference'],$this->response['amount_total']);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $this->response['id'];
    }
}
?>