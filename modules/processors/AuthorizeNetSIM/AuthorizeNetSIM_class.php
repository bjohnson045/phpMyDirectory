<?php
class AuthorizeNetSIM extends PaymentAPIHandler {
    var $gateway_name = 'AuthorizeNetSIM';
    var $test_url = 'https://test.authorize.net/gateway/transact.dll';
    var $url = 'https://secure.authorize.net/gateway/transact.dll';

    var $encapsulation_character = '"';

    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->settings['authsim_url'] != '') {
            $this->url = $this->settings['authsim_url'];
        }
    }

    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $timestamp = time();
        // x_fp_hash must match all settings from account, currency should be checked for being blank
        // for return button to work, url must be setup in the merchant account
        $this->parameters = array(
            'x_login'=>$this->settings['authsim_login'],
            'x_type'=>'AUTH_CAPTURE',
            'x_amount'=>$data['balance'],
            'x_tax'=>$data['tax'],
            'x_description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'x_version'=>'3.1',
            'x_receipt_link_method'=>'POST',
            'x_receipt_link_text'=>'Return to your account',
            'x_receipt_link_url'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php', // We don't use the relay response because it pulls the URL, not redirect
            'x_relay_response'=>'FALSE',
            'x_email_customer'=>'FALSE',
            'x_method'=>'CC',
            'x_fp_hash'=>hash_hmac('md5',$this->settings['authsim_login'].'^'.$data['invoice_id'].'^'.$timestamp.'^'.$data['balance'].'^',$this->settings['authsim_tran_key']),
            'x_fp_sequence'=>$data['invoice_id'],
            'x_invoice_num'=>$data['invoice_id'],
            'x_fp_timestamp'=>$timestamp,
            'x_customer_ip'=>get_ip_address(),
            'x_show_form'=>'PAYMENT_FORM',
            'x_duplicate_window'=>'120', // 120 Seconds before a duplicate transaction can be submitted
            'x_first_name'=>$data['user_first_name'],
            'x_last_name'=>$data['user_last_name'],
            'x_company'=>$data['user_organization'],
            'x_address'=>$data['user_address1'],
            'x_city'=>$data['user_city'],
            'x_state'=>$data['user_state'],
            'x_zip'=>$data['user_zip'],
            'x_country'=>$data['user_country'],
            'x_phone'=>$data['user_phone'],
            'x_fax'=>$data['user_fax'],
            'x_email'=>$data['user_email'],
            'x_cust_id'=>$data['user_id'],
            'x_logo_url'=>$this->settings['authsim_logo_url'],
            'x_test_request'=>($this->settings['testmode'] ? 'TRUE' : 'FALSE')
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
        return $this->response['x_invoice_num'];
    }

    function verifyResponse() {
        if(strtoupper($this->response['x_MD5_Hash']) == strtoupper(md5($this->settings['authsim_hash'].$this->settings['authsim_login'].$this->response['x_trans_id'].$this->response['x_amount']))) {
            return true;
        } else {
            $this->errors[] = 'Invalid hash';
            return false;
        }
    }

    function loadResult() {
        if($this->verifyResponse()) {
            switch($this->response['x_response_code']) {
                case 1:
                    $this->result = 'success';
                    $this->result_amount = $this->response['x_amount'];
                    break;
                case 2:
                    $this->result = 'declined';
                    break;
                case 4:
                    $this->result = 'pending';
                    break;
                default:
                    $this->result = 'failed';
                    break;
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

class AuthorizeNetSIM_Notification extends AuthorizeNetSIM {
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['x_response_code']) {
                case 1:
                    parent::processPayment($this->response['x_invoice_num'],$this->response['x_trans_id'],$this->response['x_amount']);
                    break;
                case 2:
                    //declined
                    break;
                case 4:
                    //pending
                    break;
                default:
                    //failed
                    break;
            }
        }
        return $this->response['x_invoice_num'];
    }
}
?>