<?php
class PayPoint extends PaymentAPIHandler {
    var $gateway_name = 'PayPoint';
    var $test_url = 'https://www.secpay.com/java-bin/ValCard';
    var $url = 'https://www.secpay.com/java-bin/ValCard';
    
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
        // User must ask paypoint to check the digest for security
        $this->parameters = array(
            'merchant'=>$this->settings['paypoint_id'],
            'trans_id'=>$data['invoice_id'],
            'amount'=>$data['balance'],
            'callback'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'digest'=>md5($data['invoice_id'].$data['balance'].$this->settings['paypoint_password']),
            'cb_post'=>'true',  // force it to use _POST instead of _GET
            'currency'=>$this->settings['paypoint_currency'],
            'req_cv2'=>$this->settings['paypoint_require_cv2'],
            'ssl_cb'=>$this->settings['paypoint_ssl_cb'], // if we want to use SSL for the callback
            'dups'=>'false',
            'template'=>$this->settings['paypoint_template'],
            'cb_flds'=>'custom,membership_description',
            'mb_flds'=>'trans_id:amount:callback',
            'test_status'=>($this->settings['testmode'] ? 'true' : 'false'),
            'bill_name'=>$data['user_first_name'].' '.$data['user_last_name'], 
            'bill_company'=>$data['user_organization'], 
            'bill_addr_1'=>$data['user_address1'], 
            'bill_addr_2'=>$data['user_address2'], 
            'bill_city'=>$data['user_city'], 
            'bill_state'=>$data['user_state'], 
            'bill_country'=>$data['user_country'], 
            'bill_post_code'=>$data['user_zip'], 
            'bill_tel'=>$data['user_phone'], 
            'bill_email'=>$data['user_email']
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
            switch($this->response['code']) {
                case 'A':
                    parent::processPayment($this->response['trans_id'],$this->response['trans_id'],$this->response['amount']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['cartId'];   
    }
    
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */    
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['trans_id'])) {
            $this->errors[] = 'Invalid invoice number (trans_id)';
            return false;
        }
        if($this->response['hash'] == md5('trans_id='.$this->response['trans_id'].'&amount='.$this->response['amount'].'&callback='.$this->parameters['callback'].'&'.$response['digest'])) {
            return true;   
        } else {
            $this->errors[] = 'Invalid hash';
            return false;
        }  
    }
    
    function loadResult() {
        if($this->verifyResponse()) {
            if($this->response['code'] == 'A') {
                $this->result = 'success';
                $this->result_amount = $this->response['amount'];
            } else {
                $this->result = 'failed';
            }
        } else {
            $this->result = 'failed';
        }
        return $this->result;
    }

    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }    
}
?>