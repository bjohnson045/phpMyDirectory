<?php
/**
* Setcom payment gateway class
*/
class SetcomSID extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'SetcomSID';
    /**
    * Test URL
    * @var string
    */
    var $test_url = '';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://www.sidpayment.com/paySID/';

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
        $this->parameters = array(
            'SID_MERCHANT'=>$this->settings['setcom_merchant'],
            'SID_CURRENCY'=>$this->settings['setcom_currency'],
            'SID_REFERENCE'=>$data['invoice_id'],
            'SID_AMOUNT'=>$data['balance'],
            'SID_DEFAULT_REFERENCE'=>$data['user_id'],
            'SID_COUNTRY'=>'ZA'
        );
        $this->parameters['SID_CONSISTENT'] = strtoupper(hash('sha512',$this->parameters['SID_MERCHANT'].$this->parameters['SID_CURRENCY'].$this->parameters['SID_COUNTRY'].$this->parameters['SID_REFERENCE'].$this->parameters['SID_AMOUNT'].$this->parameters['SID_DEFAULT_REFERENCE'].$this->settings['setcom_key']));
        return true;
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['SID_REFERENCE'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(strtolower($this->response['SID_STATUS']) == 'completed') {
                $this->result = 'success';
                $this->result_amount = $this->response['SID_AMOUNT'];
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
        $hash = $this->response;
        unset($hash['SID_CONSISTENT']);
        if($this->response['SID_CONSISTENT'] == strtoupper(hash('sha512',implode('',$hash).$this->settings['setcom_key']))) {
            return true;
        } else {
            $this->errors[] = 'Hash does not match';
            return false;
        }
    }

    /**
    * Load Response
    * Loads the response from the gateway
    * @return mixed
    */
    function loadResponse() {
        if(isset($_GET['SID_RECEIPTNO'])) {
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
class SetcomSID_Notification extends SetcomSID {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        $this->response['post'] = 'true';
        if($this->result == 'success') {
            switch(strtolower($this->response['SID_STATUS'])) {
                case 'completed':
                    parent::processPayment($this->response['SID_REFERENCE'],$this->response['SID_TNXID'],$this->response['SID_AMOUNT']);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $this->response['SID_REFERENCE'];
    }
}
?>