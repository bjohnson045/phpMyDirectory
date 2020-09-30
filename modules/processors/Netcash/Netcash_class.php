<?php
class Netcash extends PaymentAPIHandler {
    // Successful Response: CCNo – 4242424242424242 EXP - 12/2013 CVC – 123
    // Failed Response: CCNo – 5221001010000024 EXP - 12/2013 CVC – 123 (this will fail with a reason of „Call‟)

    var $gateway_name = 'Netcash';
    var $url = 'https://gateway.netcash.co.za/vvonline/ccnetcash.asp';

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
            'm_1'=>$this->settings['netcash_username'],
            'm_2'=>$this->settings['netcash_password'],
            'm_3'=>$this->settings['netcash_pin'],
            'p1'=>$this->settings['netcash_terminal_number'],
            'p2'=>$data['invoice_id'],
            'p3'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'p4'=>$data['balance'],
            'p10'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'Budget'=>'N',
            'm_9'=>$data['user_email']
        );
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
        return $this->response['Reference'];
    }

    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['Reference'])) {
            $this->errors[] = 'Invalid invoice number (Reference)';
            return false;
        }
        return true;
    }

    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['TransactionAccepted']) {
                case 'true':
                    $this->result = 'success';
                    $this->result_amount = $this->response['Amount'];
                    break;
                case 'false':
                    $this->errors[] = $this->response['Reason'];
                    $this->result = 'failed';
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
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}

class Netcash_Notification extends Netcash {
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            parent::processPayment($this->response['Reference'],$this->response['RETC'],$this->response['Amount']);
            break;
        }
        return $this->response['Reference'];
    }
}
?>