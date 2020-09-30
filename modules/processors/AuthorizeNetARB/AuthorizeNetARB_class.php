<?php
class AuthorizeNetARB extends PaymentAPIHandler {
    // Supports only months 1-12 and days 7-365 (not years)
    var $gateway_name = 'AuthorizeNetARB';
    var $test_url = 'https://apitest.authorize.net/xml/v1/request.api';
    var $url = 'https://api.authorize.net/xml/v1/request.api';
    var $on_site_payment = true;
    
    function __construct($PMDR) {
        parent::__construct($PMDR);
    }
    
    function process() {
        $this->loadResponse();
        if(!$this->verifyResponse()) {
            $this->result = 'failed';
        } else {
            $this->loadResult();
            if($this->response['resultCode'] == 'Ok') {
                $this->setupSubscription($this->response['refId'],$this->response['subscriptionId']);
            }
        }
        
        return $this->response['refId'];     
    }
    
    /**
    * Load gateway parameters
    * Sets specific data parameters, using the generic data passed in
    * @param array $data Generic data passed in from payment form
    * @return void
    */
    function loadParameters($data) {
        $this->parameters = array(
            'login'=>$this->settings['autharb_login'],
            'transaction_key'=>$this->settings['autharb_tran_key'],
            'refId'=>$data['order_id'],
            'invoice_id'=>$data['invoice_id'],
            'amount'=>$data['amount_recurring'],
            'trialAmount'=>$data['balance'],
            'cc_number'=>$data['cc_number'],
            'cc_expire'=>$data['cc_expire_year'].'-'.$data['cc_expire_month'], // YYYY-MM
            'subscription_name'=>'Order ID '.$data['order_id'].' - Order #'.$data['order_number'],
            'interval_length'=>($data['period'] != 'years' ? $data['period_count'] : 12*$data['period_count']),
            'interval_unit'=>($data['period'] != 'years' ? $data['period'] : 'months'), // 'days' or 'months'
            'first_name'=>$data['user_first_name'],
            'last_name'=>$data['user_last_name'],
            'company'=>$data['user_organization'],
            'address'=>$data['user_address1'],
            'city'=>$data['user_city'],
            'state'=>$data['user_state'],
            'zip'=>$data['user_zip'],
            'country'=>$data['user_country'],
            'email'=>$data['user_email'],
            'phone'=>$data['user_phone'],
            'user_id'=>$data['user_id']
        );
        
        return true;
    }
    
    function loadResult() {
        switch($this->response['resultCode']) {
            case 'Ok':
                $this->result = 'success';
                break;
            default:
                $this->result = 'failed';
                $this->result_message = $this->response['text']; 
                break;
        } 
        return $this->result;
    }

    function verifyResponse() {
        $this->response_status = 'success';
        return true;
    }
    
    function loadResponse() {
        $xml = '<?xml version="1.0" encoding="utf-8"?>
        <ARBCreateSubscriptionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
            <merchantAuthentication>
                <name>'.$this->parameters['login'].'</name>
                <transactionKey>'.$this->parameters['transaction_key'].'</transactionKey>
            </merchantAuthentication>
            <refId>'.$this->parameters['refId'].'</refId>
            <subscription>
                <name>'.$this->parameters['subscription_name'].'</name>
                <paymentSchedule>
                    <interval>
                        <length>'.$this->parameters['interval_length'].'</length>
                        <unit>'.$this->parameters['interval_unit'].'</unit>
                    </interval>
                    <startDate>'.date('Y-m-d').'</startDate>
                    <totalOccurrences>9999</totalOccurrences>
                    <trialOccurrences>1</trialOccurrences>
                </paymentSchedule>
                <amount>'.$this->parameters['amount'].'</amount>
                <trialAmount>'.$this->parameters['trialAmount'].'</trialAmount>
                <payment>
                    <creditCard>
                        <cardNumber>'.$this->parameters['cc_number'].'</cardNumber>
                        <expirationDate>'.$this->parameters['cc_expire'].'</expirationDate>
                    </creditCard>
                </payment>
                <order>
                    <invoiceNumber>'.$this->parameters['refId'].'</invoiceNumber>
                    <description>'.$this->parameters['description'].'</description>
                </order>
                <customer>
                    <id>'.$this->parameters['user_id'].'</id>
                    <email>'.$this->parameters['email'].'</email>
                    <phoneNumber>'.$this->parameters['phone'].'</phoneNumber>
                </customer>
                <billTo>
                    <firstName>'.$this->parameters['first_name'].'</firstName>
                    <lastName>'.$this->parameters['last_name'].'</lastName>
                    <company>'.$this->parameters['company'].'</company>
                    <address>'.$this->parameters['address'].'</address>
                    <city>'.$this->parameters['city'].'</city>
                    <state>'.$this->parameters['state'].'</state>
                    <zip>'.$this->parameters['zip'].'</zip>
                    <country>'.$this->parameters['country'].'</country>
                </billTo>
            </subscription>
        </ARBCreateSubscriptionRequest>';
        
        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
        curl_setopt($ch, CURLOPT_HEADER, 1); // set to 0 to eliminate header info from response
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // Returns response data instead of TRUE(1)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml); // use HTTP POST to send form data
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $raw_response = curl_exec($ch); //execute post and get results

        if(!$raw_response) {
            $this->errors[] = curl_error($ch).' ('.curl_errno($ch).')';
        } else {    
            $response_details = array();
            preg_match('/<refId>(.+)<\/refId>/',$raw_response,$response_details);
            $this->response['refId'] = $response_details[1];
            
            $response_details = array();
            preg_match('/<resultCode>(.+)<\/resultCode>/',$raw_response,$response_details);
            $this->response['resultCode'] = $response_details[1];
            
            $response_details = array();
            preg_match('/<code>(.+)<\/code>/',$raw_response,$response_details);
            $this->response['code'] = $response_details[1];
            
            $response_details = array();
            preg_match('/<text>(.+)<\/text>/',$raw_response,$response_details);
            $this->response['text'] = $response_details[1];
            
            $response_details = array();
            preg_match('/<subscriptionId>(.+)<\/subscriptionId>/',$raw_response,$response_details);
            $this->response['subscriptionId'] = $response_details[1];
        }
        curl_close ($ch);
        return $this->response; 
    }
}

class AuthorizeNetARB_Notification extends AuthorizeNetARB {
    function loadStoredParameters($order_id) {
        $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.id=? ORDER BY i.date_due ASC",array($order_id));
        return parent::loadStoredParameters($invoice_id);
    }
    
    function loadResult() {
        switch($this->response['x_response_code']) {
            case 1:
                $this->result = 'success';
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
        return $this->result;
    }
    
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['x_invoice_num'])) {
            $this->errors[] = 'Invalid invoice number';
            return false;
        }
        if(!empty($this->settings['autharb_hash'])) {
            if(strtoupper(md5($this->settings['autharb_hash'].$this->response['x_trans_id'].$this->response['x_amount'])) != $this->response['x_MD5_Hash']) {
                $this->errors[] = 'Invalid hash';
                return false;
            }
        }
        return true;    
    }

    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->verifyResponse()) {
            switch($this->result) {
                case 'success':
                    if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($this->response['x_invoice_num']))) {
                        parent::processPayment($invoice_id,$this->response['x_trans_id'],$this->response['x_amount']);
                    } else {
                        // give credit
                    }
                    break;
            }
        }
        return $invoice_id;    
    }
    
    function loadResponse() {
        if(!count($_POST)) {
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }    
}
?>