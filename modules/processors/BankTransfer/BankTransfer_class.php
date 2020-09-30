<?php
class BankTransfer extends PaymentAPIHandler {
    var $gateway_name = 'BankTransfer';
    var $test_url = '';
    var $url = '';
    
    function __construct($PMDR) {
        $this->url = BASE_URL.MEMBERS_FOLDER.'user_payment_return.php';
        parent::__construct($PMDR);
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array();   
    }

    function process() {
        $this->loadResponse();
        $this->loadResult();
        $this->db->Execute("UPDATE ".T_INVOICES." i SET i.gateway_id=? WHERE i.id=?",array($this->gateway_name,$this->response['invoice_id']));
        return $this->response['invoice_id'];     
    }
    
    /**
    * Get payment status
    * Do gateway specific checking for payment validity
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        $this->result = 'pending';
        $this->result_message = $this->settings['banktransfer_instructions'];
        return $this->result;
    }

    function loadResponse() {
        $this->response = $_POST;
    }    
}

?>