<?php
class AuthorizeNetAIM extends PaymentAPIHandler {
    var $gateway_name = 'AuthorizeNetAIM';
    var $test_url = 'https://test.authorize.net/gateway/transact.dll';
    var $url = 'https://secure.authorize.net/gateway/transact.dll';
    var $on_site_payment = true;
    
    function __construct($PMDR) {
        parent::__construct($PMDR);
        if($this->settings['authaim_url'] != '') {
            $this->url = $this->settings['authaim_url'];
        } 
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'x_login'=>$this->settings['authaim_login'],
            'x_tran_key'=>$this->settings['authaim_tran_key'],
            'x_version'>='3.1',
            'x_delim_char'=>',',
            'x_delim_data'=>'TRUE',
            'x_encap_char'=>'"',
            'x_url'=>'FALSE',
            'x_type'=>'AUTH_CAPTURE',
            'x_method'=>'CC',
            'x_relay_response'=>'FALSE',
            'x_card_num'=>$data['cc_number'],
            'x_exp_date'=>$data['cc_expire_month'].$data['cc_expire_year'],
            'x_card_code'=>$data['cc_cvv2'],
            'x_currency_code'=>$this->settings['authaim_currency'],
            'x_auth_code'=>'',
            'x_cust_id'=>$data['user_id'],
            'x_email_customer'=>'FASLE',
            'x_invoice_num'=>$data['invoice_id'],
            'x_description'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'x_amount'=>$data['balance'],
            'x_first_name'=>$data['user_first_name'],
            'x_last_name'=>$data['user_last_name'],
            'x_address'=>$data['user_address1'],
            'x_city'=>$data['user_city'],
            'x_state'=>$data['user_state'],
            'x_zip'=>$data['user_zip'],
            'x_phone'=>$data['user_phone'],
            'x_country'=>$data['user_country'],
            'x_company'=>$data['user_organization'],
            'x_email'=>$data['user_email'],
            'x_customer_ip'=>get_ip_address(),
            'x_test_request'=>($this->settings['testmode'] ? 'TRUE' : 'FALSE')
        );
        return true;
    }
    
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['response_code']) {
                case 1:
                    parent::processPayment($this->response['invoice_number'],$this->response['transaction_id'],$this->response['amount']);
                    break;
                default:
                    return false;
            }
        }
        return $this->response['invoice_number'];       
    }
    
    function loadResult() {
        // If Response code != 1 we are invalid.  1 == Approved
        // We also check to make sure the MD5 value matches
        // $this->response[38] != $this->getMd5HashSecurity() <= needed only for SIM
        // !$this->transactionExists($this->response['txn_id'])
        if($this->verifyResponse()) {
            switch($this->response['response_code']) {
                case 1:
                    $this->result = 'success';
                    $this->result_amount = $this->response['amount'];
                    break;
                case 2:
                    $this->result = 'declined';
                    break;
                case 4:
                    $this->result = 'pending';
                default:
                    $this->result = 'failed';
                    break;
            }
        } else {
            $this->result = 'failed';
        } 
        return $this->result;
    }
    
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['invoice_number'])) {
            $this->errors[] = 'Invalid invoice number';
            return false;
        }
        if(!empty($this->settings['authaim_hash'])) {
            if(strtoupper(md5($this->settings['authaim_hash'].$this->settings['authaim_login'].$this->response['transaction_id'].$this->response['amount'])) != $this->response['md5_hash']) {
                $this->errors[] = 'Invalid hash';
                return false;
            }
        }
        return true;    
    }

    function loadResponse() {        
        $parameters = $this->parameters;
        
        $prepared_data = '';
        foreach($parameters as $key=>$value) {
            $prepared_data .= "$key=".urlencode($value)."&";
        }
        
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HEADER, 0); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($prepared_data,'&')); // use HTTP POST to send form data
        $response = curl_exec($ch); //execute post and get results

        if(!$response) {
            $this->errors[] = $error_message .' ('.curl_errno($ch).')';
        } else {
            $response = explode('","',trim($response,'"'));
            $this->response = array(
                'response_code'=>$response[0],
                'response_subcode'=>$response[1],
                'response_reason_code'=>$response[2],
                'response_reason_text'=>$response[3],
                'authorization_code'=>$response[4],
                'avs_response'=>$response[5],
                'transaction_id'=>$response[6],
                'invoice_number'=>$response[7],
                'description'=>$response[8],
                'amount'=>$response[9],
                'method'=>$response[10],
                'transaction_type'=>$response[11],
                'customer_id'=>$response[12],
                'first_name'=>$response[13],
                'last_name'=>$response[14],
                'company'=>$response[15],
                'address'=>$response[16],
                'city'=>$response[17],
                'state'=>$response[18],
                'zip_code'=>$response[19],
                'country'=>$response[20],
                'phone'=>$response[21],
                'fax'=>$response[23],
                'email'=>$response[23],
                'order_number'=>$response[36],
                'md5_hash'=>$response[37],
                'card_code_response'=>$response[38],
                'card_authorization_verification'=>$response[39]
            );
        }
        curl_close ($ch);

        return $this->response; 
    }
}
?>