<?php
/**
* Clickbank payment gateway class
* Can not be used with discount codes or pro-rating because of static product prices
*/
class ClickBank extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'ClickBank';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'http://ITEM.VENDOR.pay.clickbank.net';

    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return ClickBank
    */
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
        $this->url = str_replace('VENDOR',$this->settings['clickbank_nickname'],$this->url);
        $this->url = str_replace('ITEM',$this->settings['clickbank_product_id_'.$data['pricing_id']],$this->url);

        $this->parameters = array(
            'invoice_id'=>$data['invoice_id'],
            'order_id'=>$data['order_id'],
            'total'=>$data['balance']
        );

        return true;
    }

    /**
    * Get payment button
    * Sets the method to GET for ClickBank
    * @return object Form
    */
    function getPaymentButton() {
        $form = $this->PMDR->getNew('Form');
        $form->method = 'GET';
        $form->setName('payment_button');
        $form->action = $this->url;
        $form->addFieldSet('hidden');
        foreach($this->parameters as $name=>$value) {
            $form->addField($name,'hidden',array('label'=>$name,'fieldset'=>'hidden','value'=>$value));
        }
        $form->addField('submit','submit',array('label'=>$this->PMDR->getLanguage('user_general_pay_now'),'fieldset'=>'submit'));
        return $form;
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['invoice_id'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(isset($this->response['cbreceipt'])) {
                $this->result = 'success';
                $this->result_amount = $this->response['total'];
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
        if ($this->response['cbpop'] != strtoupper(substr(sha1($this->settings['clickbank_secret_key']."|".$this->response['cbreceipt']."|".$this->response['time']."|".$this->response['item']),0,8))) {
            $this->errors[] = 'Invalid secrey key';
            return false;
        }
        if(!$this->loadStoredParameters($this->response['invoice_id'])) {
            $this->errors[] = 'Invalid invoice number';
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
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;
    }
}

/**
* ClickBank payment gateway notification class
*/
class ClickBank_Notification extends ClickBank {
    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['ctransaction']) {
                case 'SALE':
                case 'TEST_SALE':
                    parent::processPayment($this->response['invoice_id'],$this->response['ctransreceipt'],$this->response['total']);
                    if($this->response['cprodtype'] == 'RECURRING') {
                        $this->setupSubscription($this->response['order_id'],$this->response['ctransreceipt']);
                    }
                    break;
                case 'BILL':
                    parent::processPayment($this->response['invoice_id'],$this->response['ctransreceipt'],$this->response['total']);
                    break;
                case 'CANCEL-REBILL':
                    $this->cancelSubscription($this->response['order_id']);
                    break;
                case 'RFND':
                    parent::processRefund($this->response['order_id']);
                    break;
                default:
                    return false;
                    break;
            }
        }
        return $this->response['invoice_id'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if(isset($this->response['ctransaction'])) {
                $this->result = 'success';
                $this->result_amount = $this->response['total'];
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
        $hash_array = array();
        parse_str($this->response['cvendthru'],$custom_variables);
        foreach($this->response AS $key=>$value) {
            if($key == 'cverify' OR array_key_exists($key,$custom_variables)) {
                continue;
            }
            $hash_array[$key] = $value;
        }
        ksort($hash_array);
        $hash_array[] = $this->settings['clickbank_secret_key'];

        if($this->response['cverify'] != strtoupper(substr(sha1(implode('|',$hash_array)),0,8))) {
            $this->errors[] = 'Invalid hash - '.implode('|',$hash_array);
            return false;
        }

        if(!$this->loadStoredParameters($this->response['invoice_id'])) {
            $this->errors[] = 'Invalid invoice number';
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
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        parse_str($_POST['cvendthru'],$custom_variables);
        foreach($custom_variables AS $key=>$variable) {
            $_POST[$key] = $variable;
        }
        $this->response = $_POST;
    }
}
?>