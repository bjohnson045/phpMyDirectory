<?php
class GTBill extends PaymentAPIHandler {
    var $gateway_name = 'GTBill';
    var $test_url = '';
    var $url = 'https://billing.GTBill.com/signup.aspx';
    
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
        include(PMDROOT.'/includes/country_codes.php');
        $country_codes_inverse = array_flip($country_codes);
        if(isset($country_codes[$data['user_country']])) {
            $country = $data['user_country'];    
        } elseif(isset($country_codes_inverse[$data['user_country']])) {
            $country = $country_codes_inverse[$data['user_country']];    
        } else {
            $country = '';
        }
        
        $this->parameters = array(
            'MerchantID'=>$this->settings['gtbill_merchant_id'],
            'SiteID'=>$this->settings['gtbill_site_id'],
            'PriceID'=>$this->settings['gtbill_price_id_'.$data['pricing_id']],
            'CurrencyID'=>$this->settings['gtbill_currency'],
            'FirstName'=>$data['user_first_name'],
            'LastName'=>$data['user_last_name'],
            'Address1'=>$data['user_address1'],
            'Address2'=>$data['user_address2'],
            'City'=>$data['user_city'],
            'State'=>$data['user_state'],
            'Country'=>$country,
            'Phone'=>$data['user_phone'],
            'PostalCode'=>$data['user_zip'],
            'Email'=>$data['user_email'],
            'username'=>'',
            'password'=>'',
            'MerchantReference'=>$data['order_id'],
            'ApprovalURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'DenialURL'=>BASE_URL.MEMBERS_FOLDER.'user_payment_return.php',
            'ExistingMemberURL'=>BASE_URL.'/modules/processos/GTBillMember_ipn.php'
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
        return $this->response['MerchantReference'];    
    }
    
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        if(!$this->loadStoredParameters($this->response['MerchantReference'])) {
            $this->errors[] = 'Invalid invoice number (custom)';
            return false;
        }
        if(!isset($this->response['TransactionID'])) {
            return false;
        }
        return true;    
    }
    
    function loadStoredParameters($order_id) {
        $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.id=? ORDER BY i.date_due ASC",array($order_id));
        return parent::loadStoredParameters($invoice_id);
    }
    
    /**
    * Load result
    * Load the result into the class variable
    * @return string 'success'|'failed'|'pending'|'declined'
    */
    function loadResult() {
        // GTBill does not return unless its a success
        if($this->verifyResponse()) {
            $this->result = 'success';
            $this->result_amount = $this->response['Amount'];
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
        if(!count($_GET)) {
            $this->errors[] = 'GET empty';
        }
        $this->response = $_GET;
    }
}

class GTBillMember_Notification extends GTBillMember {
    /**
    * Verify Response
    * Check to make sure the response is valid and not fraudulent
    * @return boolean
    */
    function verifyResponse() {
        $http_request = $this->PMDR->get('HTTP_Request');
        $http_request->settings = array(
            CURLOPT_HEADER=>0,
            CURLOPT_RETURNTRANSFER=>1
        );
        $response = $http_request->get('curl','https://billing.GTBill.com/ip_list.txt');
        if(!in_array(get_ip_address(),explode('|',$response))) {
            $this->errors[] = 'Invalid IP address';
            return false;
        }
        if($this->response['Action'] == 'Add') {
            if(!$this->loadStoredParameters($this->response['MerchantReference'])) {
                $this->errors[] = 'Invalid order number';
                return false;
            }    
        } else {
            if(!$this->loadStoredParameters($this->getSubscriptionOrderID($this->response['MemberID']))) {
                $this->errors[] = 'Invalid order number';
                return false;
            }
        }
        return true;    
    }

    
    /**
    * Process IPN notification
    * We process the IPN notification sent and update the database appropriately
    * @return int Inovoice ID
    */
    function process() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['Action']) {
                case 'Add':
                    $this->setupSubscription($this->response['MerchantReference'],$this->response['MemberID']);
                    break;
                case 'Cancel':
                    $this->cancelSubscription($this->db->GetOne("SELECT id FROM ".T_ORDERS." WHERE subscription_id=?",array($this->response['MemberID'])));
                    break;
                case 'Payment':
                case 'Rebill':
                    if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_INVOICES." i WHERE i.order_id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($this->response['MerchantReference']))) {
                        parent::processPayment($invoice_id,$this->response['TransactionID'],$this->response['Amount']);
                    } else {
                        // give credit
                    }
                    break;
                case 'Deactivate':
                    // Do nothing
                    break;
            }
        }
        return $this->response['MerchantReference'];    
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