<?php
/**
* Setcom payment gateway class
*/
class Setcom extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'Setcom';
    /**
    * Test URL
    * @var string
    */
    var $test_url = '';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://secure.setcom.co.za/creditcard.cfm';

    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return Setcom
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
        if(isset($country_codes[$data['user_country']])) {
            $country = $data['user_country'];
        } elseif(isset($country_codes_inverse[$data['user_country']])) {
            $country = $country_codes_inverse[$data['user_country']];
        } else {
            $country = '';
        }
        $this->parameters = array(
            'CO_ID'=>$this->settings['setcom_id'],
            'OUTLET'=>$this->settings['setcom_outlet'],
            'Reference'=>$data['invoice_id'],
            'CC_Amount'=>$data['balance'],
            'EmailAddress'=>$data['user_email'],
            'MobileNumber'=>$data['user_phone'],
            'buyer_id'=>$data['user_id'],
            'bill_first_name'=>$data['user_first_name'],
            'bill_last_name'=>$data['user_last_name'],
            'bill_street1'=>$data['user_address1'],
            'bill_street2'=>$data['user_address2'],
            'bill_city'=>$data['user_city'],
            'bill_state'=>$data['user_state'],
            'bill_zip'=>$data['user_zip'],
            'bill_country'=>$country,
            'bill_phone'=>$data['user_phone']
        );
        $this->parameters['Consistent Key'] = md5($this->parameters['CO_ID'].$this->parameters['OUTLET'].$this->parameters['Reference'].$this->parameters['CC_Amount']);
        return true;
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['Reference'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(strtolower($this->response['Outcome']) == 'approved') {
                $this->result = 'success';
                $this->result_amount = $this->response['Amount'];
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
        if(isset($_GET['authnumber'])) {
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
* Setcom payment gateway notification class
*/
class Setcom_Notification extends Setcom {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch(strtolower($this->response['Outcome'])) {
                case 'approved':
                    parent::processPayment($this->response['Reference'],$this->response['OrderNumber'],$this->response['Amount']);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $this->response['Reference'];
    }
}
?>