<?php
class WorldPayJunior extends PaymentAPIHandler {
    var $gateway_name = 'WorldPayJunior';
    var $test_url = 'https://select-test.worldpay.com/wcc/purchase';
    var $url = 'https://secure.worldpay.com/wcc/purchase';

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
            'instId'=>$this->settings['worldpay_id'],
            'amount'=>$data['balance'], // can be REFUSED/AUTHORIZED/ERROR/CAPTURED
            'currency'=>$this->settings['worldpay_currency'],
            'desc'=>$this->PMDR->getConfig('invoice_company').' - Invoice #'.$data['invoice_id'],
            'testMode'=>($this->settings['testmode'] ? 100 : 0), // can be 101 for test mode as well
            'name'=>$data['user_first_name'].' '.$data['user_last_name'],     // client first/last name, can enter REFUSED, AUTHORISED, ERROR or CAPTURED here to force a test response of this type
            'address'=>$data['user_address1'],
            'postcode'=>$data['user_zip'],
            'country'=>$data['user_country'],
            'tel'=>$data['user_phone'],
            'fax'=>$data['user_fax'],
            'email'=>$data['user_email'],
            'lang'=>'', // 2 digit iso code
            'MC_callback'=>BASE_URL.'/modules/processors/WorldPayJunior/WorldPayJunior_callback.php'
        );
        if($this->settings['worldpay_futurepay']) {
            $this->parameters['cartId'] = $data['order_id'];
            $this->parameters['futurePayType'] = 'regular';
            $this->parameters['startDelayUnit'] = '1'; // Days
            $this->parameters['startDelayMult'] = $data['days_until_due'];
            $this->parameters['noOfPayments'] = '0';
            if($data['period'] == 'days') {
                $this->parameters['intervalUnit'] = '1';
            } elseif($data['period'] == 'months') {
                $this->parameters['intervalUnit'] = '3';
            } else {
                $this->parameters['intervalUnit'] = '4';
            }
            $this->parameters['intervalMult'] = $data['period_count'];
            $this->parameters['normalAmount'] = $data['total'];
            $this->parameters['option'] = '1';
        } else {
            $this->parameters['cartId'] = $data['invoice_id'];
        }
        if($this->settings['worldpay_variables'] != '') {
            $variables = explode("\n",$this->settings['worldpay_variables']);
            foreach($variables as $variable) {
                $parts = explode('|',$variable);
                $this->parameters[$parts[0]] = $parts[1];
            }
        }

        if($this->settings['worldpay_use_md5']) {
            $this->parameters['signatureFields'] = 'amount:currency:instId:cartId';
            $this->parameters['signiture'] = md5(implode(':',array($this->settings['worldpay_md5_secret'],$this->parameters['amount'],$this->parameters['currency'],$this->parameters['instId'],$this->parameters['cartId'])));
        }
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
        return $this->response['cartId'];
    }

    function processNotification() {
        $this->loadResponse();
        $this->loadResult();
        if($this->result == 'success') {
            switch($this->response['transStatus']) {
                case 'Y':
                    if(isset($this->response['futurePayId'])) {
                        if(!($order_id = $this->db->GetOne("SELECT id FROM ".T_ORDERS." WHERE subscription_id=?",array($this->response['futurePayId'])))) {
                            $this->setupSubscription($this->response['cartId'],$this->response['futurePayId']);
                            $order_id = $this->response['cartId'];
                        }
                        if($invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_INVOICES." i WHERE i.order_id=? AND i.status='unpaid' ORDER BY i.date_due ASC",array($order_id))) {
                            parent::processPayment($invoice_id,$this->response['transId'],$this->response['authAmount']);
                        } else {
                            // give credit
                        }
                    } else {
                        parent::processPayment($this->response['cartId'],$this->response['transId'],$this->response['authAmount']);
                    }
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
        if(isset($this->response['futurePayId'])) {
            $invoice_id = $this->db->GetOne("SELECT i.id FROM ".T_ORDERS." o, ".T_INVOICES." i WHERE o.type=i.type AND o.type_id=i.type_id AND o.id=? ORDER BY i.date_due ASC",array($this->response['cartId']));
            return parent::loadStoredParameters($invoice_id);
            unset($invoice_id);
        } else {
            if(!$this->loadStoredParameters($this->response['cartId'])) {
                $this->errors[] = 'Invalid invoice number (cartId)';
                return false;
            }
        }

        if($this->response['callbackPW'] != $this->settings['worldpay_pw'] OR
            $this->response['authCurrency'] != $this->settings['worldpay_currency']) {
            $this->errors[] = 'Invalid callback password, currency, or amount';
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
        // check countryMatch Parameter
        // Y = Match
        // N = No Match
        // B = Not available
        // I = Country not supplied
        // S = Card issue country not available

        // Check AVS
        // AVS = 4 digits, card verification value, postcode, address, country
        // 0 - not supported
        // 1 - not checked
        // 2 - matched
        // 4 - not matched
        if($this->verifyResponse()) {
            if($this->response['transStatus'] == 'Y') {
                $this->result = 'success';
                $this->result_amount = $this->response['authAmount'];
            } elseif($this->response['transStatus'] == 'C') { // canceled
                $this->result = 'failed';
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
            $this->errors[] = 'POST empty';
        }
        $this->response = $_POST;
    }
}
?>