<?php
// Setup HTTP referrer approved sites in viaklix admin
class viaKlix extends PaymentAPIHandler {
    var $gateway_name = 'viaKlix';
    var $test_url = '';
    var $url = 'https://www.viaKLIX.com/process.asp';
    
    // Test card no visa: 4911830000000, 4917610000000000
    
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
            'ssl_transaction_type'=>'SALE', // SALE CREDIT OR FORCE
            'ssl_merchant_ID'=>$this->settings['viaklix_merchant_id'],
            'ssl_user_id'=>$this->settings['viaklix_user_id'],
            'ssl_pin'=>$this->settings['viaklix_pin'],
            'ssl_amount'=>$data['balance'],
            'ssl_description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'ssl_show_form'=>'true', // we can pass in card/exp/cvv2 if we want but lets keep it simple
            'ssl_test_mode'=>($this->settings['testmode'] ? 'true' : 'false'),
            'ssl_invoice_number'=>$data['invoice_id'],
            'ssl_sales_tax'=>'',
            // ssl_avs_address, ssl_avs_zip ,ssl_avs_response used for AVS processing
            'ssl_result_format'=>'ASCII', // can also be HTML
            'ssl_receipt_link_method'=>'POST',
            'ssl_receipt_link_text'=>'Continue',
            'ssl_receipt_link_url'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'ssl_company'=>$data['user_organization'],
            'ssl_first_name'=>$data['user_first_name'],
            'ssl_last_name'=>$data['user_last_name'],
            'ssl_address2'=>$data['user_address1'],
            'ssl_city'=>$data['user_city'],
            'ssl_state'=>$data['user_state'],
            'ssl_country'=>$data['user_country'],
            'ssl_phone'=>$data['user_phone'],
            'ssl_email'=>$data['user_email']
        );        
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
            parent::processPayment($this->response['ssl_invoice_number'],$this->response['ssl_txn_id'],$this->response['ssl_amount']);
        }
        return $this->response['ssl_invoice_number'];
    }
    
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['ssl_txn_id'])) {
            $this->errors[] = 'Invalid invoice number (ssl_txn_id)';
            return false;
        }
        
        if($this->response['ssl_amount'] == $this->parameters['ssl_amount'] AND $this->response['ssl_invoice_number'] == $this->parameters['ssl_invoice_number']) {
            return true;
        } else {
            $this->errors[] = 'Invalid amount or invoice number';
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
            if($this->response['ssl_result'] == '0') {
                $this->result = 'success';
                $this->result_amount = $this->parameters['ssl_amount'];
            } else {
                $this->result = 'failed';
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
            $this->errors = 'POST empty';
        }
        $this->response = $_POST;
    }    
}
?>