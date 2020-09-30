<?php
class WebMoney extends PaymentAPIHandler {
    var $gateway_name = 'WebMoney';
    var $test_url = 'https://merchant.wmtransfer.com/lmi/payment.asp';
    var $url = 'https://merchant.wmtransfer.com/lmi/payment.asp';

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
            'LMI_PAYEE_PURSE'=>$this->settings['webmoney_purse'],
            'LMI_PAYMENT_AMOUNT'=>$data['balance'],
            'LMI_PAYMENT_NO'=>$data['invoice_id'],
            'LMI_PAYMENT_DESC'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'LMI_SUCCESS_METHOD'=>1, // POST
            'LMI_FAIL_METHOD'=>1,
            'LMI_FAIL_URL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'LMI_SUCCESS_URL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'LMI_RESULT_URL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
        );
        if($this->settings['testmode']) {
            $this->parameters['LMI_SIM_MODE'] = 0;
        }
    }

    /**
    * Process return
    * We process the return to get result
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['LMI_PAYMENT_NO'],$this->response['LMI_SYS_TRANS_NO'],$this->response['LMI_PAYMENT_AMOUNT']);
        }
        return $this->response['LMI_PAYMENT_NO'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        // MD5 should be set in web money control panel
        $hash_string =
            $this->response['LMI_PAYEE_PURSE'].
            $this->response['LMI_PAYMENT_AMOUNT'].
            $this->response['LMI_PAYMENT_NO'].
            $this->response['LMI_MODE'].
            $this->response['LMI_SYS_INVS_NO'].
            $this->response['LMI_SYS_TRANS_NO'].
            $this->response['LMI_SYS_TRANS_DATE'].
            $this->response['LMI_SECRET_KEY'].
            $this->response['LMI_PAYER_PURSE'].
            $this->response['LMI_PAYER_WM'];

        if(!$this->loadStoredParameters($this->response['custom'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }

        if(
            md5($hash_string) != $this->response['LMI_HASH']
            AND $this->response['LMI_PAYMENT_AMOUNT'] == $this->parameters['LMI_PAYMENT_AMOUNT']
            AND $this->response['LMI_PAYEE_PURSE'] == $this->settings['webmoney_purse']
            AND $this->response['LMI_PAYMENT_NO'] == $this->parameters['LMI_PAYMENT_NO']
        ) {
            return true;
        } else {
            $this->errors[] = 'Invalid hash, amount, purse, or payment number';
            return false;
        }
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            $this->result = 'success';
            $this->result_amount = $this->parameters['LMI_PAYMENT_AMOUNT'];
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
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}
?>