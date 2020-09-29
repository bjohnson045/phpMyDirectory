<?php
/**
* PayflowPro payment gateway class
*/
class PayflowProHosted extends PaymentAPIHandler {
    /**
    * Gateway name
    * @var string
    */
    var $gateway_name = 'PayflowProHosted';
    /**
    * Test URL
    * @var string
    */
    var $test_url = 'https://pilot-payflowpro.paypal.com';
    /**
    * Gateway URL
    * @var string
    */
    var $url = 'https://payflowpro.paypal.com';


    /**
    * Payment gateway constructor
    * @param object $PMDR
    * @return PayflowPro
    */
    function __construct($PMDR) {
        parent::__construct($PMDR);
        $this->token = uniqid('',true);
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return boolean
    */
    function loadParameters($data) {
        if(trim($this->settings['payflowpro_user']) == '') {
            $this->settings['payflowpro_user'] = $this->settings['payflowpro_vendor'];
        }
        $security_token_request = array(
            'USER'=>$this->settings['payflowpro_user'],
            'VENDOR'=>$this->settings['payflowpro_vendor'],
            'PARTNER'=>$this->settings['payflowpro_partner'],
            'PWD'=>$this->settings['payflowpro_password'],
            'TRXTYPE'=>'S',
            'AMT'=>$data['balance'],
            'CREATESECURETOKEN'=>'Y',
            'SECURETOKENID'=>$this->token,
            'INVNUM'=>$data['invoice_id'],
            'FIRSTNAME'=>$data['user_first_name'],
            'LASTNAME'=>$data['user_last_name'],
            'STREET'=>$data['user_address1'],
            'CITY'=>$data['user_city'],
            'STATE'=>$data['user_state'],
            'ZIP'=>$data['user_zip'],
            'COUNTRY'=>$data['user_country'],
            'CUSTIP'=>get_ip_address(),
            'EMAIL'=>$data['user_email'],
            'COMMENT1'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'PHONENUM'=>$data['user_phone'],
            'BUTTONSOURCE'=>'AccomplishTechnology_SP',
        );
        if($this->settings['payflowpro_authorization']) {
            $security_token_request['TRXTYPE'] = 'A';
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, urldecode(http_build_query($security_token_request)));
        $response = curl_exec($ch);
        parse_str($response, $response_array);

        if($arr['RESULT'] != 0) {
            return false;
        }

        $this->security_token = $response_array['SECURETOKEN'];

        return true;
    }

    function getPaymentButton() {
        $this->payment_template->set('mode',($this->settings['testmode'] ? 'TEST' : 'LIVE'));
        $this->payment_template->set('security_token',$this->security_token);
        $this->payment_template->set('security_token_id',$this->token);
    }

    /**
    * Process response
    * @return int Invoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        return $this->response['INVNUM'];
    }

    /**
    * Load Result
    * Loads a result (success, failed, declined) based on the response from the gateway
    * @return void;
    */
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['RESULT'] == '0') {
                $this->result = 'success';
                $this->result_amount = $this->response['AMT'];
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
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;
    }
}

class PayflowProHosted_Notification extends PayflowProHosted {
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['INVNUM'],$this->response['PNREF'],$this->response['AMT']);
        }
    }


    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}
?>